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

// Inicializar variables
$error = '';
$success = '';
$red = null;

// Obtener el ID de la red a editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No se proporcionó un ID válido");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos actuales de la red
$sql = "SELECT * FROM redes_municipales.redes WHERE id = ?";
$stmt = $condb->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php?error=La red con ID $id no existe");
    exit();
}

$red = $resultado->fetch_assoc();
$stmt->close();

// Definir opciones de ubicación ordenadas alfabéticamente
$ubicaciones = [
    'Corralon',
    'Cultura', 
    'Deportes',
    'Gorchs',
    'Hospital',
    'Palacio Municipal',
    'Produccion',
    'Rentas',
    'Seguridad',
    'Turismo'
];
sort($ubicaciones);

// Definir departamentos por ubicación (ordenados alfabéticamente)
$departamentos_por_ubicacion = [
    'Corralon' => [
        'Catastro',
        'Deposito',
        'Obras Públicas',
        'Servicio Público'
    ],
    'Cultura' => [
        'Biblioteca',
        'Cultura',
        'Museo'
    ],
    'Deportes' => [
        'Deportes'
    ],
    'Gorchs' => [
        'Gorchs'
    ],
    'Hospital' => 'libre', // Campo de texto libre
    'Palacio Municipal' => [
        'Acción Social',
        'Archivo',
        'Asesoria Legal',
        'Compras',
        'Contaduria',
        'HCD',
        'Informatica',
        'Juzgado de Faltas',
        'Personal',
        'Prensa',
        'Secretaria',
        'Servicio Local',
        'Tesoreria'
    ],
    'Produccion' => [
        'Casa de Campo',
        'Generó y Diversidad',
        'Oficina de Empleo',
        'Planta de Reciclaje'
    ],
    'Rentas' => [
        'Atencion al Publico',
        'Cementerio',
        'Descentralización Tributaria',
        'Destajistas',
        'Dirección',
        'Legales',
        'Procesamientos de Datos'
    ],
    'Seguridad' => [
        'Secretaria de Seguridad',
        'Transito'
    ],
    'Turismo' => [
        'Turismo'
    ]
];

// Ordenar todos los arrays de departamentos alfabéticamente
foreach ($departamentos_por_ubicacion as $ubicacion => $departamentos) {
    if ($departamentos !== 'libre' && is_array($departamentos)) {
        sort($departamentos_por_ubicacion[$ubicacion]);
    }
}

// Determinar si el departamento actual es libre o de lista
$departamento_actual = $red['departamento'];
$ubicacion_actual = $red['ubicacion'];
$es_departamento_libre = ($ubicacion_actual == 'Hospital');

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_red = $_POST['nombre_red'];
    $tipo_red = $_POST['tipo_red'];
    $direccion_ip = $_POST['direccion_ip'];
    $ubicacion = $_POST['ubicacion'];
    $departamento = $_POST['departamento'];
    $responsable = $_POST['responsable'];
    $telefono_contacto = $_POST['telefono_contacto'];
    $fecha_instalacion = $_POST['fecha_instalacion'];
    $observaciones = $_POST['observaciones'];
    
    // Validar IP duplicada si se proporcionó una IP diferente a la actual
    if (!empty($direccion_ip) && $direccion_ip != $red['direccion_ip']) {
        $sql_check = "SELECT id, nombre_red FROM redes_municipales.redes WHERE direccion_ip = ? AND estado = 'activa' AND id != ?";
        $stmt_check = $condb->prepare($sql_check);
        $stmt_check->bind_param("si", $direccion_ip, $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $red_existente = $result_check->fetch_assoc();
            $error = "La dirección IP <strong>$direccion_ip</strong> ya está asignada a la red: <strong>" . htmlspecialchars($red_existente['nombre_red']) . "</strong>";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            // Actualizar red
            $sql_update = "UPDATE redes_municipales.redes SET nombre_red=?, tipo_red=?, direccion_ip=?, ubicacion=?, departamento=?, responsable=?, telefono_contacto=?, fecha_instalacion=?, observaciones=? WHERE id=?";
            
            $stmt_update = $condb->prepare($sql_update);
            $stmt_update->bind_param("sssssssssi", $nombre_red, $tipo_red, $direccion_ip, $ubicacion, $departamento, $responsable, $telefono_contacto, $fecha_instalacion, $observaciones, $id);

            if ($stmt_update->execute()) {
                // REDIRIGIR AL INDEX CON MENSAJE DE ÉXITO
                header("Location: index.php?ok=1");
                exit();
            } else {
                $error = "Error al actualizar: " . $stmt_update->error;
            }
            
            $stmt_update->close();
        }
    } else {
        // Actualizar red sin cambiar IP o sin IP
        $sql_update = "UPDATE redes_municipales.redes SET nombre_red=?, tipo_red=?, direccion_ip=?, ubicacion=?, departamento=?, responsable=?, telefono_contacto=?, fecha_instalacion=?, observaciones=? WHERE id=?";
        
        $stmt_update = $condb->prepare($sql_update);
        $stmt_update->bind_param("sssssssssi", $nombre_red, $tipo_red, $direccion_ip, $ubicacion, $departamento, $responsable, $telefono_contacto, $fecha_instalacion, $observaciones, $id);
        
        if ($stmt_update->execute()) {
            // REDIRIGIR AL INDEX CON MENSAJE DE ÉXITO
            header("Location: index.php?ok=1");
            exit();
        } else {
            $error = "Error al actualizar: " . $stmt_update->error;
        }
        
        $stmt_update->close();
    }
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
        .alert strong {
            font-weight: 600;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .departamento-libre {
            display: none;
        }
    </style>
</head>

<body style="margin:0;">
  <!-- Sidenav -->
  <?php include('menulateral.php'); ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
      <div class="container-fluid">
        <!-- Brand -->
        <a class="h2 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="index.php">EDITAR RED</a>	
        <!-- User -->
        <?php include('topnav.php');?>
      </div>
    </nav>

    <!-- Header -->
    <div class="header pb-8 pt-4 pt-lg-6 d-flex align-items-center">
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="col-lg-7 col-md-10">
          <h1 class="display-4 text-white">Editar Red</h1>
          <p class="text-white mt-0 mb-3">Modifique los datos de la red seleccionada</p>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col-xl-12 order-xl-1">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Editando: <?php echo htmlspecialchars($red['nombre_red']); ?></h3>
                </div>
              </div>
            </div>
            <div class="card-body">
              <?php if (!empty($success)): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <span class="alert-text"><strong>Éxito!</strong> <?php echo $success; ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <?php endif; ?>

              <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span class="alert-text"><strong>Error!</strong> <?php echo $error; ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <?php endif; ?>

              <form method="POST">
                <div class="pl-lg-4">
                  <h6 class="heading-small text-muted mb-4">Información Básica</h6>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label required" for="nombre_red">Nombre de Usuario</label>
                        <input type="text" id="nombre_red" name="nombre_red" class="form-control" 
                            value="<?php echo htmlspecialchars($red['nombre_red']); ?>" required>
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="form-group">
                        <label class="form-control-label required" for="tipo_red">Tipo de Red</label>
                        <select id="tipo_red" name="tipo_red" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="Terminales" <?php echo $red['tipo_red'] == 'Terminales' ? 'selected' : ''; ?>>Terminales</option>
                            <option value="Impresoras" <?php echo $red['tipo_red'] == 'Impresoras' ? 'selected' : ''; ?>>Impresoras</option>
                            <option value="Router" <?php echo $red['tipo_red'] == 'Router' ? 'selected' : ''; ?>>Router</option>
                            <option value="Servidor" <?php echo $red['tipo_red'] == 'Servidor' ? 'selected' : ''; ?>>Servidor</option>
                            <option value="Relojes" <?php echo $red['tipo_red'] == 'Relojes' ? 'selected' : ''; ?>>Relojes</option>
                            <option value="Otro" <?php echo $red['tipo_red'] == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-3">
                      <div class="form-group">
                        <label class="form-control-label" for="direccion_ip">Dirección IP</label>
                        <input type="text" id="direccion_ip" name="direccion_ip" class="form-control" 
                            value="<?php echo htmlspecialchars($red['direccion_ip']); ?>" 
                            placeholder="Ej: 192.168.1.1">
                        <small class="form-text text-muted">Se validará que la IP no esté duplicada</small>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label class="form-control-label" for="fecha_instalacion">Fecha Instalación</label>
                        <input type="date" id="fecha_instalacion" name="fecha_instalacion" class="form-control" 
                            value="<?php 
                                // Si la fecha está vacía, usar fecha de hoy, sino usar el valor existente
                                if(empty($red['fecha_instalacion'])) {
                                    echo htmlspecialchars(date('Y-m-d'));
                                } else {
                                    echo htmlspecialchars($red['fecha_instalacion']);
                                }
                            ?>">
                      </div>
                    </div>
                  </div>
                  
                  <h6 class="heading-small text-muted mb-4 mt-4">Información de Ubicación</h6>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="ubicacion">Ubicación</label>
                        <select id="ubicacion" name="ubicacion" class="form-control">
                            <option value="">Seleccione una ubicación...</option>
                            <?php foreach ($ubicaciones as $ubicacion_option): ?>
                                <option value="<?php echo htmlspecialchars($ubicacion_option); ?>" 
                                    <?php echo $ubicacion_actual == $ubicacion_option ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ubicacion_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="departamento">Departamento</label>
                        <!-- Select para departamentos predefinidos -->
                        <select id="departamento_select" name="departamento" class="form-control <?php echo $es_departamento_libre ? 'd-none' : ''; ?>">
                            <option value="">Seleccione un departamento...</option>
                            <?php 
                            if ($ubicacion_actual && $departamentos_por_ubicacion[$ubicacion_actual] !== 'libre') {
                                foreach ($departamentos_por_ubicacion[$ubicacion_actual] as $depto) {
                                    $selected = ($departamento_actual == $depto) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($depto) . "\" $selected>" . htmlspecialchars($depto) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <!-- Input de texto para Hospital -->
                        <input type="text" id="departamento_input" name="departamento" class="form-control <?php echo !$es_departamento_libre ? 'departamento-libre' : ''; ?>" 
                               placeholder="Ingrese el departamento" 
                               value="<?php echo $es_departamento_libre ? htmlspecialchars($departamento_actual) : ''; ?>">
                      </div>
                    </div>
                  </div>
                  
                  <h6 class="heading-small text-muted mb-4 mt-4">Información de Contacto</h6>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefono_contacto">Nombre de Equipo</label>
                        <input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control" 
                            value="<?php echo htmlspecialchars($red['telefono_contacto']); ?>" 
                            placeholder="Desktop-012345">
                      </div>
                    </div>
                  </div>
                  
                  <h6 class="heading-small text-muted mb-4 mt-4">Observaciones</h6>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-control-label" for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" 
                            rows="4" placeholder="Notas adicionales sobre esta red"><?php echo htmlspecialchars($red['observaciones']); ?></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <br><br><br>
      <?php include "../footer.php"; ?>
    </div>
  </div>

  <!-- Argon Scripts -->
  <script src="../assets/vendor/jquery/dist/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/argon.js?v=1.0.0"></script>

  <script>
  // Datos de departamentos por ubicación
  const departamentosPorUbicacion = {
      <?php foreach ($departamentos_por_ubicacion as $ubicacion => $departamentos): ?>
      '<?php echo $ubicacion; ?>': <?php 
          if ($departamentos === 'libre') {
              echo "'libre'";
          } else {
              echo json_encode($departamentos);
          }
      ?>,
      <?php endforeach; ?>
  };

  $(document).ready(function() {
      // Manejar cambio en la ubicación
      $('#ubicacion').change(function() {
          const ubicacionSeleccionada = $(this).val();
          const departamentoSelect = $('#departamento_select');
          const departamentoInput = $('#departamento_input');
          
          // Limpiar ambos campos
          departamentoSelect.val('');
          departamentoInput.val('');
          
          if (ubicacionSeleccionada === '') {
              // Si no hay ubicación seleccionada, ocultar ambos
              departamentoSelect.hide();
              departamentoInput.hide();
              departamentoSelect.prop('disabled', true);
              departamentoInput.prop('disabled', true);
          } else if (departamentosPorUbicacion[ubicacionSeleccionada] === 'libre') {
              // Para Hospital, mostrar input y ocultar select
              departamentoSelect.hide();
              departamentoSelect.prop('disabled', true);
              departamentoInput.show();
              departamentoInput.prop('disabled', false);
              departamentoInput.attr('placeholder', 'Ingrese el departamento del hospital');
          } else {
              // Para otras ubicaciones, mostrar select y ocultar input
              departamentoSelect.show();
              departamentoSelect.prop('disabled', false);
              departamentoInput.hide();
              departamentoInput.prop('disabled', true);
              
              // Llenar el select con los departamentos correspondientes
              departamentoSelect.empty();
              departamentoSelect.append('<option value="">Seleccione un departamento...</option>');
              
              departamentosPorUbicacion[ubicacionSeleccionada].forEach(function(depto) {
                  departamentoSelect.append('<option value="' + depto + '">' + depto + '</option>');
              });
          }
      });

      // Inicializar el estado al cargar la página
      $('#ubicacion').trigger('change');

      // Manejar envío del formulario para asegurar que solo un campo se envíe
      $('form').submit(function() {
          const ubicacion = $('#ubicacion').val();
          if (ubicacion && departamentosPorUbicacion[ubicacion] === 'libre') {
              // Para Hospital, deshabilitar el select para que no se envíe
              $('#departamento_select').prop('disabled', true);
          } else {
              // Para otras ubicaciones, deshabilitar el input para que no se envíe
              $('#departamento_input').prop('disabled', true);
          }
      });
  });
  </script>
</body>
</html>
<?php $condb->close(); ?>