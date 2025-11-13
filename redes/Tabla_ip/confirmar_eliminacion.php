<?php
include 'conexion.php';

$id = $_GET['id'];
$red = null;

// Obtener datos de la red a eliminar
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
    <title>Confirmar Eliminación - Municipalidad de General Belgrano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <h1>Confirmar Eliminación - Municipalidad de General Belgrano</h1>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Confirmar Eliminación</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¿Está seguro de que desea eliminar esta red?</strong>
                    <p>Esta acción <?php echo $existe_columna_estado ? 'desactivará' : 'eliminará permanentemente'; ?> la red del sistema.</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre de Red:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['nombre_red']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Red:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['tipo_red']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección IP:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['direccion_ip']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ubicación:</label>
                        <div class="form-control" style="background-color: #f8f9fa;"><?php echo htmlspecialchars($red['ubicacion']); ?></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="eliminar.php?id=<?php echo $red['id']; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, <?php echo $existe_columna_estado ? 'Desactivar' : 'Eliminar'; ?>
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conexion->close(); ?>