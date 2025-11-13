<?php
include 'conexion.php';

$id = $_GET['id'];
$red = null;

// Obtener datos de la red
if ($resultado = $conexion->query("SELECT * FROM redes WHERE id = $id")) {
    $red = $resultado->fetch_assoc();
    $resultado->free();
} else {
    header("Location: index.php?error=Red no encontrada");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Red - Municipalidad de General Belgrano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-network-wired fa-2x"></i>
            <h1>Detalles de Red - Municipalidad de General Belgrano</h1>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Información de la Red: <?php echo htmlspecialchars($red['nombre_red']); ?></h2>
                <div>
                    <a href="editar.php?id=<?php echo $red['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-list"></i> Listado</a>
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre de Red:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['nombre_red']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Red:</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            <span class="badge 
                                <?php echo $red['tipo_red'] == 'LAN' ? 'badge-info' : ''; ?>
                                <?php echo $red['tipo_red'] == 'WLAN' ? 'badge-success' : ''; ?>
                                <?php echo $red['tipo_red'] == 'WAN' ? 'badge-warning' : ''; ?>
                            ">
                                <?php echo htmlspecialchars($red['tipo_red']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección IP:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['direccion_ip']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ubicación:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['ubicacion']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Departamento:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['departamento']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Responsable:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['responsable']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de Equipo:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['telefono_contacto']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha de Instalación:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['fecha_instalacion']); ?></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Observaciones:</label>
                    <div class="form-control" style="background-color: #f8f9fa; min-height: 100px;"><?php echo htmlspecialchars($red['observaciones']); ?></div>
                </div>
                
                <div class="form-actions">
                    <a href="editar.php?id=<?php echo $red['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conexion->close(); ?>