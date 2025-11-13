<?php
include 'conexion.php';

// Configuración de paginación
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Búsqueda y filtros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';

// Construir consulta base
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM redes WHERE estado = 'activa'";
$count_sql = "SELECT COUNT(*) as total FROM redes WHERE estado = 'activa'";

if (!empty($search)) {
    $sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%' OR responsable LIKE '%$search%')";
    $count_sql .= " AND (nombre_red LIKE '%$search%' OR direccion_ip LIKE '%$search%' OR ubicacion LIKE '%$search%' OR responsable LIKE '%$search%')";
}

if (!empty($filter_type)) {
    $sql .= " AND tipo_red = '$filter_type'";
    $count_sql .= " AND tipo_red = '$filter_type'";
}

$sql .= " ORDER BY id DESC LIMIT $start, $limit";

$resultado = $conexion->query($sql);
$total_resultados = $conexion->query("SELECT FOUND_ROWS() as total");
$total_resultados = $total_resultados->fetch_assoc()['total'];
$total_paginas = ceil($total_resultados / $limit);

// Obtener estadísticas para el dashboard
$stats = [
    'total_redes' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE estado = 'activa'")->fetch_assoc()['total'],
    'redes_Terminales' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE tipo_red = 'Terminales' AND estado = 'activa'")->fetch_assoc()['total'],
    'redes_Impresoras' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE tipo_red = 'Impresoras' AND estado = 'activa'")->fetch_assoc()['total'],
    'redes_Router' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE tipo_red = 'Router' AND estado = 'activa'")->fetch_assoc()['total'],
    'redes_Servidor' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE tipo_red = 'Servidor' AND estado = 'activa'")->fetch_assoc()['total'],
    'redes_Relojes' => $conexion->query("SELECT COUNT(*) as total FROM redes WHERE tipo_red = 'Relojes' AND estado = 'activa'")->fetch_assoc()['total']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Redes - Municipalidad de General Belgrano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <i class="fas fa-network-wired fa-2x"></i>
            <h1>Gestión de Redes - Municipalidad de General Belgrano</h1>
        </div>
        <div class="header-actions">
            <a href="mi_cuenta.php" class="btn btn-primary"><i class="fas fa-user"></i> Mi Cuenta</a>
        </div>
        </div>
            <a href="logout.php" class="dropdown-item">
                 <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <div class="container">
        <!-- Dashboard con estadísticas -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total de Redes</h3>
                <div class="number"><?php echo $stats['total_redes']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Terminales</h3>
                <div class="number"><?php echo $stats['redes_Terminales']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Impresoras</h3>
                <div class="number"><?php echo $stats['redes_Impresoras']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Router</h3>
                <div class="number"><?php echo $stats['redes_Router']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Relojes</h3>
                <div class="number"><?php echo $stats['redes_Relojes']; ?></div>
            </div>
        </div>

        <!-- Card principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Listado de Redes</h2>
                <div class="search-container">
                    <div class="col-auto">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-input" class="form-control" placeholder="Buscar redes..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <select class="form-control" id="filter-type">
                        <option value="">Todos los tipos</option>
                        <option value="Terminales" <?php echo $filter_type == 'Terminales' ? 'selected' : ''; ?>>Terminales</option>
                        <option value="Impresoras" <?php echo $filter_type == 'Impresoras' ? 'selected' : ''; ?>>Impresoras</option>
                        <option value="Router" <?php echo $filter_type == 'Router' ? 'selected' : ''; ?>>Router</option>
                        <option value="servidor" <?php echo $filter_type == 'Servidor' ? 'selected' : ''; ?>>Servidor</option>
                        <option value="Relojes" <?php echo $filter_type == 'Relojes' ? 'selected' : ''; ?>>Relojes</option>
                    </select>
                    <a href="agregar.php" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Red</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['mensaje'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre de Red</th>
                                <th>Tipo</th>
                                <th>Dirección IP</th>
                                <th>Ubicación</th>
                                <th>Departamento</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                            <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['nombre_red']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $fila['tipo_red'] == 'Terminales' ? 'badge-info' : ''; ?>
                                        <?php echo $fila['tipo_red'] == 'Impresoras' ? 'badge-success' : ''; ?>
                                        <?php echo $fila['tipo_red'] == 'Router' ? 'badge-warning' : ''; ?>
                                        <?php echo $fila['tipo_red'] == 'Servidor' ? 'badge-danger' : ''; ?>
                                        <?php echo $fila['tipo_red'] == 'Otro' ? 'badge-secondary' : ''; ?>
                                        <?php echo $fila['tipo_red'] == 'Relojes' ? 'badge-purple' : ''; ?>">
                                        <?php echo htmlspecialchars($fila['tipo_red']); ?>
                                    </span>
                                    
                                </td>
                                <td><?php echo htmlspecialchars($fila['direccion_ip']); ?></td>
                                <td><?php echo htmlspecialchars($fila['ubicacion']); ?></td>
                                <td><?php echo htmlspecialchars($fila['departamento']); ?></td>
                                <td><?php echo htmlspecialchars($fila['responsable']); ?></td>
                                <td class="actions">
                                    <a href="detalles.php?id=<?php echo $fila['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="editar.php?id=<?php echo $fila['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <a href="confirmar_eliminacion.php?id=<?php echo $fila['id']; ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No se encontraron redes registradas.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>"><i class="fas fa-chevron-left"></i></a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li><a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($page < $total_paginas): ?>
                    <li><a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($filter_type) ? '&filter_type='.urlencode($filter_type) : ''; ?>"><i class="fas fa-chevron-right"></i></a></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad de búsqueda y filtrado
        document.getElementById('search-input').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value;
                const filterValue = document.getElementById('filter-type').value;
                
                let url = 'index.php?';
                if (searchValue) url += 'search=' + encodeURIComponent(searchValue);
                if (filterValue) url += (searchValue ? '&' : '') + 'filter_type=' + encodeURIComponent(filterValue);
                
                window.location.href = url;
            }
        });

        document.getElementById('filter-type').addEventListener('change', function() {
            const searchValue = document.getElementById('search-input').value;
            const filterValue = this.value;
            
            let url = 'index.php?';
            if (searchValue) url += 'search=' + encodeURIComponent(searchValue);
            if (filterValue) url += (searchValue ? '&' : '') + 'filter_type=' + encodeURIComponent(filterValue);
            
            window.location.href = url;
        });
    </script>
</body>
</html>
<?php $conexion->close(); ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php endif; ?>