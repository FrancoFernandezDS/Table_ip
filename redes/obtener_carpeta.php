<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include('../assets/inc/condb.php');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no especificado']);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM redes_municipales.credenciales_carpetas WHERE id = ? AND estado = 'activa'";
    $stmt = $condb->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $carpeta = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $carpeta]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Carpeta no encontrada']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>