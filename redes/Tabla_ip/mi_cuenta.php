<?php
include 'config.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Sistema de Gestión de Redes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #237dc7ff 0%, #eaeceeff 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            color: #e2eaf3ff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
            background: linear-gradient(135deg, #0e5ec7ff 0%, #157da7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo i {
            font-size: 1.8rem;
            background: linear-gradient(135deg, #092abbff 0%, #0f8ea5ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #cbd1d6ff;
        }

        .user-welcome {
            font-size: 0.9rem;
        }

        .user-role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-admin {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .role-tecnico {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .role-consulta {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #a5a9c9ff 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #148ea1 0%, #11707f 100%);
        }

        .btn-logout {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #849192 0%, #6c7879 100%);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .account-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        @media (max-width: 968px) {
            .account-container {
                grid-template-columns: 1fr;
            }
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #5c8cbbff;
        }

        .profile-username {
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 25px;
            color: #2c3e50;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f2f6;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 500;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .last-access {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card, .info-card {
            animation: fadeIn 0.6s ease-out;
        }

        .profile-card {
            animation-delay: 0.1s;
        }

        .info-card {
            animation-delay: 0.2s;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-network-wired"></i>
            <h1>Gestión de Redes</h1>
        </div>
        <div class="header-actions">
            <div class="user-info">
                <span class="user-welcome">Hola, <?php echo $_SESSION['user_name']; ?></span>
                <span class="user-role-badge <?php echo 'role-' . $_SESSION['user_role']; ?>">
                    <?php 
                    switch ($_SESSION['user_role']) {
                        case 'administrador':
                            echo 'Administrador';
                            break;
                        case 'tecnico':
                            echo 'Técnico';
                            break;
                        case 'consulta':
                            echo 'Consulta';
                            break;
                        default:
                            echo 'Usuario';
                    }
                    ?>
                </span>
            </div>
            
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Inicio
            </a>
            
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión

            </a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Mi Cuenta</h1>
        
        <div class="account-container">
            <!-- Tarjeta de perfil -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                
                <h2 class="profile-name"><?php echo $_SESSION['user_name']; ?></h2>
                <p class="profile-username">@<?php echo $_SESSION['user_username']; ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Redes activas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Reportes</div>
                    </div>
                </div>
                
             <div class="action-buttons">
                    <a href="editar_perfil.php" class="btn btn-info">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                </div>
            </div> 
            
            <!-- Tarjeta de información -->
            <div class="info-card">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i> Información de la Cuenta
                </h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nombre completo</div>
                        <div class="info-value"><?php echo $_SESSION['user_name']; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Nombre de usuario</div>
                        <div class="info-value"><?php echo $_SESSION['user_username']; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Rol en el sistema</div>
                        <div class="info-value">
                            <?php 
                            switch ($_SESSION['user_role']) {
                                case 'administrador':
                                    echo '👑 Administrador';
                                    break;
                                case 'tecnico':
                                    echo '🔧 Técnico';
                                    break;
                                case 'consulta':
                                    echo '👁️ Consulta';
                                    break;
                                default:
                                    echo 'Usuario';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Estado de la cuenta</div>
                        <div class="info-value">Activa ✅</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Último acceso</div>
                        <div class="info-value">Hoy, <?php echo date(format: 'H:i'); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Miembro desde</div>
                        <div class="info-value"><?php echo date('d/m/Y'); ?></div>
                    </div>
                </div>
                <div class="last-access">
                    <i class="fas fa-clock"></i> 
                    Sesión iniciada el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i'); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Efectos de interacción
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Actualizar hora de último acceso
            function updateTime() {
                const now = new Date();
                const timeElement = document.querySelector('.last-access');
                if (timeElement) {
                    timeElement.innerHTML = `<i class="fas fa-clock"></i> Sesión iniciada el ${now.toLocaleDateString()} a las ${now.toLocaleTimeString()}`;
                }
            }
            
            setInterval(updateTime, 60000);
        });
    </script>
</body>
</html>