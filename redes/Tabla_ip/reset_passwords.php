<?php
include 'conexion.php';

// Contraseñas por defecto (cambia estas por las que desees)
$passwords = [
    'admin' => 'admin123',
    'tecnico' => 'tecnico123', 
    'consulta' => 'consulta123'
];

echo "<h2>Restableciendo contraseñas...</h2>";

foreach ($passwords as $usuario => $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE usuarios SET password = ? WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $password_hash, $usuario);
    
    if ($stmt->execute()) {
        echo "<p>Contraseña para <strong>$usuario</strong> restablecida a: <strong>$password</strong></p>";
    } else {
        echo "<p>Error al restablecer contraseña para $usuario: " . $conexion->error . "</p>";
    }
    
    $stmt->close();
}

echo "<h3>Ahora puedes iniciar sesión con:</h3>";
echo "<ul>";
foreach ($passwords as $usuario => $password) {
    echo "<li><strong>Usuario:</strong> $usuario - <strong>Contraseña:</strong> $password</li>";
}
echo "</ul>";

echo '<p><a href="login.php">Ir al login</a></p>';

$conexion->close();
?>