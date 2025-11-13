<?php
include 'conexion.php';

// Inicializar variables
$error = '';
$success = '';
$red = null;

// Obtener el ID de la red a editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=No se proporcionó un ID válido");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos actuales de la red
$sql = "SELECT * FROM redes WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: index.php?error=La red con ID $id no existe");
    exit();
}

$red = $resultado->fetch_assoc();
$stmt->close();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre_red = trim($_POST['nombre_red']);
    $tipo_red = trim($_POST['tipo_red']);
    $direccion_ip = trim($_POST['direccion_ip']);
    $mascara_subred = trim($_POST['mascara_subred']);
    $gateway = trim($_POST['gateway']);
    $dns_primario = trim($_POST['dns_primario']);
    $dns_secundario = trim($_POST['dns_secundario']);
    $ubicacion = trim($_POST['ubicacion']);
    $departamento = trim($_POST['departamento']);
    $responsable = trim($_POST['responsable']);
    $telefono_contacto = trim($_POST['telefono_contacto']);
    $fecha_instalacion = trim($_POST['fecha_instalacion']);
    $observaciones = trim($_POST['observaciones']);
    
    // Validaciones básicas
    if (empty($nombre_red) || empty($tipo_red)) {
        $error = "Los campos Nombre de Red y Tipo de Red son obligatorios.";
    } else {
        // Validar formato de IP si se proporciona
        if (!empty($direccion_ip) && !filter_var($direccion_ip, FILTER_VALIDATE_IP)) {
            $error = "La dirección IP no tiene un formato válido.";
        }
        
        if (empty($error)) {
            // Actualizar en la base de datos
            $sql = "UPDATE redes SET 
                    nombre_red = ?, 
                    tipo_red = ?, 
                    direccion_ip = ?, 
                    mascara_subred = ?, 
                    gateway = ?, 
                    dns_primario = ?, 
                    dns_secundario = ?, 
                    ubicacion = ?, 
                    departamento = ?, 
                    responsable = ?, 
                    telefono_contacto = ?, 
                    fecha_instalacion = ?, 
                    observaciones = ? 
                    WHERE id = ?";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssssssssssssi", 
                $nombre_red, $tipo_red, $direccion_ip, $mascara_subred, 
                $gateway, $dns_primario, $dns_secundario, $ubicacion, 
                $departamento, $responsable, $telefono_contacto, 
                $fecha_instalacion, $observaciones, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Red actualizada correctamente.";
                    // Actualizar los datos locales para mostrarlos
                    $red['nombre_red'] = $nombre_red;
                    $red['tipo_red'] = $tipo_red;
                    $red['direccion_ip'] = $direccion_ip;
                    $red['mascara_subred'] = $mascara_subred;
                    $red['gateway'] = $gateway;
                    $red['dns_primario'] = $dns_primario;
                    $red['dns_secundario'] = $dns_secundario;
                    $red['ubicacion'] = $ubicacion;
                    $red['departamento'] = $departamento;
                    $red['responsable'] = $responsable;
                    $red['telefono_contacto'] = $telefono_contacto;
                    $red['fecha_instalacion'] = $fecha_instalacion;
                    $red['observaciones'] = $observaciones;
                } else {
                    $error = "No se realizaron cambios en la red.";
                }
            } else {
                $error = "Error al actualizar la red: " . $stmt->error;
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
    <title>Editar Red - Municipalidad de General Belgrano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
    <style>
        .required-label::after {
            content: " *";
            color: #e74c3c;
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .input-hint {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .btn-loading {
            position: relative;
            color: transparent !important;
        }
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 0.7s ease infinite;
        }
        @keyframes button-loading-spinner {
            from { transform: rotate(0turn); }
            to { transform: rotate(1turn); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-network-wired fa-2x"></i>
            <h1>Editar Red - Municipalidad de General Belgrano</h1>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Editando: <?php echo htmlspecialchars($red['nombre_red']); ?></h2>
                <div>
                    <a href="detalles.php?id=<?php echo $red['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <div style="margin-top: 10px;">
                        <a href="index.php" class="btn btn-primary btn-sm">Volver al listado</a>
                        <a href="detalles.php?id=<?php echo $red['id']; ?>" class="btn btn-primary btn-sm">Ver detalles</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Error:</strong> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form id="form-editar-red" method="POST" onsubmit="return validateForm()">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-info-circle"></i> Información Básica
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre_red" class="required-label">Nombre de Red</label>
                                <input type="text" id="nombre_red" name="nombre_red" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['nombre_red']); ?>" required>
                                <div class="input-hint">Identificador único de la red</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tipo_red" class="required-label">Tipo de Red</label>
                                <select id="tipo_red" name="tipo_red" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Terminales" <?php echo $red['tipo_red'] == 'Terminales' ? 'selected' : ''; ?>>Terminales</option>
                                    <option value="Impresoras" <?php echo $red['tipo_red'] == 'Impresoras' ? 'selected' : ''; ?>>Impresoras</option>
                                    <option value="Router" <?php echo $red['tipo_red'] == 'Router' ? 'selected' : ''; ?>>Router</option>
                                    <option value="Servidor" <?php echo $red['tipo_red'] == 'Servidor' ? 'selected' : ''; ?>>Servidor</option>
                                    <option value="Relojes" <?php echo $red['tipo_red'] == 'Relojes' ? 'selected' : ''; ?>>Relojes</option>
                                    <option value="Otro" <?php echo $red['tipo_red'] == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ubicacion">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['ubicacion']); ?>" 
                                    placeholder="Ej: Palacio Municipal, Planta Baja">
                            </div>
                            
                            <div class="form-group">
                                <label for="departamento">Departamento</label>
                                <input type="text" id="departamento" name="departamento" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['departamento']); ?>" 
                                    placeholder="Ej: Informatica">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-server"></i> Configuración de Red
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="direccion_ip">Dirección IP</label>
                                <input type="text" id="direccion_ip" name="direccion_ip" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['direccion_ip']); ?>" 
                                    placeholder="Ej: 192.168.1.1" pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$">
                                <div class="input-hint">Formato: XXX.XXX.XXX.XXX</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-users"></i> Información de Contacto
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="responsable">Responsable</label>
                                <input type="text" id="responsable" name="responsable" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['responsable']); ?>" 
                                    placeholder="Nombre del responsable">
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono_contacto">Nombre de Equipo</label>
                                <input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['telefono_contacto']); ?>" 
                                    placeholder="Desktop-012345">
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha_instalacion">Fecha de Instalación</label>
                                <input type="date" id="fecha_instalacion" name="fecha_instalacion" class="form-control" 
                                    value="<?php echo htmlspecialchars($red['fecha_instalacion']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-sticky-note"></i> Observaciones
                        </h3>
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" 
                                rows="4" placeholder="Notas adicionales sobre esta red"><?php echo htmlspecialchars($red['observaciones']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" id="btn-guardar" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="detalles.php?id=<?php echo $red['id']; ?>" class="btn">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        function validateForm() {
            const nombreRed = document.getElementById('nombre_red').value.trim();
            const tipoRed = document.getElementById('tipo_red').value;
            const direccionIP = document.getElementById('direccion_ip').value.trim();
            
            // Validar campos obligatorios
            if (!nombreRed) {
                alert('El campo Nombre de Red es obligatorio.');
                document.getElementById('nombre_red').focus();
                return false;
            }
            
            if (!tipoRed) {
                alert('Debe seleccionar un Tipo de Red.');
                document.getElementById('tipo_red').focus();
                return false;
            }
            
            // Validar formato de IP si se proporciona
            if (direccionIP && !isValidIP