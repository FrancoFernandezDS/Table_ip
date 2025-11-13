<?php
// Obtener credenciales de carpetas
$sql_carpetas = "SELECT * FROM redes_municipales.credenciales_carpetas WHERE estado = 'activa' ORDER BY nombre_carpeta";
$resultado_carpetas = $condb->query($sql_carpetas);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Credenciales de Carpetas</h4>
    <button class="btn btn-primary btn-sm" onclick="abrirModalNuevaCarpeta()">
        <i class="fas fa-plus"></i> Agregar Carpeta
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Dependencia</th>
                <th>Usuario</th>
                <th>Contraseña</th>
                <th>Grupo</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado_carpetas->num_rows > 0): ?>
                <?php while($carpeta = $resultado_carpetas->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($carpeta['nombre_carpeta']); ?></strong>
                    </td>
                    <td>
                        <code><?php echo htmlspecialchars($carpeta['usuario_carpeta']); ?></code>
                    </td>
                    <td>
                        <div class="password-container">
                            <div class="input-group input-group-sm">
                                <input type="password" class="form-control form-control-sm password-field" 
                                       value="<?php echo htmlspecialchars($carpeta['password_carpeta']); ?>" 
                                       readonly id="password-carpeta-<?php echo $carpeta['id']; ?>"
                                       style="font-family: 'Courier New', monospace;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePasswordCarpeta(<?php echo $carpeta['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" type="button" onclick="copiarPassword('<?php echo htmlspecialchars($carpeta['password_carpeta']); ?>', this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted password-hint">Haz clic en el ojo para ver</small>
                        </div>
                    </td>
                    <td>
                        <small class="text-muted font-italic"><?php echo htmlspecialchars($carpeta['ruta_acceso']); ?></small>
                    </td>
                    <td>
                        <small class="text-muted"><?php echo htmlspecialchars($carpeta['fecha_actualizacion']); ?></small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-warning btn-sm" onclick="editarCarpeta(<?php echo $carpeta['id']; ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarCarpeta(<?php echo $carpeta['id']; ?>, '<?php echo htmlspecialchars($carpeta['nombre_carpeta']); ?>')" title="Eliminar">
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
                            <i class="fas fa-folder fa-2x mb-2"></i>
                            <p>No hay credenciales de carpetas registradas</p>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalNuevaCarpeta()">
                                <i class="fas fa-plus"></i> Agregar la primera credencial
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar/editar carpeta -->
<div class="modal fade" id="modalAgregarCarpeta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCarpetaTitulo">Agregar Nueva Carpeta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCarpeta">
                    <input type="hidden" id="carpeta_id" name="carpeta_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_carpeta">Dependencia *</label>
                                <input type="text" class="form-control" id="nombre_carpeta" name="nombre_carpeta" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario_carpeta">Usuario *</label>
                                <input type="text" class="form-control" id="usuario_carpeta" name="usuario_carpeta" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_carpeta">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_carpeta" name="password_carpeta" required
                                           style="font-family: 'Courier New', monospace;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordModalCarpeta()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_actualizacion_carpeta">Fecha Actualización</label>
                                <input type="date" class="form-control" id="fecha_actualizacion_carpeta" name="fecha_actualizacion" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ruta_acceso">Grupo</label>
                        <input type="text" class="form-control" id="ruta_acceso" name="ruta_acceso" 
                               placeholder="Ej: Informatica">
                    </div>
                    <div class="form-group">
                        <label for="observaciones_carpeta">Observaciones</label>
                        <textarea class="form-control" id="observaciones_carpeta" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCarpeta" onclick="guardarCarpeta()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones de contraseña para carpetas
function togglePasswordCarpeta(id) {
    const input = document.getElementById('password-carpeta-' + id);
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

function togglePasswordModalCarpeta() {
    const input = document.getElementById('password_carpeta');
    const icon = event.target.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Funciones del modal para carpetas
function abrirModalNuevaCarpeta() {
    document.getElementById('modalCarpetaTitulo').textContent = 'Agregar Nueva Carpeta';
    document.getElementById('formCarpeta').reset();
    document.getElementById('carpeta_id').value = '';
    document.getElementById('fecha_actualizacion_carpeta').value = '<?php echo date('Y-m-d'); ?>';
    $('#modalAgregarCarpeta').modal('show');
}

function editarCarpeta(id) {
    // Mostrar loading
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;
    
    fetch('obtener_carpeta.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalCarpetaTitulo').textContent = 'Editar Carpeta';
                document.getElementById('carpeta_id').value = data.data.id;
                document.getElementById('nombre_carpeta').value = data.data.nombre_carpeta;
                document.getElementById('usuario_carpeta').value = data.data.usuario_carpeta;
                document.getElementById('password_carpeta').value = data.data.password_carpeta;
                document.getElementById('ruta_acceso').value = data.data.ruta_acceso;
                document.getElementById('fecha_actualizacion_carpeta').value = data.data.fecha_actualizacion;
                document.getElementById('observaciones_carpeta').value = data.data.observaciones;
                
                $('#modalAgregarCarpeta').modal('show');
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

function eliminarCarpeta(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la carpeta "${nombre}"?`)) {
        // Mostrar loading
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('eliminar_carpeta.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Carpeta eliminada correctamente');
                // Recargar manteniendo la pestaña activa
                window.location.href = 'index.php?tab=carpetas';
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

function guardarCarpeta() {
    const form = document.getElementById('formCarpeta');
    const formData = new FormData(form);
    
    console.log('Datos del formulario carpeta:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Validaciones
    if (!formData.get('nombre_carpeta') || !formData.get('usuario_carpeta') || !formData.get('password_carpeta')) {
        alert('Por favor complete todos los campos obligatorios (*)');
        return;
    }
    
    // Mostrar loading
    const btn = document.getElementById('btnGuardarCarpeta');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';
    btn.disabled = true;
    
    fetch('guardar_carpeta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        if (data.success) {
            $('#modalAgregarCarpeta').modal('hide');
            alert('Carpeta guardada correctamente');
            setTimeout(() => {
                // Recargar manteniendo la pestaña activa
                window.location.href = 'index.php?tab=carpetas';
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