<?php
// actualizar_passwords.php
include 'conexion.php';

echo "<h2>Actualizando contraseñas a formato seguro...</h2>";

// Obtener todos los usuarios
$result = $conexion->query("SELECT id, usuario, password FROM usuario");

if ($result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
        // Si la contraseña no está hasheada, hashearla
        if (!password_verify($user['password'], $user['password']) && 
            !password_verify('test', $user['password'])) { // Verificación adicional
            
            $nuevo_hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $update_sql = "UPDATE usuario SET password = ? WHERE id = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param("si", $nuevo_hash, $user['id']);
            
            if ($update_stmt->execute()) {
                echo "<p>✅ Contraseña de <strong>{$user['usuario']}</strong> actualizada correctamente</p>";
            } else {
                echo "<p>❌ Error al actualizar contraseña de {$user['usuario']}: " . $conexion->error . "</p>";
            }
            
            $update_stmt->close();
        } else {
            echo "<p>➡️ Contraseña de <strong>{$user['usuario']}</strong> ya está en formato seguro</p>";
        }
    }
} else {
    echo "<p>No hay usuarios en la tabla.</p>";
}

echo '<p><a href="login.php">Ir al login</a></p>';

$conexion->close();
?>