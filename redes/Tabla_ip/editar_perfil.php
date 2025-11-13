<?php
include 'config.php';
requireAuth();

// Obtener datos actuales del usuario
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM usuario WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $departamento = trim($_POST['departamento']);
    $password_actual = $_POST['password_actual'];
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validaciones básicas
    if (empty($nombre)) {
        $error = "El nombre es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error = "El formato del email no es válido.";
    } else {
        // Verificar si se quiere cambiar la contraseña
        $cambiar_password = false;
        if (!empty($nueva_password)) {
            if ($password_actual !== $usuario['password']) {
                $error = "La contraseña actual es incorrecta.";
            } elseif ($nueva_password !== $confirmar_password) {
                $error = "Las nuevas contraseñas no coinciden.";
            } else {
                $cambiar_password = true;
            }
        }
        
        if (empty($error)) {
            // Preparar la consulta de actualización
            if ($cambiar_password) {
                $sql = "UPDATE usuario SET nombre = ?, email = ?, telefono = ?, departamento = ?, password = ? WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sssssi", $nombre, $email, $telefono, $departamento, $nueva_password, $user_id);
            } else {
                $sql = "UPDATE usuario SET nombre = ?, email = ?, telefono = ?, departamento = ? WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssssi", $nombre, $email, $telefono, $departamento, $user_id);
            }
            
            if ($stmt->execute()) {
                $success = "Perfil actualizado correctamente.";
                
                // Actualizar datos en sesión
                $_SESSION['user_name'] = $nombre;
                
                // Recargar datos del usuario
                $sql = "SELECT * FROM usuario WHERE id = ?";
                $stmt2 = $conexion->prepare($sql);
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $resultado = $stmt2->get_result();
                $usuario = $resultado->fetch_assoc();
                $stmt2->close();
            } else {
                $error = "Error al actualizar el perfil: " . $conexion->error;
            }
            
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Sistema de Gestión de Redes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            color: #2c3e50;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .form-card {
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

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f2f6;
        }

        .form-section-title {
            font-size: 1.2rem;
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #95a5a6;
        }

        .input-with-icon {
            position: relative;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-network-wired"></i>
            <h1>Gestión de Redes - Editar Perfil</h1>
        </div>
        <div class="header-actions">
            <a href="mi_cuenta.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Inicio
            </a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Editar Mi Perfil</h1>
        
        <div class="form-card">
            <h2 class="card-title">
                <i class="fas fa-user-edit"></i> Información Personal
            </h2>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Información básica -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i> Información Básica
                    </h3>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" class="form-control" name="nombre" 
                            value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" 
                            value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>
                        <small style="color: #7f8c8d; font-size: 0.9rem;">El nombre de usuario no se puede cambiar</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                            value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>"
                            placeholder="ejemplo@municipalidad.gb">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono" 
                            value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                            placeholder="+54 2241 123456">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <input type="text" class="form-control" name="departamento" 
                            value="<?php echo htmlspecialchars($usuario['departamento'] ?? ''); ?>"
                            placeholder="Sistemas">
                    </div>
                </div>
                
                <!-- Cambio de contraseña -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </h3>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Complete solo si desea cambiar su contraseña. De lo contrario, deje estos campos en blanco.
                    </p>
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña actual</label>
                        <div class="input-with-icon">
                            <input type="password" class="form-control" name="password_actual"
                                placeholder="Ingrese su contraseña actual">
                            <span class="password-toggle" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nueva contraseña</label>
                        <div class="input-with-icon">
                            <input type="password" class="form-control" name="nueva_password"
                                placeholder="Ingrese nueva contraseña">
                            <span class="password-toggle" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <div class="input-with-icon">
                            <input type="password" class="form-control" name="confirmar_password"
                                placeholder="Confirme la nueva contraseña">
                            <span class="password-toggle" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    
                    <a href="mi_cuenta.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    
                    <a href="mi_cuenta.php" class="btn btn-primary">
                        <i class="fas fa-user"></i> Ver Mi Perfil
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funcionalidad para mostrar/ocultar contraseña
        function togglePassword(element) {
            const input = element.parentElement.querySelector('input');
            const icon = element.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

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
        });
    </script>
</body>
</html>
<?php $conexion->close(); ?>