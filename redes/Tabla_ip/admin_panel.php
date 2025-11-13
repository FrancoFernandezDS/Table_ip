<?php
include 'config.php';
requireAuth();

// Verificar que sea administrador
if ($_SESSION['user_role'] != 'administrador') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Usar los mismos estilos que index.php */
    </style>
</head>
<body>
    <header class="header">
        <!-- Mismo header que index.php -->
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Panel de Administración</h2>
            </div>
            <div class="card-body">
                <h3>Opciones de administrador</h3>
                <p>Aquí van las funciones exclusivas para administradores.</p>
            </div>
        </div>
    </div>
</body>
</html>