<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include('../assets/inc/condb.php');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if(!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no especificado']);
    exit;
}

$id = intval($_POST['id']);

try {
    // Cambiar estado a 'inactiva' en lugar de eliminar físicamente
    $sql = "UPDATE redes_municipales.credenciales_correos SET estado = 'inactiva' WHERE id = ?";
    $stmt = $condb->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Correo eliminado correctamente']);
    } else {
        throw new Exception('Error en la ejecución: ' . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>