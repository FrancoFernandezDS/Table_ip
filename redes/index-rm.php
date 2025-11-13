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
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page > 1) ? ($page * $limit) - $limit : 0;

    // Búsqueda y filtros
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';

    // Construir consulta base
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM redes_municipales.redes WHERE estado = 'activa'";
    $count_sql = "SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa'";

    if (!empty($search)) {
        $sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%' OR responsable LIKE '%$search%')";
        $count_sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%' OR responsable LIKE '%$search%')";
    }

    if (!empty($filter_type)) {
        $sql .= " AND tipo_red = '$filter_type'";
        $count_sql .= " AND tipo_red = '$filter_type'";
    }

    $sql .= " ORDER BY id DESC LIMIT $start, $limit";

    $resultado = $condb->query($sql);
    $total_resultados = $condb->query("SELECT FOUND_ROWS() as total");
    $total_resultados = $total_resultados->fetch_assoc()['total'];
    $total_paginas = ceil($total_resultados / $limit);

    // Obtener estadísticas para el dashboard
    $stats = [
        'total_redes' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE estado = 'activa'")->fetch_assoc()['total'],
        'redes_Terminales' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Terminales' AND estado = 'activa'")->fetch_assoc()['total'],
        'redes_Impresoras' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Impresoras' AND estado = 'activa'")->fetch_assoc()['total'],
        'redes_Router' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Router' AND estado = 'activa'")->fetch_assoc()['total'],
        'redes_Servidor' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Servidor' AND estado = 'activa'")->fetch_assoc()['total'],
        'redes_Relojes' => $condb->query("SELECT COUNT(*) as total FROM redes_municipales.redes WHERE tipo_red = 'Relojes' AND estado = 'activa'")->fetch_assoc()['total']
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
            $sql = "SELECT id, direccion_ip FROM redes_municipales.redes WHERE estado = 'activa'";
            $result = $condb->query($sql);
            $estados = [];
            
            while ($row = $result->fetch_assoc()) {
                $estado = hacerPing($row['direccion_ip']) ? 1 : 0;
                $fecha_actual = date('Y-m-d H:i:s');
                
                // Actualizar base de datos
                $sql_update = "UPDATE redes_municipales.redes SET ping = ?, ultimo_check = ? WHERE id = ?";
                $stmt = $condb->prepare($sql_update);
                $stmt->bind_param("isi", $estado, $fecha_actual, $row['id']);
                $stmt->execute();
                
                $estados[] = [
                    'id' => $row['id'],
                    'estado' => $estado,
                    'timestamp' => $fecha_actual
                ];
            }
            
            echo json_encode(['success' => true, 'estados' => $estados]);
            break;
            
        case 'verificar_individual':
            $ip_id = intval($_GET['ip_id']);
            $sql = "SELECT direccion_ip FROM redes_municipales.redes WHERE id = ?";
            $stmt = $condb->prepare($sql);
            $stmt->bind_param("i", $ip_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $estado = hacerPing($row['direccion_ip']) ? 1 : 0;
                $fecha_actual = date('Y-m-d H:i:s');
                
                $sql_update = "UPDATE redes_municipales.redes SET ping = ?, ultimo_check = ? WHERE id = ?";
                $stmt_update = $condb->prepare($sql_update);
                $stmt_update->bind_param("isi", $estado, $fecha_actual, $ip_id);
                $stmt_update->execute();
                
                echo json_encode([
                    'success' => true, 
                    'estado' => $estado,
                    'timestamp' => $fecha_actual
                ]);
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
    $sql_existentes = "SELECT direccion_ip FROM redes_municipales.redes WHERE estado = 'activa'";
    $result_existentes = $condb->query($sql_existentes);
    $ipsExistentes = [];
    while ($row = $result_existentes->fetch_assoc()) {
        $ipsExistentes[$row['direccion_ip']] = true;
    }
    
    // Preparar statement para inserción
    $sql_insert = "INSERT INTO redes_municipales.redes 
                  (nombre_red, tipo_red, direccion_ip, ubicacion, departamento, responsable, estado, ping, ultimo_check) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                $responsable = "Por asignar";
                $estado = "activa";
                $ping = 1;
                $ultimo_check = date('Y-m-d H:i:s');
                
                $stmt_insert->bind_param("sssssssis", $nombre_red, $tipo_red, $ip, $ubicacion, $departamento, $responsable, $estado, $ping, $ultimo_check);
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
  <meta name="description" content="ART - <?php echo $organismo; ?>">
  <meta name="author" content="Informática MGB">
    <title>ART - <?php echo $organismo; ?></title>
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
        
        .auto-refresh-badge {
            animation: blink 2s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        .table-status {
            text-align: center;
            min-width: 120px;
        }

        /* Agregar al final del estilo existente */
        .modal.show {
            display: block !important;
        }

        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            100% { background-position: 0 0; }
        }

        /* Estilos para el botón de control remoto */
        .btn-control-remoto {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-control-remoto:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .protocol-option {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .protocol-option:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .protocol-option.active {
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
        }
        
        .connection-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
        }
        
        .copy-btn {
            cursor: pointer;
        }
        
        .copy-btn:hover {
            background-color: #e9ecef;
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
						<div class="col-xl-3 col-lg-6 col-12">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">TOTAL</h5>
											<span class="h3 font-weight-bold mb-0">
                        <?php echo $stats['total_redes']; ?> 
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-lg-6 col-12 col-sm-12">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">TERMINALES</h5>
											<span class="h3 font-weight-bold mb-0">
                        <?php echo $stats['redes_Terminales']; ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-lg-6 col-12">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">IMPRESORAS</h5>
											<span class="h3 font-weight-bold mb-0">
                        <?php echo $stats['redes_Impresoras']; ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-lg-6 col-12 col-sm-12">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-muted mb-0">ROUTERS</h5>
											<span class="h3 font-weight-bold mb-0">
                        <?php echo $stats['redes_Router']; ?>
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
                      <strong>Inscripción guardado con Exito!</strong>
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
          <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="auto-refresh-badge badge badge-success mr-3">Auto-Refresh: 300s</span>
                        <div class="btn-group mr-3">
                            <button id="btnVerificarTodas" class="btn btn-primary btn-sm">
                                <span class="spinner-border spinner-border-sm d-none" id="spinnerTodas"></span>
                                🔄 Verificar Todas
                            </button>
                            <button id="btnToggleAutoRefresh" class="btn btn-outline-success btn-sm">⏸️ Pausar</button>
                            <!-- BOTÓN DE ESCANEO DE RED -->
                            <button id="btnEscanearRed" class="btn btn-info btn-sm">
                                <span class="spinner-border spinner-border-sm d-none" id="spinnerEscanear"></span>
                                🔍 Escanear Red 172.20.98.0/23
                            </button>
                        </div>
                        <div id="contador" class="text-muted">
                            Próxima verificación en: <span id="countdown">300</span>s
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
        </div>
      </div>

      <!-- Table -->
      <div class="row">  
        <div class="col-xl-12 order-xl-1 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center"> 
                <div class="col-lg-9 col-md-7 col-sm-6">			  
                  <h3 class="mb-0">Redes</h3>
                </div>
                <div class="col text-right">
                  <div class="search-container">
                    <div class="search-box">
                      <i class="fas fa-search"></i>
                      <input type="text" id="search-input" placeholder="Buscar redes..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <select class="filter-select" id="filter-type">
                      <option value="">Todos los tipos</option>
                      <option value="Terminales" <?php echo $filter_type == 'Terminales' ? 'selected' : ''; ?>>Terminales</option>
                      <option value="Impresoras" <?php echo $filter_type == 'Impresoras' ? 'selected' : ''; ?>>Impresoras</option>
                      <option value="Router" <?php echo $filter_type == 'Router' ? 'selected' : ''; ?>>Router</option>
                      <option value="Servidor" <?php echo $filter_type == 'Servidor' ? 'selected' : ''; ?>>Servidor</option>
                      <option value="Relojes" <?php echo $filter_type == 'Relojes' ? 'selected' : ''; ?>>Relojes</option>
                    </select>
                    <a href="agregar.php" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Red</a>
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
                    <th>Responsable</th>
                    <th>Última Verificación</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tabla-redes">
                  <?php if ($resultado->num_rows > 0): ?>
                  <?php while($fila = $resultado->fetch_assoc()): 
                    $estado = $fila['ping'] ?? 0;
                    $claseEstado = $estado ? 'online' : 'offline';
                    $textoEstado = $estado ? 'En línea' : 'Sin respuesta';
                    $ultimoCheck = $fila['ultimo_check'] ? date('H:i:s', strtotime($fila['ultimo_check'])) : 'No verificado';
                  ?>
                  <tr id="fila-<?php echo $fila['id']; ?>" data-ip-id="<?php echo $fila['id']; ?>">
                    <td class="table-status">
                      <div class="status-cell">
                        <span class="status-indicator <?php echo $claseEstado; ?>" id="status-<?php echo $fila['id']; ?>"></span>
                        <small class="text-<?php echo $estado ? 'success' : 'danger'; ?>" id="text-<?php echo $fila['id']; ?>">
                          <?php echo $textoEstado; ?>
                        </small>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($fila['nombre_red']); ?></td>
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
                    <td><?php echo htmlspecialchars($fila['responsable']); ?></td>
                    <td id="time-<?php echo $fila['id']; ?>"><?php echo $ultimoCheck; ?></td>
                    <td class="actions">
                      <a href="detalles.php?id=<?php echo $fila['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                      <a href="editar.php?id=<?php echo $fila['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                      <a href="confirmar_eliminacion.php?id=<?php echo $fila['id']; ?>" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                      </a>
                      <button class="btn btn-info btn-sm btn-verificar-individual" data-ip-id="<?php echo $fila['id']; ?>" title="Verificar ahora">
                        <i class="fas fa-sync-alt"></i>
                      </button>
                      <!-- NUEVO BOTÓN DE CONTROL REMOTO -->
                      <button class="btn btn-success btn-sm btn-control-remoto" 
                              data-ip="<?php echo htmlspecialchars($fila['direccion_ip']); ?>"
                              data-nombre="<?php echo htmlspecialchars($fila['nombre_red']); ?>"
                              title="Control remoto">
                        <i class="fas fa-desktop"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                  <?php else: ?>
                  <tr>
                    <td colspan="9" style="text-align: center;">No se encontraron redes registradas.</td>
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
                  <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                  </a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>">
                    <?php echo $i; ?>
                  </a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $total_paginas): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>">
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
<br><br>
      <?php include "../footer.php"; ?> 
    </div>
  </div>

  <!-- Argon Scripts -->
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/argon.js?v=1.0.0"></script>  
  
  <!-- Modal para Control Remoto -->
  <div class="modal fade" id="modalControlRemoto" tabindex="-1" role="dialog" aria-labelledby="modalControlRemotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalControlRemotoLabel">Control Remoto - <span id="modalEquipoNombre"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-4">
                <div class="list-group" id="protocolosLista">
                  <a class="list-group-item list-group-item-action protocol-option active" data-protocolo="rdp">
                    <i class="fas fa-windows mr-2"></i>RDP
                  </a>
                  <a class="list-group-item list-group-item-action protocol-option" data-protocolo="ssh">
                    <i class="fas fa-terminal mr-2"></i>SSH
                  </a>
                  <a class="list-group-item list-group-item-action protocol-option" data-protocolo="vnc">
                    <i class="fas fa-desktop mr-2"></i>VNC
                  </a>
                  <a class="list-group-item list-group-item-action protocol-option" data-protocolo="teamviewer">
                    <i class="fas fa-network-wired mr-2"></i>TeamViewer
                  </a>
                  <a class="list-group-item list-group-item-action protocol-option" data-protocolo="anydesk">
                    <i class="fas fa-desktop mr-2"></i>AnyDesk
                  </a>
                  <a class="list-group-item list-group-item-action protocol-option" data-protocolo="web">
                    <i class="fas fa-globe mr-2"></i>Web
                  </a>
                </div>
              </div>
              <div class="col-md-8">
                <div id="contenidoProtocolo">
                  <!-- Contenido dinámico según el protocolo seleccionado -->
                  <div class="connection-info">
                    <h6>Información de conexión</h6>
                    <p><strong>IP:</strong> <span id="infoIp"></span></p>
                    <p><strong>Protocolo:</strong> <span id="infoProtocolo">RDP</span></p>
                    <p><strong>Puerto predeterminado:</strong> <span id="infoPuerto">3389</span></p>
                    
                    <div class="form-group mt-3">
                      <label for="puertoPersonalizado">Puerto personalizado (opcional):</label>
                      <input type="number" class="form-control" id="puertoPersonalizado" placeholder="Dejar vacío para usar el predeterminado">
                    </div>
                    
                    <div class="form-group">
                      <label for="comandoConexion">Comando/URL de conexión:</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="comandoConexion" readonly>
                        <div class="input-group-append">
                          <button class="btn btn-outline-secondary copy-btn" type="button" id="btnCopiarComando" title="Copiar al portapapeles">
                            <i class="fas fa-copy"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <div class="mt-3">
                      <button class="btn btn-primary" id="btnAbrirConexion">
                        <i class="fas fa-external-link-alt mr-1"></i> Abrir conexión
                      </button>
                      <button class="btn btn-outline-secondary" id="btnGenerarComando">
                        <i class="fas fa-sync-alt mr-1"></i> Regenerar comando
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
  class IPMonitor {
      constructor() {
          this.autoRefreshInterval = 300000; // 5 minutos
          this.countdownInterval = null;
          this.autoRefreshEnabled = true;
          this.countdownTime = 300;
          this.isChecking = false;
          
          this.init();
      }
      
      init() {
          this.startAutoRefresh();
          this.bindEvents();
      }
      
      bindEvents() {
          // Verificar todas las IPs
          $('#btnVerificarTodas').click(() => this.verificarTodas());
          
          // Verificar IP individual
          $(document).on('click', '.btn-verificar-individual', (e) => {
              const ipId = $(e.currentTarget).data('ip-id');
              this.verificarIndividual(ipId);
          });
          
          // Toggle auto-refresh
          $('#btnToggleAutoRefresh').click(() => this.toggleAutoRefresh());
          
          // Escanear red
          $('#btnEscanearRed').click(() => this.escanearRed());
          
          // Control remoto
          $(document).on('click', '.btn-control-remoto', (e) => {
              const ip = $(e.currentTarget).data('ip');
              const nombre = $(e.currentTarget).data('nombre');
              this.mostrarControlRemoto(ip, nombre);
          });
      }

      mostrarControlRemoto(ip, nombre) {
          $('#modalEquipoNombre').text(nombre);
          $('#infoIp').text(ip);
          $('#puertoPersonalizado').val('');
          
          // Seleccionar RDP por defecto
          $('.protocol-option').removeClass('active');
          $('.protocol-option[data-protocolo="rdp"]').addClass('active');
          this.actualizarInfoProtocolo('rdp', ip);
          
          $('#modalControlRemoto').modal('show');
      }

      actualizarInfoProtocolo(protocolo, ip) {
          const protocolos = {
              rdp: { nombre: 'RDP (Escritorio Remoto)', puerto: 3389, comando: `mstsc /v:${ip}` },
              ssh: { nombre: 'SSH', puerto: 22, comando: `ssh ${ip}` },
              vnc: { nombre: 'VNC', puerto: 5900, comando: `vncviewer ${ip}` },
              teamviewer: { nombre: 'TeamViewer', puerto: null, comando: `teamviewer --id ${ip}` },
              anydesk: { nombre: 'AnyDesk', puerto: null, comando: `anydesk ${ip}` },
              web: { nombre: 'Web (HTTP/HTTPS)', puerto: 80, comando: `http://${ip}` }
          };
          
          const info = protocolos[protocolo] || protocolos.rdp;
          $('#infoProtocolo').text(info.nombre);
          $('#infoPuerto').text(info.puerto || 'N/A');
          
          this.generarComandoConexion(ip, protocolo, info.puerto);
      }

      generarComandoConexion(ip, protocolo, puertoPredeterminado) {
          let puerto = $('#puertoPersonalizado').val() || puertoPredeterminado;
          let comando = '';
          
          switch(protocolo) {
              case 'rdp':
                  comando = `mstsc /v:${ip}` + (puerto && puerto != 3389 ? `:${puerto}` : '');
                  break;
              case 'ssh':
                  comando = `ssh ${ip}` + (puerto && puerto != 22 ? ` -p ${puerto}` : '');
                  break;
              case 'vnc':
                  comando = `vncviewer ${ip}` + (puerto && puerto != 5900 ? `:${puerto}` : '');
                  break;
              case 'teamviewer':
                  comando = `teamviewer --id ${ip}`;
                  break;
              case 'anydesk':
                  comando = `anydesk ${ip}`;
                  break;
              case 'web':
                  const protocoloWeb = puerto == 443 ? 'https' : 'http';
                  comando = `${protocoloWeb}://${ip}` + (puerto && puerto != 80 && puerto != 443 ? `:${puerto}` : '');
                  break;
          }
          
          $('#comandoConexion').val(comando);
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
    
    // Mostrar modal de progreso
    this.mostrarModalProgreso();
    
    $.getJSON('?action=escanear_red', (data) => {
        if (data.success) {
            const resultado = data.resultado;
            const mensaje = `✅ Escaneo completado:\n\n` +
                           `• IPs escaneadas: ${resultado.total_escaneadas}\n` +
                           `• IPs activas encontradas: ${resultado.ips_encontradas}\n` +
                           `• Nuevas IPs agregadas: ${resultado.nuevas_ips_agregadas}\n\n` +
                           `La base de datos ha sido actualizada.`;
            
            // Ocultar modal de progreso
            this.ocultarModalProgreso();
            
            // Mostrar resultados
            this.mostrarResultadosEscaneo(mensaje);
            
            // Recargar la página para mostrar los nuevos resultados
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

      startAutoRefresh() {
          this.startCountdown();
          
          setInterval(() => {
              if (this.autoRefreshEnabled && !this.isChecking) {
                  this.verificarTodas();
              }
          }, this.autoRefreshInterval);
      }
      
      startCountdown() {
          this.countdownTime = 300;
          $('#countdown').text(this.countdownTime);
          
          this.countdownInterval = setInterval(() => {
              this.countdownTime--;
              $('#countdown').text(this.countdownTime);
              
              if (this.countdownTime <= 0) {
                  this.countdownTime = 300;
              }
          }, 1000);
      }
      
      toggleAutoRefresh() {
          this.autoRefreshEnabled = !this.autoRefreshEnabled;
          
          const btn = $('#btnToggleAutoRefresh');
          if (this.autoRefreshEnabled) {
              btn.removeClass('btn-outline-danger').addClass('btn-outline-success').html('⏸️ Pausar');
              $('.auto-refresh-badge').removeClass('badge-warning').addClass('badge-success');
          } else {
              btn.removeClass('btn-outline-success').addClass('btn-outline-danger').html('▶️ Reanudar');
              $('.auto-refresh-badge').removeClass('badge-success').addClass('badge-warning');
          }
      }
      
      verificarTodas() {
          if (this.isChecking) return;
          
          this.isChecking = true;
          $('#loading').removeClass('d-none');
          $('#spinnerTodas').removeClass('d-none');
          $('#btnVerificarTodas').prop('disabled', true);
          
          // Mostrar estado "verificando" en todas las filas
          $('.status-indicator').removeClass('online offline').addClass('checking');
          
          $.getJSON('?action=verificar_todas', (data) => {
              if (data.success) {
                  this.actualizarEstados(data.estados);
              }
          }).always(() => {
              this.isChecking = false;
              $('#loading').addClass('d-none');
              $('#spinnerTodas').addClass('d-none');
              $('#btnVerificarTodas').prop('disabled', false);
              this.countdownTime = 300;
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
              this.actualizarEstadoIndividual(estado.id, estado.estado, estado.timestamp);
          });
      }
      
      actualizarEstadoIndividual(ipId, estado, timestamp) {
          const statusIndicator = $(`#status-${ipId}`);
          const statusText = $(`#text-${ipId}`);
          const timeElement = $(`#time-${ipId}`);
          
          statusIndicator.removeClass('checking online offline');
          
          if (estado) {
              statusIndicator.addClass('online');
              statusText.html('En línea').removeClass('text-danger').addClass('text-success');
          } else {
              statusIndicator.addClass('offline');
              statusText.html('Sin respuesta').removeClass('text-success').addClass('text-danger');
          }
          
          if (timestamp) {
              const time = new Date(timestamp).toLocaleTimeString();
              timeElement.text(time);
          }
      }
  }
  
  // Inicializar el monitor cuando el documento esté listo
  $(document).ready(() => {
      const ipMonitor = new IPMonitor();
      
      // Funcionalidad de búsqueda
      $('#search-input').keyup(function(e) {
          if (e.key === 'Enter') {
              const searchValue = this.value;
              const filterValue = $('#filter-type').val();
              
              let url = 'index.php?';
              if (searchValue) url += 'search=' + encodeURIComponent(searchValue);
              if (filterValue) url += (searchValue ? '&' : '') + 'filter_type=' + encodeURIComponent(filterValue);
              
              window.location.href = url;
          }
      });

      $('#filter-type').change(function() {
          const searchValue = $('#search-input').val();
          const filterValue = this.value;
          
          let url = 'index.php?';
          if (searchValue) url += 'search=' + encodeURIComponent(searchValue);
          if (filterValue) url += (searchValue ? '&' : '') + 'filter_type=' + encodeURIComponent(filterValue);
          
          window.location.href = url;
      });

      // Auto-ocultar alertas
      window.setTimeout(function() {
          $(".alert").fadeTo(1000, 0).slideUp(1000, function(){
              $(this).remove(); 
          });
      }, 2500);
      
      // Control remoto - eventos del modal
      $(document).on('click', '.protocol-option', function() {
          $('.protocol-option').removeClass('active');
          $(this).addClass('active');
          
          const protocolo = $(this).data('protocolo');
          const ip = $('#infoIp').text();
          ipMonitor.actualizarInfoProtocolo(protocolo, ip);
      });
      
      $('#puertoPersonalizado').on('input', function() {
          const protocolo = $('.protocol-option.active').data('protocolo');
          const ip = $('#infoIp').text();
          ipMonitor.actualizarInfoProtocolo(protocolo, ip);
      });
      
      $('#btnGenerarComando').click(function() {
          const protocolo = $('.protocol-option.active').data('protocolo');
          const ip = $('#infoIp').text();
          ipMonitor.actualizarInfoProtocolo(protocolo, ip);
      });
      
      $('#btnCopiarComando').click(function() {
          const comando = $('#comandoConexion').val();
          navigator.clipboard.writeText(comando).then(() => {
              $(this).html('<i class="fas fa-check"></i>');
              setTimeout(() => {
                  $(this).html('<i class="fas fa-copy"></i>');
              }, 2000);
          });
      });
      
      $('#btnAbrirConexion').click(function() {
          const comando = $('#comandoConexion').val();
          const protocolo = $('.protocol-option.active').data('protocolo');
          
          if (protocolo === 'web') {
              // Para protocolos web, abrir en nueva pestaña
              window.open(comando, '_blank');
          } else {
              // Para otros protocolos, mostrar instrucciones
              alert(`Para conectarse usando ${protocolo.toUpperCase()}:\n\n1. Copie el comando: ${comando}\n2. Ejecútelo en su terminal o aplicación correspondiente.\n\nNota: La conexión directa desde el navegador no es posible para este protocolo.`);
          }
      });
  });
  
  </script>

  <!-- Modal ARTISTA-->
  <div class="modal fade" id="verartista" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content"></div>
    </div>
  </div>

</body>
</html>

<?php $condb->close(); ?>