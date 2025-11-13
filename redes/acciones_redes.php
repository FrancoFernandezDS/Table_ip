<?php
session_start();
include('../assets/inc/condb.php');

if ($_POST['action'] == 'activar_ip') {
    $ip_id = intval($_POST['ip_id']);
    
    $sql = "UPDATE redes_municipales.redes SET estado = 'activa', ping = 0 WHERE id = ?";
    $stmt = $condb->prepare($sql);
    $stmt->bind_param("i", $ip_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>