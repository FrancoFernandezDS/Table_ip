<?php
include 'config.php';
requireAuth();

// Verificar que sea técnico o administrador
if ($_SESSION['user_role'] != 'tecnico' && $_SESSION['user_role'] != 'administrador') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramientas Técnicas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <!-- Mismo header que index.php -->
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Herramientas Técnicas</h2>
            </div>
            <div class="card-body">
                <h3>Opciones para técnicos</h3>
                <p>Aquí van las funciones exclusivas para técnicos.</p>
            </div>
        </div>
    </div>
</body>
</html>