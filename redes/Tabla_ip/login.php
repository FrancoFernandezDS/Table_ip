<?php
session_start();
include 'conexion.php';

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$mensaje = '';
$mostrar_modal = false;

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Si es para recuperar contraseña
    if (isset($_POST['recuperar_password'])) {
        $email_usuario = trim($_POST['email_usuario']);
        
        if (empty($email_usuario)) {
            $error = "Por favor, ingrese su usuario o email.";
            $mostrar_modal = true;
        } else {
            // Verificar si el usuario existe
            $sql = "SELECT * FROM usuario WHERE usuario = ? OR email = ?";
            $stmt = $conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ss", $email_usuario, $email_usuario);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows === 1) {
                    $user = $resultado->fetch_assoc();
                    
                    // Generar token de recuperación
                    $token = bin2hex(random_bytes(32));
                    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Guardar token en la base de datos
                    $sql_update = "UPDATE usuario SET token_recuperacion = ?, expiracion_token = ? WHERE id = ?";
                    $stmt_update = $conexion->prepare($sql_update);
                    $stmt_update->bind_param("ssi", $token, $expiracion, $user['id']);
                    
                    if ($stmt_update->execute()) {
                        $mensaje = "Se han enviado instrucciones para restablecer su contraseña.";
                    } else {
                        $error = "Error al generar el token de recuperación.";
                    }
                    
                    $stmt_update->close();
                } else {
                    $error = "No se encontró ningún usuario con ese nombre o email.";
                    $mostrar_modal = true;
                }
                
                $stmt->close();
            }
        }
    } 
    // Si es login normal
    else {
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];
        
        if (empty($usuario) || empty($password)) {
            $error = "Por favor, complete todos los campos.";
        } else {
            // Buscar el usuario en la base de datos
            $sql = "SELECT * FROM usuario WHERE usuario = ?";
            $stmt = $conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $usuario);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows === 1) {
                    $user = $resultado->fetch_assoc();
                    
                    // Verificar la contraseña (texto plano)
                    if ($password === $user['password']) {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['nombre'];
                        $_SESSION['user_username'] = $user['usuario'];
                        $_SESSION['user_role'] = $user['rol'];
                        
                        // Redirigir al dashboard
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Credenciales incorrectas.";
                    }
                } else {
                    $error = "Credenciales incorrectas.";
                }
                
                $stmt->close();
            } else {
                $error = "Error en la consulta: " . $conexion->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Gestión de Redes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .login-header {
            background: #2c3e50;
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.8;
        }
        
        .login-body {
            padding: 25px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-logo i {
            font-size: 3rem;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }
        
        .input-with-icon input {
            padding-left: 45px;
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .input-with-icon input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-link {
            background: none;
            color: #3498db;
            padding: 5px;
            font-size: 0.9rem;
        }
        
        .btn-link:hover {
            text-decoration: underline;
            background: none;
        }
        
        .btn-login {
            width: 100%;
            justify-content: center;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .login-links {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #95a5a6;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.4rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #95a5a6;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Municipalidad de General Belgrano</h1>
                <p>Sistema de Gestión de Redes</p>
            </div>
            
            <div class="login-body">
                <div class="login-logo">
                    <i class="fas fa-network-wired"></i>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="usuario" name="usuario" 
                                value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" 
                                placeholder="Ingrese su usuario" required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                placeholder="Ingrese su contraseña" required>
                            <span class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>
                
                <div class="login-links">
                    <a href="#" class="btn-link" id="btn-olvide-password">
                        <i class="fas fa-key"></i> ¿Olvidó su contraseña?
                    </a>
                </div>
                
                
            </div>
        </div>
    </div>

    <!-- Modal para recuperar contraseña -->
    <div class="modal <?php echo $mostrar_modal ? 'show' : ''; ?>" id="modalRecuperar">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">&times;</button>
            
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-key"></i> Recuperar Contraseña
                </h3>
                <p>Ingrese su usuario o email para restablecer su contraseña.</p>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email_usuario">Usuario o Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="text" id="email_usuario" name="email_usuario" 
                            value="<?php echo isset($_POST['email_usuario']) ? htmlspecialchars($_POST['email_usuario']) : ''; ?>" 
                            placeholder="Usuario o email" required>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelRecuperar">Cancelar</button>
                    <button type="submit" name="recuperar_password" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funcionalidad para mostrar/ocultar contraseña
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Funcionalidad del modal de recuperación
        const modal = document.getElementById('modalRecuperar');
        const btnOlvide = document.getElementById('btn-olvide-password');
        const closeModal = document.getElementById('closeModal');
        const cancelRecuperar = document.getElementById('cancelRecuperar');

        btnOlvide.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.add('show');
        });

        closeModal.addEventListener('click', function() {
            modal.classList.remove('show');
        });

        cancelRecuperar.addEventListener('click', function() {
            modal.classList.remove('show');
        });

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    </script>
</body>
</html>
<?php $conexion->close(); ?>