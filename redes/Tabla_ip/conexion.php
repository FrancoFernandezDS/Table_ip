<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "redes_municipales";

$conexion = new mysqli($servidor, $usuario, $password, $basedatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si existe la columna 'estado' para eliminación lógica
$existe_columna_estado = false;
$resultado = $conexion->query("SHOW COLUMNS FROM redes LIKE 'estado'");
if ($resultado->num_rows > 0) {
    $existe_columna_estado = true;
}

// Verificar si existe la tabla de usuarios (ahora se llama 'usuario')
$existe_tabla_usuarios = false;
$resultado = $conexion->query("SHOW TABLES LIKE 'usuario'");
if ($resultado->num_rows > 0) {
    $existe_tabla_usuarios = true;
}
?>