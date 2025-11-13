<?php
include 'config.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Sistema de Gestión de Redes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
    <style>
        .denied-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .denied-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .denied-icon {
            font-size: 5rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .denied-title {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .denied-message {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="denied-container">
        <div class="denied-card">
            <div class="denied-icon">
                <i class="fas fa-ban"></i>
            </div>
            
            <h2 class="denied-title">Acceso Denegado</h2>
            
            <p class="denied-message">
                No tiene permisos suficientes para acceder a esta página.
                Si cree que esto es un error, contacte al administrador del sistema.
            </p>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
                <a href="logout.php" class="btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</body>
</html>