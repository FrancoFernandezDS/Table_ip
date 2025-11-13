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

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No se proporcionó un ID válido");
    exit();
}

$id = intval($_GET['id']); // Sanitizar el ID

// Verificar que el registro existe antes de intentar eliminarlo
$check_sql = "SELECT id FROM redes_municipales.redes WHERE id = ?";
$stmt_check = $condb->prepare($check_sql);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$resultado = $stmt_check->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php?error=La red con ID $id no existe");
    exit();
}
$stmt_check->close();

// Verificar si existe la columna estado
$check_column_sql = "SHOW COLUMNS FROM redes LIKE 'estado'";
$column_result = $condb->query($check_column_sql);
$existe_columna_estado = $column_result->num_rows > 0;

// Determinar el método de eliminación según la estructura de la BD
if ($existe_columna_estado) {
    // Eliminación lógica (preferida)
    $sql = "UPDATE redes_municipales.redes SET estado = 'inactiva' WHERE id = ?";
} else {
    // Eliminación física (alternativa)
    $sql = "DELETE FROM redes_municipales.redes WHERE id = ?";
}

// Preparar y ejecutar la consulta
$stmt = $condb->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = "Red " . ($existe_columna_estado ? "desactivada" : "eliminada") . " correctamente";
        header("Location: index.php?mensaje=" . urlencode($mensaje));
        exit();
    } else {
        $error = "Error al ejecutar la consulta: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $error = "Error al preparar la consulta: " . $condb->error;
}

// Si hay error, redirigir con mensaje de error
if (isset($error)) {
    header("Location: index.php?error=" . urlencode($error));
    exit();
}

$condb->close();
?>