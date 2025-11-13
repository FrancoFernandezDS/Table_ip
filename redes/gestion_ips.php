<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Redes Municipales - IPs Libres</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        header p {
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .total-ips {
            border-left: 4px solid var(--primary);
        }
        
        .ips-en-uso {
            border-left: 4px solid var(--secondary);
        }
        
        .ips-libres {
            border-left: 4px solid var(--success);
        }
        
        .ips-sin-ping {
            border-left: 4px solid var(--warning);
        }
        
        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 500;
        }
        
        .tab.active {
            background: var(--secondary);
            color: white;
        }
        
        .tab:hover:not(.active) {
            background: var(--light);
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .tab-content.active {
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .ip-libre {
            color: var(--success);
            font-weight: bold;
        }
        
        .ip-ocupada {
            color: var(--danger);
        }
        
        .ip-sin-ping {
            color: var(--warning);
        }
        
        .search-box {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-box button {
            padding: 10px 20px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 5px;
        }
        
        .pagination button {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .pagination button.active {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }
        
        footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1.5rem;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .ips-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        
        .ip-row {
            display: flex;
            gap: 10px;
        }
        
        .ip-item {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .ip-libre {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .ip-ocupada {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .ip-sin-ping {
            background-color: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffe0b2;
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            .ip-row {
                flex-wrap: wrap;
            }
            
            .ip-item {
                min-width: calc(50% - 10px);
            }
        }
        
        @media (max-width: 480px) {
            .ip-item {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Redes Municipales</h1>
            <p>Administración y monitoreo de direcciones IP</p>
        </header>
        
        <?php
        // Datos de la base de datos - IPs en uso
        $redes = [
            ['id' => 1, 'nombre_red' => 'Informatica', 'tipo_red' => 'Terminales', 'direccion_ip' => '172.20.99.223', 'estado' => 'activa'],
            ['id' => 3, 'nombre_red' => 'Informatica', 'tipo_red' => 'Impresoras', 'direccion_ip' => '172.20.99.182', 'estado' => 'activa'],
            ['id' => 4, 'nombre_red' => 'Rentas', 'tipo_red' => 'Router', 'direccion_ip' => '172.20.99.23', 'estado' => 'activa'],
            ['id' => 5, 'nombre_red' => 'Casa de Campo', 'tipo_red' => 'Relojes', 'direccion_ip' => '172.20.99.239', 'estado' => 'activa'],
            ['id' => 6, 'nombre_red' => 'Servidor WEB', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.5', 'estado' => 'activa'],
            ['id' => 7, 'nombre_red' => 'Camaras', 'tipo_red' => 'Otro', 'direccion_ip' => '172.20.99.91', 'estado' => 'activa'],
            ['id' => 8, 'nombre_red' => 'Iomega', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.1', 'estado' => 'activa'],
            ['id' => 9, 'nombre_red' => 'Servidor Sifim', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.2', 'estado' => 'activa'],
            ['id' => 10, 'nombre_red' => 'Servidor Rafam', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.3', 'estado' => 'activa'],
            ['id' => 11, 'nombre_red' => 'Servidor Remoto', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.4', 'estado' => 'activa'],
            ['id' => 12, 'nombre_red' => 'Servidor Debian', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.6', 'estado' => 'activa'],
            ['id' => 13, 'nombre_red' => 'Servidor Fomuvi', 'tipo_red' => 'Servidor', 'direccion_ip' => '172.20.99.7', 'estado' => 'activa'],
            ['id' => 14, 'nombre_red' => 'Compras', 'tipo_red' => 'Terminales', 'direccion_ip' => '172.20.99.11', 'estado' => 'activa'],
            ['id' => 15, 'nombre_red' => 'Compras', 'tipo_red' => 'Terminales', 'direccion_ip' => '172.20.99.12', 'estado' => 'activa'],
            ['id' => 16, 'nombre_red' => 'Rentas', 'tipo_red' => 'Terminales', 'direccion_ip' => '172.20.99.40', 'estado' => 'activa'],
            ['id' => 17, 'nombre_red' => 'Asesoría Legal', 'tipo_red' => 'Impresoras', 'direccion_ip' => '172.20.99.74', 'estado' => 'activa'],
            ['id' => 18, 'nombre_red' => 'Rentas', 'tipo_red' => 'Impresoras', 'direccion_ip' => '172.20.99.102', 'estado' => 'activa'],
            ['id' => 19, 'nombre_red' => 'Colonia', 'tipo_red' => 'Router', 'direccion_ip' => '172.20.99.140', 'estado' => 'activa'],
            ['id' => 20, 'nombre_red' => 'Informatica', 'tipo_red' => 'Terminales', 'direccion_ip' => '172.20.99.220', 'estado' => 'activa'],
            ['id' => 21, 'nombre_red' => 'Palacio Municipal', 'tipo_red' => 'Relojes', 'direccion_ip' => '172.20.99.240', 'estado' => 'activa']
        ];
        
        // Obtener todas las IPs en uso
        $ips_en_uso = array_column($redes, 'direccion_ip');
        
        // Definir el rango de IPs a verificar (basado en la máscara 255.255.254.0)
        $ip_base = '172.20.99.';
        $inicio_rango = 1;
        $fin_rango = 254;
        
        // Contadores
        $total_ips = $fin_rango - $inicio_rango + 1;
        $ips_con_ping = 0;
        $ips_sin_ping = 0;
        $ips_libres = 0;
        
        // Identificar IPs libres
        $ips_libres_lista = [];
        for ($i = $inicio_rango; $i <= $fin_rango; $i++) {
            $ip = $ip_base . $i;
            if (!in_array($ip, $ips_en_uso)) {
                $ips_libres_lista[] = $ip;
                $ips_libres++;
            } else {
                // Simular verificación de ping (en un caso real, aquí harías ping a la IP)
                // Para este ejemplo, asumiremos que algunas IPs responden y otras no
                $responde_ping = rand(0, 1); // Simulación aleatoria
                if ($responde_ping) {
                    $ips_con_ping++;
                } else {
                    $ips_sin_ping++;
                }
            }
        }
        ?>
        
        <div class="stats-container">
            <div class="stat-card total-ips">
                <h3>Total de IPs en el Rango</h3>
                <div class="number"><?php echo $total_ips; ?></div>
            </div>
            
            <div class="stat-card ips-en-uso">
                <h3>IPs en Uso</h3>
                <div class="number"><?php echo count($ips_en_uso); ?></div>
            </div>
            
            <div class="stat-card ips-libres">
                <h3>IPs Libres</h3>
                <div class="number"><?php echo $ips_libres; ?></div>
            </div>
            
            <div class="stat-card ips-sin-ping">
                <h3>IPs en Uso Sin Ping</h3>
                <div class="number"><?php echo $ips_sin_ping; ?></div>
            </div>
        </div>
        
        <div class="tabs">
            <div class="tab active" data-tab="ips-libres">IPs Libres</div>
            <div class="tab" data-tab="ips-ocupadas">IPs Ocupadas</div>
            <div class="tab" data-tab="todas-ips">Todas las IPs</div>
        </div>
        
        <div class="search-box">
            <input type="text" id="search-ip" placeholder="Buscar IP específica (ej: 172.20.99.100)...">
            <button id="search-btn">Buscar</button>
        </div>
        
        <div class="tab-content active" id="ips-libres-content">
            <h2>Direcciones IP Libres Disponibles</h2>
            <p>Total: <?php echo count($ips_libres_lista); ?> IPs libres en el rango <?php echo $ip_base . $inicio_rango; ?> - <?php echo $ip_base . $fin_rango; ?></p>
            
            <div class="ips-grid">
                <?php
                // Mostrar las IPs libres en una cuadrícula
                $columnas = 5;
                $filas = ceil(count($ips_libres_lista) / $columnas);
                
                for ($fila = 0; $fila < $filas; $fila++) {
                    echo '<div class="ip-row">';
                    for ($col = 0; $col < $columnas; $col++) {
                        $indice = $fila * $columnas + $col;
                        if ($indice < count($ips_libres_lista)) {
                            echo '<div class="ip-item ip-libre">' . $ips_libres_lista[$indice] . '</div>';
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="tab-content" id="ips-ocupadas-content">
            <h2>Direcciones IP en Uso</h2>
            <p>Total: <?php echo count($ips_en_uso); ?> IPs en uso</p>
            <table>
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>Nombre de Red</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Respuesta Ping</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($redes as $red) {
                        // Simular estado de ping
                        $responde_ping = rand(0, 1);
                        $estado_ping = $responde_ping ? "Responde" : "No responde";
                        $clase_ping = $responde_ping ? "ip-ocupada" : "ip-sin-ping";
                        
                        echo "<tr>";
                        echo "<td class='$clase_ping'>{$red['direccion_ip']}</td>";
                        echo "<td>{$red['nombre_red']}</td>";
                        echo "<td>{$red['tipo_red']}</td>";
                        echo "<td>{$red['estado']}</td>";
                        echo "<td class='$clase_ping'>$estado_ping</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="tab-content" id="todas-ips-content">
            <h2>Todas las Direcciones IP del Rango</h2>
            <p>Rango completo: <?php echo $ip_base . $inicio_rango; ?> - <?php echo $ip_base . $fin_rango; ?></p>
            <div class="ips-grid">
                <?php
                // Mostrar todas las IPs del rango
                $columnas = 8;
                $filas = ceil($total_ips / $columnas);
                
                for ($fila = 0; $fila < $filas; $fila++) {
                    echo '<div class="ip-row">';
                    for ($col = 0; $col < $columnas; $col++) {
                        $ip_num = $fila * $columnas + $col + $inicio_rango;
                        if ($ip_num <= $fin_rango) {
                            $ip = $ip_base . $ip_num;
                            $clase = in_array($ip, $ips_en_uso) ? "ip-ocupada" : "ip-libre";
                            echo '<div class="ip-item ' . $clase . '">' . $ip . '</div>';
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <footer>
            <p>Sistema de Gestión de Redes Municipales &copy; <?php echo date('Y'); ?></p>
            <p>Base de datos actualizada el: <?php echo date('d/m/Y H:i:s'); ?></p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funcionalidad de pestañas
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remover clase active de todas las pestañas y contenidos
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Agregar clase active a la pestaña clickeada
                    tab.classList.add('active');
                    
                    // Mostrar el contenido correspondiente
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId + '-content').classList.add('active');
                });
            });
            
            // Funcionalidad de búsqueda
            const searchInput = document.getElementById('search-ip');
            const searchBtn = document.getElementById('search-btn');
            
            searchBtn.addEventListener('click', buscarIP);
            searchInput.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    buscarIP();
                }
            });
            
            function buscarIP() {
                const ipBuscada = searchInput.value.trim();
                if (ipBuscada) {
                    // Verificar si la IP está en uso
                    const ipsEnUso = <?php echo json_encode($ips_en_uso); ?>;
                    const ipsLibres = <?php echo json_encode($ips_libres_lista); ?>;
                    
                    if (ipsEnUso.includes(ipBuscada)) {
                        alert(`La IP ${ipBuscada} está EN USO.`);
                        // Cambiar a la pestaña de IPs ocupadas
                        tabs.forEach(t => t.classList.remove('active'));
                        tabContents.forEach(c => c.classList.remove('active'));
                        document.querySelector('.tab[data-tab="ips-ocupadas"]').classList.add('active');
                        document.getElementById('ips-ocupadas-content').classList.add('active');
                    } else if (ipsLibres.includes(ipBuscada)) {
                        alert(`La IP ${ipBuscada} está LIBRE.`);
                        // Cambiar a la pestaña de IPs libres
                        tabs.forEach(t => t.classList.remove('active'));
                        tabContents.forEach(c => c.classList.remove('active'));
                        document.querySelector('.tab[data-tab="ips-libres"]').classList.add('active');
                        document.getElementById('ips-libres-content').classList.add('active');
                    } else {
                        alert(`La IP ${ipBuscada} no está en el rango válido (<?php echo $ip_base . $inicio_rango; ?> - <?php echo $ip_base . $fin_rango; ?>).`);
                    }
                } else {
                    alert('Por favor, ingresa una dirección IP para buscar.');
                }
            }
        });
    </script>
</body>
</html>