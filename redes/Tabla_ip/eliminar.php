<?php
include 'conexion.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No se proporcionó un ID válido");
    exit();
}

$id = intval($_GET['id']); // Sanitizar el ID

// Verificar que el registro existe antes de intentar eliminarlo
$check_sql = "SELECT id FROM redes WHERE id = $id";
$resultado = $conexion->query($check_sql);

if ($resultado->num_rows === 0) {
    header("Location: index.php?error=La red con ID $id no existe");
    exit();
}

// Determinar el método de eliminación según la estructura de la BD
if ($existe_columna_estado) {
    // Eliminación lógica (preferida)
    $sql = "UPDATE redes SET estado = 'inactiva' WHERE id = ?";
} else {
    // Eliminación física (alternativa)
    $sql = "DELETE FROM redes WHERE id = ?";
}

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($sql);

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
    $error = "Error al preparar la consulta: " . $conexion->error;
}

// Si hay error, redirigir con mensaje de error
if (isset($error)) {
    header("Location: index.php?error=" . urlencode($error));
    exit();
}

$conexion->close();
?>