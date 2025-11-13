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

$id = $_POST['correo_id'] ?? '';
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$email_oficial = trim($_POST['email_oficial'] ?? '');
$password_email = trim($_POST['password_email'] ?? '');
$responsable = trim($_POST['responsable'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$fecha_actualizacion = $_POST['fecha_actualizacion'] ?? date('Y-m-d');

// Validaciones básicas
if(empty($nombre_usuario) || empty($email_oficial) || empty($password_email)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
    exit;
}

// Validar formato de email
if (!filter_var($email_oficial, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
    exit;
}

try {
    if(empty($id)) {
        // Insertar nuevo
        $sql = "INSERT INTO redes_municipales.credenciales_correos (nombre_usuario, email_oficial, password_email, responsable, observaciones, fecha_actualizacion, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'activa')";
        $stmt = $condb->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en prepare: ' . $condb->error);
        }
        $stmt->bind_param("ssssss", $nombre_usuario, $email_oficial, $password_email, $responsable, $observaciones, $fecha_actualizacion);
    } else {
        // Actualizar existente
        $sql = "UPDATE redes_municipales.credenciales_correos SET nombre_usuario=?, email_oficial=?, password_email=?, responsable=?, observaciones=?, fecha_actualizacion=? 
                WHERE id=?";
        $stmt = $condb->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en prepare: ' . $condb->error);
        }
        $stmt->bind_param("ssssssi", $nombre_usuario, $email_oficial, $password_email, $responsable, $observaciones, $fecha_actualizacion, $id);
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