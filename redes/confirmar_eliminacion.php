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
  
$id = $_GET['id'];
$red = null;

// Verificar que se proporcionó un ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No se proporcionó un ID válido");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos de la red a eliminar
$sql = "SELECT * FROM redes_municipales.redes WHERE id = ?";
$stmt = $condb->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php?error=Red no encontrada");
    exit();
}

$red = $resultado->fetch_assoc();
$stmt->close();

// Verificar si existe la columna estado
$check_column_sql = "SHOW COLUMNS FROM redes LIKE 'estado'";
$column_result = $condb->query($check_column_sql);
$existe_columna_estado = $column_result->num_rows > 0;
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
        <a class="h2 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="index.php">CONFIRMAR ELIMINACIÓN</a>	
        <!-- User -->
        <?php include('topnav.php');?>
      </div>
    </nav>

    <!-- Header -->
    <div class="header pb-6 pt-5 pt-lg-6 d-flex align-items-center">
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="col-lg-7 col-md-10">
          <h1 class="display-2 text-white">Confirmar Eliminación</h1>
          <p class="text-white mt-0 mb-5">Confirme la acción a realizar</p>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row">
        <div class="col-xl-8 offset-xl-2">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-white border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-danger">Confirmar Eliminación</h3>
                </div>
                <div class="col-4 text-right">
                  <a href="index.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="alert alert-warning">
                <div class="alert-icon">
                  <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="alert-message">
                  <strong>¿Está seguro de que desea <?php echo $existe_columna_estado ? 'desactivar' : 'eliminar'; ?> esta red?</strong>
                  <p class="mt-2 mb-0">Esta acción <?php echo $existe_columna_estado ? 'desactivará' : 'eliminará permanentemente'; ?> la red del sistema.</p>
                </div>
              </div>

              <div class="pl-lg-4">
                <h4 class="heading-small text-muted mb-4">Información de la Red</h4>
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Nombre de Red:</strong></label>
                      <div class="form-control-plaintext"><?php echo htmlspecialchars($red['nombre_red']); ?></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Tipo de Red:</strong></label>
                      <div class="form-control-plaintext">
                        <span class="badge 
                          <?php echo $red['tipo_red'] == 'Terminales' ? 'badge-info' : ''; ?>
                          <?php echo $red['tipo_red'] == 'Impresoras' ? 'badge-success' : ''; ?>
                          <?php echo $red['tipo_red'] == 'Router' ? 'badge-warning' : ''; ?>
                          <?php echo $red['tipo_red'] == 'Servidor' ? 'badge-danger' : ''; ?>
                          <?php echo $red['tipo_red'] == 'Otro' ? 'badge-secondary' : ''; ?>
                          <?php echo $red['tipo_red'] == 'Relojes' ? 'badge-purple' : ''; ?>">
                          <?php echo htmlspecialchars($red['tipo_red']); ?>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Dirección IP:</strong></label>
                      <div class="form-control-plaintext"><?php echo htmlspecialchars($red['direccion_ip']); ?></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Ubicación:</strong></label>
                      <div class="form-control-plaintext"><?php echo htmlspecialchars($red['ubicacion']); ?></div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Departamento:</strong></label>
                      <div class="form-control-plaintext"><?php echo htmlspecialchars($red['departamento']); ?></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-control-label"><strong>Responsable:</strong></label>
                      <div class="form-control-plaintext"><?php echo htmlspecialchars($red['responsable']); ?></div>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <a href="eliminar.php?id=<?php echo $red['id']; ?>" class="btn btn-danger btn-lg">
                    <i class="fas fa-trash"></i> Sí, <?php echo $existe_columna_estado ? 'Desactivar' : 'Eliminar'; ?>
                  </a>
                  <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                  </a>
                </div>
              </div>
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
</body>
</html>
<?php $condb->close(); ?>