<?php
  // Initialize the session
  session_start();
  
  // Check if the user is logged in, if not then redirect him to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: ../ingresar.php");
      exit;
  }

	include('../assets/inc/condb.php');	
	include('../assets/inc/funciones.php');

	$cuit = $_SESSION['cuitcuil'];
  $ok = $_GET['ok'];
  $organismo = $_SESSION["tipoOrganismo"]." ".$_SESSION["nombreOrganismo"];

    // Configuración de paginación
    $limit = 20;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page > 1) ? ($page * $limit) - $limit : 0;

    // Búsqueda y filtros
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
    $filter_estado = isset($_GET['filter_estado']) ? $_GET['filter_estado'] : 'todas';

    // FUNCIÓN PARA FORZAR VERIFICACIÓN DE PING MANUAL
    function forzarVerificacionPing($condb, $ip_id = null) {
        $fecha_actual = date('Y-m-d H:i:s');
        $ips_verificadas = 0;
        
        try {
            if ($ip_id) {
                // Verificación individual
                $sql = "SELECT id, direccion_ip FROM redes_municipales.redes WHERE id = ?";
                $stmt = $condb->prepare($sql);
                $stmt->bind_param("i", $ip_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $estado = hacerPing($row['direccion_ip']) ? 1 : 0;
                    
                    $sql_update = "UPDATE redes_municipales.redes 
                                  SET ping = ?, ultimo_check = ? 
                                  WHERE id = ?";
                    $stmt_update = $condb->prepare($sql_update);
                    $stmt_update->bind_param("isi", $estado, $fecha_actual, $ip_id);
                    $stmt_update->execute();
                    
                    $ips_verificadas = 1;
                }
            } else {
                // Verificación masiva de todas las IPs activas
                $sql = "SELECT id, direccion_ip FROM redes_municipales.redes WHERE estado = 'activa'";
                $result = $condb->query($sql);
                
                while ($row = $result->fetch_assoc()) {
                    $estado = hacerPing($row['direccion_ip']) ? 1 : 0;
                    
                    $sql_update = "UPDATE redes_municipales.redes 
                                  SET ping = ?, ultimo_check = ? 
                                  WHERE id = ?";
                    $stmt_update = $condb->prepare($sql_update);
                    $stmt_update->bind_param("isi", $estado, $fecha_actual, $row['id']);
                    $stmt_update->execute();
                    
                    $ips_verificadas++;
                }
            }
            
            return $ips_verificadas;
            
        } catch (Exception $e) {
            error_log("Error en forzarVerificacionPing: " . $e->getMessage());
            return 0;
        }
    }

    // Construir consulta base - SOLO IPS ACTIVAS
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM redes_municipales.redes WHERE estado = 'activa'";
    $count_sql = "SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa'";

    // Filtro por estado (todas, con ping, sin ping)
    if ($filter_estado === 'con_ping') {
        $sql .= " AND ping = 1";
        $count_sql .= " AND ping = 1";
    } elseif ($filter_estado === 'sin_ping') {
        $sql .= " AND ping = 0";
        $count_sql .= " AND ping = 0";
    }
    // Por defecto mostrar TODAS las activas (con y sin ping)

    if (!empty($search)) {
        $sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%')";
        $count_sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%')";
    }

    if (!empty($filter_type)) {
        $sql .= " AND tipo_red = '$filter_type'";
        $count_sql .= " AND tipo_red = '$filter_type'";
    }

    // Ordenar por IP: primero las que terminan en .99, luego las que terminan en .98, luego el resto
    $sql .= " ORDER BY 
              CASE 
                WHEN direccion_ip LIKE '172.20.99.%' THEN 1
                WHEN direccion_ip LIKE '172.20.98.%' THEN 2
                ELSE 3
              END,
              INET_ATON(direccion_ip) ASC 
              LIMIT $start, $limit";

    $resultado = $condb->query($sql);
    $total_resultados = $condb->query("SELECT FOUND_ROWS() as total");
    $total_resultados = $total_resultados->fetch_assoc()['total'];
    $total_paginas = ceil($total_resultados / $limit);

    // Obtener estadísticas para el dashboard
    $stats = [
        'total_activas_con_ping' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa' AND ping = 1")->fetch_assoc()['total'],
        'total_activas_sin_ping' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa' AND ping = 0")->fetch_assoc()['total'],
        'total_todas_activas' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa'")->fetch_assoc()['total']
    ];

    // Obtener estadísticas por tipo de dispositivo
    $tipos_dispositivos = [
        'terminales' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Terminales' AND estado = 'activa'")->fetch_assoc()['total'],
        'impresoras' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Impresoras' AND estado = 'activa'")->fetch_assoc()['total'],
        'routers' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Router' AND estado = 'activa'")->fetch_assoc()['total'],
        'servidores' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Servidor' AND estado = 'activa'")->fetch_assoc()['total'],
        'relojes' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Relojes' AND estado = 'activa'")->fetch_assoc()['total'],
        'otros' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red NOT IN ('Terminales', 'Impresoras', 'Router', 'Servidor', 'Relojes') AND estado = 'activa'")->fetch_assoc()['total']
    ];

function hacerPing($ip) {
    // Validar IP o dominio
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        // Intentar resolver dominio
        $ip_resuelta = gethostbyname($ip);
        if ($ip_resuelta === $ip) {
            return false; // No se pudo resolver
        }
        $ip = $ip_resuelta;
    }
    
    // Ejecutar ping
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $comando = "ping -n 2 -w 1000 " . escapeshellarg($ip);
    } else {
        $comando = "ping -c 2 -W 1 " . escapeshellarg($ip);
    }
    
    $output = [];
    $resultado = 0;
    exec($comando, $output, $resultado);
    
    return $resultado === 0;
}

// API para verificación en tiempo real Y escaneo de red
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'verificar_todas':
            $ips_verificadas = forzarVerificacionPing($condb);
            
            // Obtener estados actualizados
            $sql_estados = "SELECT id, ping, ultimo_check FROM redes_municipales.redes WHERE estado = 'activa'";
            $result = $condb->query($sql_estados);
            $estados = [];
            
            while ($row = $result->fetch_assoc()) {
                $estados[] = [
                    'id' => $row['id'],
                    'estado' => $row['ping'],
                    'ultimo_check' => $row['ultimo_check']
                ];
            }
            
            echo json_encode([
                'success' => true, 
                'estados' => $estados,
                'ips_verificadas' => $ips_verificadas
            ]);
            break;
            
        case 'verificar_individual':
            $ip_id = intval($_GET['ip_id']);
            $ips_verificadas = forzarVerificacionPing($condb, $ip_id);
            
            if ($ips_verificadas > 0) {
                // Obtener el estado actualizado
                $sql = "SELECT ping, ultimo_check FROM redes_municipales.redes WHERE id = ?";
                $stmt = $condb->prepare($sql);
                $stmt->bind_param("i", $ip_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo json_encode([
                        'success' => true, 
                        'estado' => $row['ping'],
                        'timestamp' => $row['ultimo_check']
                    ]);
                } else {
                    echo json_encode(['success' => false]);
                }
            } else {
                echo json_encode(['success' => false]);
            }
            break;
            
        case 'obtener_estados':
            $sql = "SELECT id, ping, ultimo_check FROM redes_municipales.redes WHERE estado = 'activa'";
            $result = $condb->query($sql);
            $estados = [];
            
            while ($row = $result->fetch_assoc()) {
                $estados[] = [
                    'id' => $row['id'],
                    'estado' => $row['ping'],
                    'ultimo_check' => $row['ultimo_check']
                ];
            }
            
            echo json_encode(['success' => true, 'estados' => $estados]);
            break;
            
        case 'escanear_red':
            $resultado_escaneo = escanearRed($condb);
            echo json_encode([
                'success' => true, 
                'resultado' => $resultado_escaneo
            ]);
            break;
            
        case 'verificar_y_actualizar':
            $ips_verificadas = forzarVerificacionPing($condb);
            
            // Obtener estados actualizados para la respuesta
            $sql_estados = "SELECT id, ping, ultimo_check FROM redes_municipales.redes WHERE estado = 'activa'";
            $result = $condb->query($sql_estados);
            $estados = [];
            
            while ($row = $result->fetch_assoc()) {
                $estados[] = [
                    'id' => $row['id'],
                    'estado' => $row['ping'],
                    'ultimo_check' => $row['ultimo_check']
                ];
            }
            
            echo json_encode([
                'success' => true, 
                'estados' => $estados,
                'ips_verificadas' => $ips_verificadas
            ]);
            break;
    }
    
    exit;
}

// Función optimizada para escanear la red
function escanearRed($condb) {
    $subred = "172.20.98.0/23";
    $ipsEncontradas = [];
    $nuevasIPs = 0;
    
    // Determinar el rango de IPs basado en la subred /23
    $ipPartes = explode('.', substr($subred, 0, strpos($subred, '/')));
    $cidr = (int)substr($subred, strpos($subred, '/') + 1);
    
    // Calcular el número de hosts (para /23: 512-2 = 510 hosts)
    $numHosts = pow(2, 32 - $cidr) - 2;
    $ipInicio = ip2long($ipPartes[0] . '.' . $ipPartes[1] . '.' . $ipPartes[2] . '.1');
    $ipFin = $ipInicio + $numHosts - 1;
    
    // Obtener todas las IPs existentes en la base de datos de una vez
    $sql_existentes = "SELECT direccion_ip FROM redes_municipales.redes";
    $result_existentes = $condb->query($sql_existentes);
    $ipsExistentes = [];
    while ($row = $result_existentes->fetch_assoc()) {
        $ipsExistentes[$row['direccion_ip']] = true;
    }
    
    // Preparar statement para inserción
    $sql_insert = "INSERT INTO redes_municipales.redes 
                  (nombre_red, tipo_red, direccion_ip, ubicacion, departamento, estado, ping, ultimo_check) 
                  VALUES (?, ?, ?, ?, ?, 'activa', ?, ?)";
    $stmt_insert = $condb->prepare($sql_insert);
    
    // Escanear las IPs en el rango en lotes para mejor rendimiento
    $loteSize = 20;
    $ipActual = $ipInicio;
    $contador = 0;
    
    while ($ipActual <= $ipFin) {
        $loteIPs = [];
        
        // Crear lote de IPs
        for ($i = 0; $i < $loteSize && $ipActual <= $ipFin; $i++, $ipActual++) {
            $loteIPs[] = long2ip($ipActual);
        }
        
        // Procesar lote actual
        foreach ($loteIPs as $ip) {
            $contador++;
            
            // Mostrar progreso cada 50 IPs
            if ($contador % 50 === 0) {
                echo "Procesando IP $contador de $numHosts...\n";
                flush();
            }
            
            // Saltar IP si ya existe en la base de datos
            if (isset($ipsExistentes[$ip])) {
                continue;
            }
            
            // Hacer ping a la IP
            if (hacerPing($ip)) {
                $ipsEncontradas[] = $ip;
                
                // Insertar nueva red
                $nombre_red = "Equipo " . $ip;
                $tipo_red = "Terminales";
                $ubicacion = "Por determinar";
                $departamento = "Por determinar";
                $ping = 1;
                $ultimo_check = date('Y-m-d H:i:s');
                
                $stmt_insert->bind_param("sssssis", $nombre_red, $tipo_red, $ip, $ubicacion, $departamento, $ping, $ultimo_check);
                if ($stmt_insert->execute()) {
                    $nuevasIPs++;
                }
                
                // Agregar a IPs existentes para evitar duplicados en este escaneo
                $ipsExistentes[$ip] = true;
            }
            
            // Pequeña pausa para no saturar la red
            usleep(5000); // 5ms
        }
    }
    
    $stmt_insert->close();
    
    return [
        'total_escaneadas' => $numHosts,
        'ips_encontradas' => count($ipsEncontradas),
        'nuevas_ips_agregadas' => $nuevasIPs,
        'ips' => $ipsEncontradas
    ];
}

?>

<!DOCTYPE html>
<html lang="es_ES">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="RED - <?php echo $organismo; ?>">
  <meta name="author" content="Informática MGB">
    <title>RED - <?php echo $organismo; ?></title>
    <!-- Favicon -->
    <link href="../assets/img/brand/favicon.ico" rel="icon" type="image/png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <!-- Icons -->
    <link href="../assets/vendor/nucleo/css/nucleo.css" rel="stylesheet">
    <link href="../assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="../assets/css/argon.css?v=1.0.0" rel="stylesheet">
	  <link type="text/css" href="../assets/css/custom.css" rel="stylesheet">
    <style>
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            transition: all 0.3s ease;
        }
        .online { background-color: #28a745; box-shadow: 0 0 8px rgba(40, 167, 69, 0.5); }
        .offline { background-color: #dc3545; }
        .checking { background-color: #ffc107; animation: pulse 1s infinite; }
        
        .status-cell {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .table-status {
            text-align: center;
            min-width: 120px;
        }

        .alert-info-custom {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .search-box input {
            width: 100%;
            padding: 8px 12px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            font-size: 14px;
            min-width: 150px;
        }

        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .clickable-row:hover {
            background-color: #f8f9fa !important;
        }

        .actions-column {
            text-align: center;
            min-width: 120px;
        }

        .actions-column .btn {
            margin: 2px;
        }

        .modal.show {
            display: block !important;
        }

        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        .text-purple {
            color: #6f42c1 !important;
        }
        
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }

        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: #3498db;
            font-weight: 600;
        }

        .password-field {
            font-family: 'Courier New', monospace;
        }

        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            100% { background-position: 0 0; }
        }

        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                width: 100%;
            }
            
            .search-box,
            .filter-select {
                width: 100%;
                min-width: auto;
            }
            
            .card-header .row {
                text-align: center;
            }
            
            .card-header .col-lg-8 {
                margin-top: 10px;
            }

            .actions-column {
                min-width: 80px;
            }

            .actions-column .btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .nav-tabs .nav-link {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .search-box input {
                font-size: 16px;
            }
        }

    </style>
</head>

<body style="margin:0;">
  <!-- Sidenav -->
  <?php include('menulateral.php');	 ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
      <div class="container-fluid">
        <!-- Brand -->
        <a class="h2 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="index.php">REDES MUNICIPALES</a>	
        <!-- User -->
		    <?php include('topnav.php');?>
      </div>
    </nav>

    <!-- Header -->
    <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center">
      <!-- Header container -->
        <div class="container-fluid d-flex">
          <div class="container-fluid mt--2">
					<div class="row">
						<div class="col-xl-2 col-lg-2 col-4">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">EN LINEA</h5>
											<span class="h3 font-weight-bold mb-0">
                        <?php echo $stats['total_activas_con_ping']; ?> 
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-2 col-4">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">SIN RESPUESTA</h5>
											<span class="h3 font-weight-bold mb-0 text-warning">
                        <?php echo $stats['total_activas_sin_ping']; ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-2 col-4">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">TOTAL IP ACTIVAS</h5>
											<span class="h3 font-weight-bold mb-0 text-primary">
                        <?php echo $stats['total_todas_activas']; ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
              <div class="col-xl-2 col-lg-2 col-4">
                  <div class="card card-stats mb-4 mb-xl-0">
                      <div class="card-body">
                          <div class="row">
                              <div class="col">
                                  <h5 class="card-title text-uppercase text-muted mb-0">TERMINALES</h5>
                                  <span class="h3 font-weight-bold mb-0 text-primary">
                                      <?php echo $tipos_dispositivos['terminales']; ?>
                                  </span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-xl-2 col-lg-2 col-4">
                  <div class="card card-stats mb-4 mb-xl-0">
                      <div class="card-body">
                          <div class="row">
                              <div class="col">
                                  <h5 class="card-title text-uppercase text-muted mb-0">IMPRESORAS</h5>
                                  <span class="h3 font-weight-bold mb-0 text-success">
                                      <?php echo $tipos_dispositivos['impresoras']; ?>
                                  </span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-xl-2 col-lg-2 col-4">
                  <div class="card card-stats mb-4 mb-xl-0">
                      <div class="card-body">
                          <div class="row">
                              <div class="col">
                                  <h5 class="card-title text-uppercase text-muted mb-0">ROUTERS</h5>
                                  <span class="h3 font-weight-bold mb-0 text-warning">
                                      <?php echo $tipos_dispositivos['routers']; ?>
                                  </span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
					</div>

            <!-- Alertas -->
            <?php if ($ok == 1) { ?>
              <div class="container">
                <div class="row justify-content-center">
                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>Inscripción guardada con Exito!</strong>
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                    </div> 
                  </div>
                </div>
              </div>
              <?php }        if ($ok == 2) { ?>
              <div class="container">
                <div class="row justify-content-center">
                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <strong>Complete todos los datos solicitados, intente nuevamente!</strong>
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                    </div> 
                  </div>
                </div>
              </div>
            <?php } if ($ok == 3) { ?>
              <div class="container">
                <div class="row justify-content-center">
                  <div class="col-lg-8 col-md-8 col-sm-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>Se ha eliminado la inscripción!</strong>
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                    </div> 
                  </div>
                </div>
              </div>
            <?php } ?>

          </div>  
        </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <!-- Controles de verificación -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                  <div class="btn-group mr-3">
                      <button id="btnVerificarTodas" class="btn btn-primary btn-sm">
                          <span class="spinner-border spinner-border-sm d-none" id="spinnerTodas"></span>
                          🔄 Verificar Todas
                      </button>
                      <!-- BOTÓN DE ESCANEO DE RED -->
                      <button id="btnEscanearRed" class="btn btn-info btn-sm">
                          <span class="spinner-border spinner-border-sm d-none" id="spinnerEscanear"></span>
                          🔍 Escanear Red
                      </button>
                  </div>
              </div>
              <div id="loading" class="d-none">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                      <span class="sr-only">Verificando...</span>
                  </div>
              </div>
          </div>
        </div>
      </div>

      <!-- SISTEMA DE PESTAÑAS -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="redes-tab" data-toggle="tab" href="#redes" role="tab" aria-controls="redes" aria-selected="true">
                    Redes Activas
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="correos-tab" data-toggle="tab" href="#correos" role="tab" aria-controls="correos" aria-selected="false">
                    Credenciales de Correos
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="carpetas-tab" data-toggle="tab" href="#carpetas" role="tab" aria-controls="carpetas" aria-selected="false">
                   Credenciales de Carpetas
                  </a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content" id="myTabContent">
                <!-- Pestaña de Redes (contenido existente) -->
                <div class="tab-pane fade show active" id="redes" role="tabpanel" aria-labelledby="redes-tab">
                  <!-- Table -->
                  <div class="row">  
                    <div class="col-xl-12 order-xl-1 mb-5 mb-xl-0">
                      <div class="card shadow">
                        <div class="card-header border-0">
                          <div class="row align-items-center"> 
                            <div class="col-lg-4 col-md-5 col-sm-12 mb-2 mb-lg-0">			  
                              <h3 class="mb-0">Redes Activas</h3>
                            </div>
                            <div class="col-lg-8 col-md-7 col-sm-12">
                              <div class="d-flex flex-column flex-md-row justify-content-end align-items-stretch align-items-md-center gap-2">
                                <!-- Buscador -->
                                <div class="search-box flex-fill">
                                  <i class="fas fa-search"></i>
                                  <input type="text" id="search-input" placeholder="Buscar redes..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                
                                <!-- Filtro por tipo -->
                                <select class="filter-select flex-shrink-0" id="filter-type" style="min-width: 150px;">
                                  <option value="">Todos los tipos</option>
                                  <option value="Terminales" <?php echo $filter_type == 'Terminales' ? 'selected' : ''; ?>>Terminales</option>
                                  <option value="Impresoras" <?php echo $filter_type == 'Impresoras' ? 'selected' : ''; ?>>Impresoras</option>
                                  <option value="Router" <?php echo $filter_type == 'Router' ? 'selected' : ''; ?>>Router</option>
                                  <option value="Servidor" <?php echo $filter_type == 'Servidor' ? 'selected' : ''; ?>>Servidor</option>
                                  <option value="Relojes" <?php echo $filter_type == 'Relojes' ? 'selected' : ''; ?>>Relojes</option>
                                  <option value="Otro" <?php echo $filter_type == 'Otro' ? 'selected' : ''; ?>>Otros</option>
                                </select>

                                <!-- Filtro por estado -->
                                <select class="filter-select flex-shrink-0" id="filter-estado" style="min-width: 150px;">
                                  <option value="todas" <?php echo $filter_estado == 'todas' ? 'selected' : ''; ?>>Todas las activas</option>
                                  <option value="con_ping" <?php echo $filter_estado == 'con_ping' ? 'selected' : ''; ?>>En Linea</option>
                                  <option value="sin_ping" <?php echo $filter_estado == 'sin_ping' ? 'selected' : ''; ?>>Sin Respuesta</option>
                                </select>

                                <!-- Botón Agregar Red -->
                                <a href="agregar.php" class="btn btn-primary flex-shrink-0">
                                  <i class="fas fa-plus"></i> Agregar Red
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="table-responsive table-sm">
                          <table class="table align-items-center table-flush table-hover table-sm">
                            <thead class="thead-light">
                              <tr>
                                <th>Estado</th>
                                <th>Nombre de Red</th>
                                <th>Tipo</th>
                                <th>Dirección IP</th>
                                <th>Ubicación</th>
                                <th>Departamento</th>
                                <th>Última Verificación</th>
                                <th class="actions-column">Acciones</th>
                              </tr>
                            </thead>
                            <tbody id="tabla-redes">
                              <?php if ($resultado->num_rows > 0): ?>
                              <?php while($fila = $resultado->fetch_assoc()): 
                                $estado = $fila['ping'] ?? 0;
                                
                                // Determinar clase y texto según el estado
                                $claseEstado = $estado ? 'online' : 'offline';
                                $textoEstado = $estado ? 'En línea' : 'Sin respuesta';
                                $textColor = $estado ? 'text-success' : 'text-danger';
                                
                                $ultimoCheck = $fila['ultimo_check'] ? date('H:i:s d/m/Y', strtotime($fila['ultimo_check'])) : 'No verificado';
                              ?>
                              <tr id="fila-<?php echo $fila['id']; ?>" data-ip-id="<?php echo $fila['id']; ?>" class="clickable-row openBtnRed" data-toggle="modal" data-id="<?php echo $fila['id']; ?>" data-target="#verRed">
                                <td class="table-status">
                                  <div class="status-cell">
                                    <span class="status-indicator <?php echo $claseEstado; ?>" id="status-<?php echo $fila['id']; ?>"></span>
                                    <small class="<?php echo $textColor; ?>" id="text-<?php echo $fila['id']; ?>">
                                      <?php echo $textoEstado; ?>
                                    </small>
                                  </div>
                                </td>
                                <td>
                                  <?php echo htmlspecialchars($fila['nombre_red']); ?>
                                  <?php if ($estado == 0): ?>
                                    <span class="badge badge-danger ml-1">Sin Ping</span>
                                  <?php endif; ?>
                                </td>
                                <td>
                                  <span class="badge 
                                    <?php echo $fila['tipo_red'] == 'Terminales' ? 'badge-info' : ''; ?>
                                    <?php echo $fila['tipo_red'] == 'Impresoras' ? 'badge-success' : ''; ?>
                                    <?php echo $fila['tipo_red'] == 'Router' ? 'badge-warning' : ''; ?>
                                    <?php echo $fila['tipo_red'] == 'Servidor' ? 'badge-danger' : ''; ?>
                                    <?php echo $fila['tipo_red'] == 'Otro' ? 'badge-secondary' : ''; ?>
                                    <?php echo $fila['tipo_red'] == 'Relojes' ? 'badge-purple' : ''; ?>">
                                    <?php echo htmlspecialchars($fila['tipo_red']); ?>
                                  </span>
                                </td>
                                <td><?php echo htmlspecialchars($fila['direccion_ip']); ?></td>
                                <td><?php echo htmlspecialchars($fila['ubicacion']); ?></td>
                                <td><?php echo htmlspecialchars($fila['departamento']); ?></td>
                                <td id="time-<?php echo $fila['id']; ?>"><?php echo $ultimoCheck; ?></td>
                                <td class="actions-column">
                                  <button class="btn btn-info btn-sm btn-verificar-individual" data-ip-id="<?php echo $fila['id']; ?>" title="Verificar ahora">
                                    <i class="fas fa-sync-alt"></i>
                                  </button>
                                </td>
                              </tr>
                              <?php endwhile; ?>
                              <?php else: ?>
                              <tr>
                                <td colspan="8" style="text-align: center;">
                                  <?php if ($filter_estado == 'con_ping'): ?>
                                    No se encontraron IPs activas con respuesta.
                                  <?php elseif ($filter_estado == 'sin_ping'): ?>
                                    No se encontraron IPs activas sin respuesta.
                                  <?php else: ?>
                                    No se encontraron redes activas registradas.
                                  <?php endif; ?>
                                </td>
                              </tr>
                              <?php endif; ?>
                              </tbody>
                          </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                        <div class="card-footer">
                          <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                              <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?><?php echo !empty($filter_estado) ? '&filter_estado='.urlencode($filter_estado) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                              </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                              <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?><?php echo !empty($filter_estado) ? '&filter_estado='.urlencode($filter_estado) : ''; ?>">
                                <?php echo $i; ?>
                              </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_paginas): ?>
                            <li class="page-item">
                              <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?><?php echo !empty($filter_estado) ? '&filter_estado='.urlencode($filter_estado) : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                              </a>
                            </li>
                            <?php endif; ?>
                          </ul>
                        </div>
                        <?php endif; ?>
                      </div>
                    </div>				
                  </div>
                </div>

                <!-- Pestaña de Correos -->
                <div class="tab-pane fade" id="correos" role="tabpanel" aria-labelledby="correos-tab">
                  <?php include('correos_tab.php'); ?>
                </div>

                <!-- Pestaña de Carpetas -->
                <div class="tab-pane fade" id="carpetas" role="tabpanel" aria-labelledby="carpetas-tab">
                  <?php include('carpetas_tab.php'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <br><br>
      <?php include "../footer.php"; ?> 
    </div>
  </div>

  <!-- Argon Scripts -->
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/argon.js?v=1.0.0"></script>  
  
  <script>
  class IPMonitor {
      constructor() {
          this.isChecking = false;
          this.init();
      }
      
      init() {
          this.bindEvents();
      }
      
      bindEvents() {
          // Verificar todas las IPs
          $('#btnVerificarTodas').click(() => this.verificarTodas());
          
          // Verificar IP individual
          $(document).on('click', '.btn-verificar-individual', (e) => {
              e.stopPropagation();
              const ipId = $(e.currentTarget).data('ip-id');
              this.verificarIndividual(ipId);
          });
          
          // Escanear red
          $('#btnEscanearRed').click(() => this.escanearRed());
      }

      escanearRed() {
          if (this.isChecking) return;
          
          this.isChecking = true;
          $('#spinnerEscanear').removeClass('d-none');
          $('#btnEscanearRed').prop('disabled', true);
          
          if (!confirm('¿Está seguro de que desea escanear la red 172.20.98.0/23?\n\nEsto escaneará 510 direcciones IP y puede tomar 5-10 minutos.\n\nRecomendamos realizar esta acción durante horarios de menor actividad.')) {
              this.isChecking = false;
              $('#spinnerEscanear').addClass('d-none');
              $('#btnEscanearRed').prop('disabled', false);
              return;
          }
          
          this.mostrarModalProgreso();
          
          $.getJSON('?action=escanear_red', (data) => {
              if (data.success) {
                  const resultado = data.resultado;
                  const mensaje = `✅ Escaneo completado:\n\n` +
                                 `• IPs escaneadas: ${resultado.total_escaneadas}\n` +
                                 `• IPs activas encontradas: ${resultado.ips_encontradas}\n` +
                                 `• Nuevas IPs agregadas: ${resultado.nuevas_ips_agregadas}\n\n` +
                                 `La base de datos ha sido actualizada.`;
                  
                  this.ocultarModalProgreso();
                  this.mostrarResultadosEscaneo(mensaje);
                  
                  setTimeout(() => {
                      window.location.reload();
                  }, 3000);
              }
          }).fail(() => {
              this.ocultarModalProgreso();
              alert('❌ Error al escanear la red. Verifique la conexión e intente nuevamente.');
          }).always(() => {
              this.isChecking = false;
              $('#spinnerEscanear').addClass('d-none');
              $('#btnEscanearRed').prop('disabled', false);
          });
      }

      mostrarModalProgreso() {
          const modalHTML = `
          <div class="modal fade show" id="modalProgreso" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
              <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title">🔍 Escaneando red...</h5>
                      </div>
                      <div class="modal-body text-center">
                          <div class="spinner-border text-primary mb-3" role="status">
                              <span class="sr-only">Escaneando...</span>
                          </div>
                          <p>Escaneando red 172.20.98.0/23 (510 direcciones IP)</p>
                          <div class="progress">
                              <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                   role="progressbar" style="width: 100%"></div>
                          </div>
                          <small class="text-muted">Esto puede tomar varios minutos...</small>
                      </div>
                  </div>
              </div>
          </div>`;
          
          $('body').append(modalHTML);
          $('#modalProgreso').addClass('show');
      }

      ocultarModalProgreso() {
          $('#modalProgreso').remove();
      }

      mostrarResultadosEscaneo(mensaje) {
          const modalHTML = `
          <div class="modal fade show" id="modalResultados" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
              <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                      <div class="modal-header bg-success text-white">
                          <h5 class="modal-title">✅ Escaneo completado</h5>
                      </div>
                      <div class="modal-body">
                          <pre style="white-space: pre-wrap; font-family: inherit;">${mensaje}</pre>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-primary" onclick="$('#modalResultados').remove(); window.location.reload();">
                              Aceptar y recargar
                          </button>
                      </div>
                  </div>
              </div>
          </div>`;
          
          $('body').append(modalHTML);
          $('#modalResultados').addClass('show');
      }
      
      verificarTodas() {
          if (this.isChecking) return;
          
          this.isChecking = true;
          $('#loading').removeClass('d-none');
          $('#spinnerTodas').removeClass('d-none');
          $('#btnVerificarTodas').prop('disabled', true);
          
          $('.status-indicator').removeClass('online offline').addClass('checking');
          
          $.getJSON('?action=verificar_y_actualizar', (data) => {
              if (data.success) {
                  this.actualizarEstados(data.estados);
                  if (data.ips_verificadas > 0) {
                      alert(`✅ Se verificaron ${data.ips_verificadas} IPs`);
                  }
              }
          }).always(() => {
              this.isChecking = false;
              $('#loading').addClass('d-none');
              $('#spinnerTodas').addClass('d-none');
              $('#btnVerificarTodas').prop('disabled', false);
          });
      }
      
      verificarIndividual(ipId) {
          const btn = $(`[data-ip-id="${ipId}"] .btn-verificar-individual`);
          const statusIndicator = $(`#status-${ipId}`);
          
          btn.prop('disabled', true);
          btn.html('<i class="fas fa-spinner fa-spin"></i>');
          statusIndicator.removeClass('online offline').addClass('checking');
          
          $.getJSON(`?action=verificar_individual&ip_id=${ipId}`, (data) => {
              if (data.success) {
                  this.actualizarEstadoIndividual(ipId, data.estado, data.timestamp);
              }
          }).always(() => {
              btn.prop('disabled', false);
              btn.html('<i class="fas fa-sync-alt"></i>');
          });
      }
      
      actualizarEstados(estados) {
          estados.forEach(estado => {
              this.actualizarEstadoIndividual(estado.id, estado.estado, estado.ultimo_check);
          });
      }
      
      actualizarEstadoIndividual(ipId, estado, timestamp) {
          const statusIndicator = $(`#status-${ipId}`);
          const statusText = $(`#text-${ipId}`);
          const timeElement = $(`#time-${ipId}`);
          
          statusIndicator.removeClass('checking online offline');
          
          if (estado) {
              statusIndicator.addClass('online');
              statusText.html('En línea').removeClass('text-danger text-muted').addClass('text-success');
          } else {
              statusIndicator.addClass('offline');
              statusText.html('Sin respuesta').removeClass('text-success text-muted').addClass('text-danger');
          }
          
          if (timestamp) {
              const time = new Date(timestamp).toLocaleTimeString();
              timeElement.text(time);
          }
      }
  }
  
  // Inicializar el monitor cuando el documento esté listo
  $(document).ready(() => {
      new IPMonitor();
      
      // Manejar pestañas activas
      const urlParams = new URLSearchParams(window.location.search);
      const activeTab = urlParams.get('tab');
      
      if (activeTab) {
          $(`#${activeTab}-tab`).tab('show');
      }
      
      // Actualizar URLs de pestañas para incluir el parámetro tab
      $('a[data-toggle="tab"]').on('click', function(e) {
          const tabId = $(this).attr('id').replace('-tab', '');
          const newUrl = `index.php?tab=${tabId}`;
          window.history.replaceState(null, null, newUrl);
      });
      
      // Funcionalidad de búsqueda
      $('#search-input').keyup(function(e) {
          if (e.key === 'Enter') {
              buscarRedes();
          }
      });

      $('#filter-type, #filter-estado').change(function() {
          buscarRedes();
      });

      function buscarRedes() {
          const searchValue = $('#search-input').val();
          const filterValue = $('#filter-type').val();
          const filterEstado = $('#filter-estado').val();
          
          let url = 'index.php?';
          let params = [];
          
          // Mantener la pestaña activa
          const activeTab = $('.nav-tabs .nav-link.active').attr('id').replace('-tab', '');
          if (activeTab && activeTab !== 'redes') {
              params.push('tab=' + activeTab);
          }
          
          if (searchValue) params.push('search=' + encodeURIComponent(searchValue));
          if (filterValue) params.push('filter_type=' + encodeURIComponent(filterValue));
          if (filterEstado) params.push('filter_estado=' + encodeURIComponent(filterEstado));
          
          if (params.length > 0) {
              url += params.join('&');
          }
          
          window.location.href = url;
      }

      window.buscarRedes = buscarRedes;

      // Auto-ocultar alertas
      window.setTimeout(function() {
          $(".alert").fadeTo(1000, 0).slideUp(1000, function(){
              $(this).remove(); 
          });
      }, 2500);

      // Cargar modal al hacer clic en fila
      $('.openBtnRed').on('click',function(){
          var valor = $(this).data("id");
          $('.modal-content-red').load('verRed.php?id='+valor,function(){
              $('#verRed').modal({show:true});
          });
      });	
  });
  
  </script>

  <!-- Modal para ver detalles de red -->
  <div class="modal fade" id="verRed" tabindex="-1" role="dialog" aria-labelledby="verRedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content modal-content-red">
        <!-- El contenido se carga dinámicamente desde verRed.php -->
      </div>
    </div>
  </div>

</body>
</html>

<?php $condb->close(); ?>