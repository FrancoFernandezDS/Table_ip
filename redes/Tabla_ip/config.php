<?php
// Configuración de la sesión
session_start();

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión de Redes');
define('APP_VERSION', '1.0');
define('DEFAULT_ROLE', 'consulta');

// Incluir conexión a la base de datos
include 'conexion.php';

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar permisos
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (!is_array($roles)) $roles = [$roles];
    return in_array($_SESSION['user_role'], $roles);
}

// Función para redirigir si no está autenticado
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Función para redirigir si no tiene el rol adecuado
function requireRole($roles) {
    requireAuth();
    if (!hasRole($roles)) {
        header("Location: acceso_denegado.php");
        exit();
    }
}

// Función para obtener el nombre del rol en español
function getRoleName($role) {
    $roles = [
        'administrador' => 'Administrador',
        'tecnico' => 'Técnico',
        'consulta' => 'Consulta'
    ];
    return isset($roles[$role]) ? $roles[$role] : 'Desconocido';
}
?>