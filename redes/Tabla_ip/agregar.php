<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_red = $_POST['nombre_red'];
    $tipo_red = $_POST['tipo_red'];
    $direccion_ip = $_POST['direccion_ip'];
    $mascara_subred = $_POST['mascara_subred'];
    $gateway = $_POST['gateway'];
    $dns_primario = $_POST['dns_primario'];
    $dns_secundario = $_POST['dns_secundario'];
    $ubicacion = $_POST['ubicacion'];
    $departamento = $_POST['departamento'];
    $responsable = $_POST['responsable'];
    $telefono_contacto = $_POST['telefono_contacto'];
    $fecha_instalacion = $_POST['fecha_instalacion'];
    $observaciones = $_POST['observaciones'];
    
    $sql = "INSERT INTO redes (nombre_red, tipo_red, direccion_ip, mascara_subred, gateway, dns_primario, dns_secundario, ubicacion, departamento, responsable, telefono_contacto, fecha_instalacion, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssssssss", $nombre_red, $tipo_red, $direccion_ip, $mascara_subred, $gateway, $dns_primario, $dns_secundario, $ubicacion, $departamento, $responsable, $telefono_contacto, $fecha_instalacion, $observaciones);
    
    if ($stmt->execute()) {
        header("Location: index.php?mensaje=Red agregada correctamente");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Red</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-cancelar {
            background: #95a5a6;
        }
        .btn-cancelar:hover {
            background: #7f8c8d;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Agregar Nueva Red</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nombre_red">Nombre de Red*:</label>
                <input type="text" id="nombre_red" name="nombre_red" required>
            </div>
            
            <div class="form-group">
                <label for="tipo_red">Tipo de Red*:</label>
                <select id="tipo_red" name="tipo_red" required>
                    <option value="">Seleccione...</option>
                    <option value="Terminales">Terminales</option>
                    <option value="Impresoras">Impresoras</option>
                    <option value="Router">Router</option>
                    <option value="Servidor">Servidor</option>
                    <option value="Relojes">Relojes</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="direccion_ip">Dirección IP:</label>
                <input type="text" id="direccion_ip" name="direccion_ip" placeholder="Ej: 192.168.1.1">
            </div>
            <div class="form-group">
                <label for="ubicacion">Ubicación:</label>
                <input type="text" id="ubicacion" name="ubicacion" placeholder="Ej: Edificio Central, Planta Baja">
            </div>
            
            <div class="form-group">
                <label for="departamento">Departamento:</label>
                <input type="text" id="departamento" name="departamento" placeholder="Ej: Sistemas">
            </div>
            
            <div class="form-group">
                <label for="responsable">Responsable:</label>
                <input type="text" id="responsable" name="responsable" placeholder="Nombre del responsable">
            </div>
            
            <div class="form-group">
                <label for="telefono_contacto">Nombre de Equipo:</label>
                <input type="text" id="telefono_contacto" name="telefono_contacto" placeholder="Desktop-012345">
            </div>
            
            <div class="form-group">
                <label for="fecha_instalacion">Fecha de Instalación:</label>
                <input type="date" id="fecha_instalacion" name="fecha_instalacion">
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones:</label>
                <textarea id="observaciones" name="observaciones" rows="4"></textarea>
            </div>
            
            <button type="submit" class="btn">Guardar Red</button>
            <a href="index.php" class="btn btn-cancelar">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $conexion->close(); ?>