<?php
// api_get_network_config.php
// Endpoint para obtener info de red remota (ping, hostname, MAC en ARP, puertos abiertos, inferencia OS, máscara, puerta de enlace y DNS)

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (empty($_GET['ip'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetro ip requerido']);
    exit;
}

$ip = $_GET['ip'];
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'IP inválida']);
    exit;
}

set_time_limit(30); // evitar timeouts en escaneos cortos
$response = [
    'success' => true,
    'ip' => $ip,
    'reachable' => false,
    'hostname' => null,
    'mac' => null,
    'open_ports' => [],
    'os_guess' => 'Desconocido',
    'notes' => [],
    'netmask' => null,    // máscara (de la interfaz local utilizada para alcanzar la IP)
    'gateway' => null,    // gateway usado para alcanzar la IP (ruta local)
    'dns' => []           // servidores DNS del servidor donde corre este script
];

// -------------- UTILIDADES --------------

function safe_exec($cmd, &$output = null, &$ret = null) {
    $output = [];
    $ret = 1;
    // usamos @exec para evitar warnings en caso de comandos no permitidos
    @exec($cmd . ' 2>&1', $output, $ret);
    return $output;
}

function ping_host($ip, $timeout = 1) {
    $escaped = escapeshellarg($ip);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = "ping -n 1 -w " . ($timeout * 1000) . " $escaped";
    } else {
        $cmd = "ping -c 1 -W " . intval($timeout) . " $escaped";
    }
    $out = [];
    $ret = 1;
    @exec($cmd, $out, $ret);
    return $ret === 0;
}

function prefix_to_netmask($prefix) {
    $prefix = (int)$prefix;
    if ($prefix < 0 || $prefix > 32) return null;
    $mask = ($prefix === 0) ? 0 : (~((1 << (32 - $prefix)) - 1)) & 0xFFFFFFFF;
    return long2ip($mask);
}

// Obtiene interfaz y gateway que se usan para alcanzar la IP (Linux: ip route get)
function get_route_info($ip, &$notes = []) {
    $info = ['iface' => null, 'gateway' => null, 'src' => null];
    if (stripos(PHP_OS, 'WIN') !== false) {
        // Windows: route print parsing is unreliable here — devolver vacío
        $notes[] = 'Detección de ruta no soportada en Windows en este script.';
        return $info;
    }

    // Intentar ip route get
    $cmd = 'ip route get ' . escapeshellarg($ip);
    $out = [];
    $ret = 1;
    @exec($cmd . ' 2>/dev/null', $out, $ret);
    if ($ret === 0 && !empty($out)) {
        // Normalmente la primera línea contiene "via <gw> dev <iface> src <src>"
        $line = implode(' ', $out);
        if (preg_match('/via\s+([0-9\.]+)\s+dev\s+([^\s]+)/', $line, $m)) {
            $info['gateway'] = $m[1];
            $info['iface'] = $m[2];
        } elseif (preg_match('/dev\s+([^\s]+)/', $line, $m2)) {
            $info['iface'] = $m2[1];
        }
        if (preg_match('/src\s+([0-9\.]+)/', $line, $m3)) {
            $info['src'] = $m3[1];
        }
        return $info;
    }

    // Fallback: parse `route -n`
    $out2 = [];
    @exec('route -n 2>/dev/null', $out2, $r2);
    if ($r2 === 0 && !empty($out2)) {
        foreach ($out2 as $line) {
            // columnas: Destination Gateway Genmask Flags MSS Window irtt Iface
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 8 && filter_var($parts[0], FILTER_VALIDATE_IP)) {
                $dest = $parts[0];
                $gw = $parts[1];
                $iface = end($parts);
                // Si la ruta es 0.0.0.0 -> default
                if ($dest === '0.0.0.0' && filter_var($gw, FILTER_VALIDATE_IP)) {
                    $info['gateway'] = $gw;
                    $info['iface'] = $iface;
                    return $info;
                }
            }
        }
    }

    $notes[] = 'No se pudo determinar la ruta con comandos disponibles.';
    return $info;
}

// Obtener netmask (dotted) de la interfaz local
function get_netmask_for_iface($iface, &$notes = []) {
    if (!$iface) return null;
    if (stripos(PHP_OS, 'WIN') !== false) {
        $notes[] = 'Detección de máscara no soportada en Windows en este script.';
        return null;
    }

    // Intentar usar ip -o -f inet addr show dev <iface>
    $out = [];
    @exec('ip -o -f inet addr show dev ' . escapeshellarg($iface) . ' 2>/dev/null', $out, $r);
    if ($r === 0 && !empty($out)) {
        foreach ($out as $line) {
            // línea ejemplo: "2: eth0    inet 192.168.1.2/24 brd ..."
            if (preg_match('/inet\s+([0-9\.]+)\/(\d+)/', $line, $m)) {
                $prefix = intval($m[2]);
                $mask = prefix_to_netmask($prefix);
                if ($mask) return $mask;
            }
        }
    }

    // Fallback a ifconfig
    @exec('ifconfig ' . escapeshellarg($iface) . ' 2>/dev/null', $out2, $r2);
    if ($r2 === 0 && !empty($out2)) {
        $joined = implode("\n", $out2);
        // Buscar netmask en varios formatos
        if (preg_match('/netmask\s+([0-9\.]+)/', $joined, $m2)) {
            return $m2[1];
        }
        if (preg_match('/Mask:([0-9\.]+)/', $joined, $m3)) {
            return $m3[1];
        }
    }

    $notes[] = 'No se pudo determinar la máscara de subred de la interfaz.';
    return null;
}

// Leer DNS del /etc/resolv.conf (servidores configurados en el servidor)
function get_system_dns(&$notes = []) {
    $dns = [];
    $path = '/etc/resolv.conf';
    if (is_readable($path)) {
        $content = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($content !== false) {
            foreach ($content as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0) continue;
                if (preg_match('/^nameserver\s+([0-9\.]+)/i', $line, $m)) {
                    $dns[] = $m[1];
                }
            }
        } else {
            $notes[] = 'No se pudo leer ' . $path;
        }
    } else {
        $notes[] = $path . ' no es legible en este servidor.';
    }
    return $dns;
}

// Obtener MAC en caché ARP
function get_mac_from_arp($ip) {
    $mac = null;

    if (stripos(PHP_OS, 'WIN') === false) {
        @exec("ip neigh show " . escapeshellarg($ip) . " 2>/dev/null", $out1, $r1);
        if ($r1 === 0 && !empty($out1)) {
            foreach ($out1 as $line) {
                if (preg_match('/([0-9a-f]{2}(?::[0-9a-f]{2}){5})/i', $line, $m)) {
                    return strtolower($m[1]);
                }
            }
        }
        @exec("arp -n " . escapeshellarg($ip) . " 2>/dev/null", $out2, $r2);
        if ($r2 === 0 && !empty($out2)) {
            foreach ($out2 as $line) {
                if (preg_match('/([0-9a-f]{2}(?::[0-9a-f]{2}){5})/i', $line, $m)) {
                    return strtolower($m[1]);
                }
            }
        }
        @exec("arp -a " . escapeshellarg($ip) . " 2>/dev/null", $out3, $r3);
        if ($r3 === 0 && !empty($out3)) {
            foreach ($out3 as $line) {
                if (strpos($line, $ip) !== false && preg_match('/([0-9a-f]{2}(?::[0-9a-f]{2}){5})/i', $line, $m)) {
                    return strtolower($m[1]);
                }
            }
        }
    } else {
        // Windows
        @exec("arp -a " . escapeshellarg($ip), $outW, $rW);
        if ($rW === 0 && !empty($outW)) {
            foreach ($outW as $line) {
                if (strpos($line, $ip) !== false && preg_match('/([0-9a-f]{2}(?:-[0-9a-f]{2}){5})/i', $line, $m)) {
                    return str_replace('-', ':', strtolower($m[1]));
                }
            }
        }
    }

    return $mac;
}

// -------------- FIN UTILIDADES --------------

// 1) Ping la IP para ver si responde (esto también ayuda a poblar la ARP local)
try {
    $reachable = ping_host($ip, 1);
    $response['reachable'] = $reachable;
} catch (Throwable $e) {
    $response['notes'][] = "Ping falló: " . $e->getMessage();
}

// 2) Hostname (reverse DNS)
$reverse = @gethostbyaddr($ip);
if ($reverse && $reverse !== $ip) {
    $response['hostname'] = $reverse;
}

// 3) Intentar obtener MAC en la ARP local (Linux/Unix preferentemente)
$mac = get_mac_from_arp($ip);
if ($mac) {
    $response['mac'] = $mac;
} else {
    $response['notes'][] = 'MAC no encontrada en caché ARP local';
}

// 4) Comprobar puertos TCP comunes de forma rápida (sin nmap): 22,80,135,139,445,3389,5985,161
$common_ports = [22, 80, 135, 139, 445, 3389, 5985, 5986, 161];
$open_ports = [];
$timeout_port = 0.6; // segundos por intento

foreach ($common_ports as $port) {
    $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout_port);
    if ($fp) {
        $open_ports[] = $port;
        fclose($fp);
    }
}
$response['open_ports'] = $open_ports;

// 5) Inferencia básica de SO
if (in_array(3389, $open_ports) || in_array(445, $open_ports) || in_array(139, $open_ports)) {
    $response['os_guess'] = 'Windows (probable)';
} elseif (in_array(22, $open_ports) || in_array(161, $open_ports)) {
    $response['os_guess'] = 'UNIX/Linux (probable)';
} else {
    $response['os_guess'] = 'No determinado';
}

// 6) Si no responde ping pero hay puertos, lo indicamos
if (!$response['reachable'] && !empty($open_ports)) {
    $response['notes'][] = 'No responde a ping pero tiene puertos TCP abiertos (posible firewall ICMP bloqueado)';
}

// 7) Obtener información de ruta/interfaz local (gateway y iface)
$routeNotes = [];
$routeInfo = get_route_info($ip, $routeNotes);
if (!empty($routeNotes)) {
    $response['notes'] = array_merge($response['notes'], $routeNotes);
}
if (!empty($routeInfo['gateway'])) $response['gateway'] = $routeInfo['gateway'];
$iface = $routeInfo['iface'] ?? null;

// 8) Obtener máscara de la interfaz local usada para alcanzar la IP
$netmaskNotes = [];
$mask = get_netmask_for_iface($iface, $netmaskNotes);
if ($mask) {
    $response['netmask'] = $mask;
} else {
    // si no hay iface, intentar calcular a partir de la IP local (src) si está disponible
    if (!empty($routeInfo['src'])) {
        // intentar hallar la interfaz asociada a la src
        $src = $routeInfo['src'];
        $iface2 = null;
        @exec('ip -o -f inet addr show 2>/dev/null', $outAll, $rAll);
        if ($rAll === 0 && !empty($outAll)) {
            foreach ($outAll as $line) {
                if (preg_match('/inet\s+([0-9\.]+)\/(\d+)\s+brd\s+[0-9\.]+\s+scope\s+\w+\s+([^ ]+)/', $line, $m)) {
                    if ($m[1] === $src) {
                        $prefix = intval($m[2]);
                        $response['netmask'] = prefix_to_netmask($prefix);
                        break;
                    }
                }
            }
        }
    }
    if (!empty($netmaskNotes)) {
        $response['notes'] = array_merge($response['notes'], $netmaskNotes);
    }
}

// 9) Obtener DNS del sistema
$dnsNotes = [];
$dns = get_system_dns($dnsNotes);
if (!empty($dns)) $response['dns'] = array_values($dns);
if (!empty($dnsNotes)) $response['notes'] = array_merge($response['notes'], $dnsNotes);

// 10) SNMP opcional
if (!empty($_GET['snmp_community'])) {
    $community = $_GET['snmp_community'];
    if (function_exists('snmpget')) {
        try {
            @ini_set('snmp.timeout', 1);
            @ini_set('snmp.retries', 1);
            $sysDescr = @snmpget($ip, $community, 'SNMPv2-MIB::sysDescr.0');
            if ($sysDescr) {
                $response['snmp_sysdescr'] = trim($sysDescr);
            }
        } catch (Throwable $e) {
            $response['notes'][] = 'SNMP: ' . $e->getMessage();
        }
    } else {
        $response['notes'][] = 'SNMP no disponible en este servidor (extensión PHP faltante)';
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;