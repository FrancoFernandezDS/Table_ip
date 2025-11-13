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

$id = $_POST['carpeta_id'] ?? '';
$nombre_carpeta = trim($_POST['nombre_carpeta'] ?? '');
$usuario_carpeta = trim($_POST['usuario_carpeta'] ?? '');
$password_carpeta = trim($_POST['password_carpeta'] ?? '');
$ruta_acceso = trim($_POST['ruta_acceso'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$fecha_actualizacion = $_POST['fecha_actualizacion'] ?? date('Y-m-d');

// Validaciones básicas
if(empty($nombre_carpeta) || empty($usuario_carpeta) || empty($password_carpeta)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
    exit;
}

try {
    if(empty($id)) {
        // Insertar nuevo
        $sql = "INSERT INTO redes_municipales.credenciales_carpetas (nombre_carpeta, usuario_carpeta, password_carpeta, ruta_acceso, observaciones, fecha_actualizacion, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'activa')";
        $stmt = $condb->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en prepare: ' . $condb->error);
        }
        $stmt->bind_param("ssssss", $nombre_carpeta, $usuario_carpeta, $password_carpeta, $ruta_acceso, $observaciones, $fecha_actualizacion);
    } else {
        // Actualizar existente
        $sql = "UPDATE redes_municipales.credenciales_carpetas SET nombre_carpeta=?, usuario_carpeta=?, password_carpeta=?, ruta_acceso=?, observaciones=?, fecha_actualizacion=? 
                WHERE id=?";
        $stmt = $condb->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en prepare: ' . $condb->error);
        }
        $stmt->bind_param("ssssssi", $nombre_carpeta, $usuario_carpeta, $password_carpeta, $ruta_acceso, $observaciones, $fecha_actualizacion, $id);
    }

    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Credencial guardada correctamente']);
    } else {
        throw new Exception('Error en la ejecución: ' . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
?>