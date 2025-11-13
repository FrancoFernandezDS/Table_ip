<?php
// Obtener credenciales de correos
$sql_correos = "SELECT * FROM redes_municipales.credenciales_correos WHERE estado = 'activa' ORDER BY nombre_usuario";
$resultado_correos = $condb->query($sql_correos);

// Obtener todas las áreas únicas para el filtro
$sql_areas = "SELECT DISTINCT nombre_usuario FROM redes_municipales.credenciales_correos WHERE estado = 'activa' ORDER BY nombre_usuario";
$resultado_areas = $condb->query($sql_areas);
$areas = [];
while($area = $resultado_areas->fetch_assoc()) {
    $areas[] = $area['nombre_usuario'];
}

// Verificar si hay filtro aplicado
$filtro_area = isset($_GET['filtro_area']) ? $_GET['filtro_area'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Aplicar filtros si existen
if ($filtro_area || $busqueda) {
    $sql_correos = "SELECT * FROM redes_municipales.credenciales_correos WHERE estado = 'activa'";
    
    if ($filtro_area) {
        $sql_correos .= " AND nombre_usuario = '" . $condb->real_escape_string($filtro_area) . "'";
    }
    
    if ($busqueda) {
        $sql_correos .= " AND (nombre_usuario LIKE '%" . $condb->real_escape_string($busqueda) . "%' 
                          OR email_oficial LIKE '%" . $condb->real_escape_string($busqueda) . "%' 
                          OR responsable LIKE '%" . $condb->real_escape_string($busqueda) . "%')";
    }
    
    $sql_correos .= " ORDER BY nombre_usuario";
    $resultado_correos = $condb->query($sql_correos);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Credenciales de Correos Oficiales</h4>
    <button class="btn btn-primary btn-sm" onclick="abrirModalNuevoCorreo()">
        <i class="fas fa-plus"></i> Agregar Correo
    </button>
</div>

<!-- Filtros y Buscador Compactos -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <label for="filtroArea" class="mb-1 small font-weight-bold">Filtrar por Área:</label>
                    <select class="form-control form-control-sm" id="filtroArea" onchange="aplicarFiltros()">
                        <option value="">Todas las áreas</option>
                        <?php foreach($areas as $area): ?>
                            <option value="<?php echo htmlspecialchars($area); ?>" 
                                <?php echo $filtro_area == $area ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="form-group mb-0">
                    <label for="busquedaCorreos" class="mb-1 small font-weight-bold">Buscar:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" id="busquedaCorreos" 
                               placeholder="Área, email o administrador..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($filtro_area || $busqueda): ?>
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="limpiarFiltros()" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <?php if ($filtro_area || $busqueda): ?>
                <div class="form-group mb-0">
                    <label class="mb-1 small font-weight-bold text-muted">Filtros:</label>
                    <div>
                        <button class="btn btn-outline-danger btn-sm btn-block" type="button" onclick="limpiarFiltros()">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($filtro_area || $busqueda): ?>
        <div class="row mt-2">
            <div class="col-12">
                <div class="d-flex align-items-center">
                    <small class="text-muted mr-2"><strong>Filtros aplicados:</strong></small>
                    <?php if ($filtro_area): ?>
                        <span class="badge badge-primary badge-sm mr-2">Área: <?php echo htmlspecialchars($filtro_area); ?></span>
                    <?php endif; ?>
                    <?php if ($busqueda): ?>
                        <span class="badge badge-info badge-sm">Búsqueda: "<?php echo htmlspecialchars($busqueda); ?>"</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Area</th>
                <th>Email Oficial</th>
                <th>Contraseña</th>
                <th>Administradores</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado_correos->num_rows > 0): ?>
                <?php while($correo = $resultado_correos->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($correo['nombre_usuario']); ?></strong>
                    </td>
                    <td>
                        <span class="text-primary"><?php echo htmlspecialchars($correo['email_oficial']); ?></span>
                    </td>
                    <td>
                        <div class="password-container">
                            <div class="input-group input-group-sm">
                                <input type="password" class="form-control form-control-sm password-field" 
                                       value="<?php echo htmlspecialchars($correo['password_email']); ?>" 
                                       readonly id="password-<?php echo $correo['id']; ?>"
                                       style="font-family: 'Courier New', monospace;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword(<?php echo $correo['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" type="button" onclick="copiarPassword('<?php echo htmlspecialchars($correo['password_email']); ?>', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted password-hint">Haz clic en el ojo para ver</small>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($correo['responsable']); ?></td>
                    <td>
                        <small class="text-muted"><?php echo htmlspecialchars($correo['fecha_actualizacion']); ?></small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-warning btn-sm" onclick="editarCorreo(<?php echo $correo['id']; ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarCorreo(<?php echo $correo['id']; ?>, '<?php echo htmlspecialchars($correo['nombre_usuario']); ?>')" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>
                                <?php if ($filtro_area || $busqueda): ?>
                                    No se encontraron correos que coincidan con los filtros aplicados.
                                <?php else: ?>
                                    No hay credenciales de correos registradas
                                <?php endif; ?>
                            </p>
                            <?php if ($filtro_area || $busqueda): ?>
                                <button class="btn btn-primary btn-sm" onclick="limpiarFiltros()">
                                    <i class="fas fa-times"></i> Limpiar filtros
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary btn-sm" onclick="abrirModalNuevoCorreo()">
                                    <i class="fas fa-plus"></i> Agregar la primera credencial
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar/editar correo -->
<div class="modal fade" id="modalAgregarCorreo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCorreoTitulo">Agregar Nuevo Correo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCorreo">
                    <input type="hidden" id="correo_id" name="correo_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_usuario">Area *</label>
                                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email_oficial">Email Oficial *</label>
                                <input type="email" class="form-control" id="email_oficial" name="email_oficial" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_email">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_email" name="password_email" required
                                           style="font-family: 'Courier New', monospace;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordModal()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_actualizacion">Fecha Actualización</label>
                                <input type="date" class="form-control" id="fecha_actualizacion" name="fecha_actualizacion" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="responsable_correo">Administradores</label>
                        <input type="text" class="form-control" id="responsable_correo" name="responsable">
                    </div>
                    <div class="form-group">
                        <label for="observaciones_correo">Observaciones</label>
                        <textarea class="form-control" id="observaciones_correo" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCorreo" onclick="guardarCorreo()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones de filtrado y búsqueda
function aplicarFiltros() {
    const filtroArea = document.getElementById('filtroArea').value;
    const busqueda = document.getElementById('busquedaCorreos').value;
    
    let url = 'index.php?tab=correos';
    
    if (filtroArea) {
        url += '&filtro_area=' + encodeURIComponent(filtroArea);
    }
    
    if (busqueda) {
        url += '&busqueda=' + encodeURIComponent(busqueda);
    }
    
    window.location.href = url;
}

function limpiarFiltros() {
    window.location.href = 'index.php?tab=correos';
}

// Permitir búsqueda con Enter
document.getElementById('busquedaCorreos')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        aplicarFiltros();
    }
});

// Funciones de contraseña
function togglePassword(id) {
    const input = document.getElementById('password-' + id);
    const icon = event.target.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
        event.target.classList.remove('btn-outline-secondary');
        event.target.classList.add('btn-outline-warning');
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
        event.target.classList.remove('btn-outline-warning');
        event.target.classList.add('btn-outline-secondary');
    }
}

function togglePasswordModal() {
    const input = document.getElementById('password_email');
    const icon = event.target.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function copiarPassword(password, btn) {
    if (!navigator.clipboard) {
        const temp = document.createElement('input');
        document.body.appendChild(temp);
        temp.value = password;
        temp.select();
        try {
            document.execCommand('copy');
            const icon = btn.querySelector('i');
            icon.className = 'fas fa-check';
            btn.classList.remove('btn-outline-info');
            btn.classList.add('btn-outline-success');
            setTimeout(() => {
                icon.className = 'fas fa-copy';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-outline-info');
            }, 1500);
        } catch (e) {
            alert('No se pudo copiar la contraseña');
        }
        document.body.removeChild(temp);
        return;
    }

    navigator.clipboard.writeText(password).then(function() {
        const icon = btn.querySelector('i');
        icon.className = 'fas fa-check';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-outline-success');
        
        setTimeout(() => {
            icon.className = 'fas fa-copy';
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-outline-info');
        }, 1500);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar la contraseña');
    });
}

// Funciones del modal
function abrirModalNuevoCorreo() {
    document.getElementById('modalCorreoTitulo').textContent = 'Agregar Nuevo Correo';
    document.getElementById('formCorreo').reset();
    document.getElementById('correo_id').value = '';
    document.getElementById('fecha_actualizacion').value = '<?php echo date('Y-m-d'); ?>';
    $('#modalAgregarCorreo').modal('show');
}

function editarCorreo(id) {
    // Mostrar loading
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;
    
    fetch('obtener_correo.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalCorreoTitulo').textContent = 'Editar Correo';
                document.getElementById('correo_id').value = data.data.id;
                document.getElementById('nombre_usuario').value = data.data.nombre_usuario;
                document.getElementById('email_oficial').value = data.data.email_oficial;
                document.getElementById('password_email').value = data.data.password_email;
                document.getElementById('responsable_correo').value = data.data.responsable;
                document.getElementById('fecha_actualizacion').value = data.data.fecha_actualizacion;
                document.getElementById('observaciones_correo').value = data.data.observaciones;
                
                $('#modalAgregarCorreo').modal('show');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
}

function eliminarCorreo(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el correo "${nombre}"?`)) {
        // Mostrar loading
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('eliminar_correo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Correo eliminado correctamente');
                // Recargar manteniendo la pestaña activa
                window.location.href = 'index.php?tab=correos';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function guardarCorreo() {
    const form = document.getElementById('formCorreo');
    const formData = new FormData(form);
    
    // Validaciones
    if (!formData.get('nombre_usuario') || !formData.get('email_oficial') || !formData.get('password_email')) {
        alert('Por favor complete todos los campos obligatorios (*)');
        return;
    }
    
    // Mostrar loading
    const btn = document.getElementById('btnGuardarCorreo');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';
    btn.disabled = true;
    
    fetch('guardar_correo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#modalAgregarCorreo').modal('hide');
            alert('Correo guardado correctamente');
            setTimeout(() => {
                // Recargar manteniendo la pestaña activa
                window.location.href = 'index.php?tab=correos';
            }, 1000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
</script>

<style>
.password-container {
    position: relative;
}
.password-hint {
    font-size: 0.7rem;
    display: block;
    margin-top: 2px;
}
.password-field {
    font-family: 'Courier New', monospace !important;
    background-color: #f8f9fa;
}
.input-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
.badge-sm {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
}
</style>