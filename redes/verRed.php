<?php
// verRed.php (modificado para mostrar máscara, puerta de enlace y DNS)
session_start();
include('../assets/inc/condb.php');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    exit;
}

$id = intval($_GET['id']);
if ($id <= 0) {
    echo "<div class='modal-header'><h5 class='modal-title'>ID inválido</h5></div>";
    exit;
}

$sql = "SELECT * FROM redes_municipales.redes WHERE id = ?";
$stmt = $condb->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$red = $result->fetch_assoc();

if (!$red) {
    echo "<div class='modal-header'><h5 class='modal-title'>Red no encontrada</h5></div>";
    exit;
}

// sanitize helper
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>

<div class="modal-header">
    <h5 class="modal-title">Detalles de Red: <?php echo h($red['nombre_red']); ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div id="remote-config-alert" class="mb-2"></div>

    <div class="row">
        <div class="col-md-6">
            <h6>Información Básica</h6>
            <p><strong>Nombre:</strong> <?php echo h($red['nombre_red']); ?></p>
            <p><strong>Tipo:</strong> <?php echo h($red['tipo_red']); ?></p>
            <p><strong>IP:</strong> <?php echo h($red['direccion_ip']); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Configuración</h6>
            <p><strong>Fecha Instalación:</strong> <?php echo h($red['fecha_instalacion']); ?></p>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <h6>Ubicación</h6>
            <p><strong>Ubicación:</strong> <?php echo h($red['ubicacion']); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Contacto</h6>
            <p><strong>Departamento:</strong> <?php echo h($red['departamento']); ?></p>
        </div>
    </div>
    <?php if (!empty($red['observaciones'])): ?>
    <div class="row mt-3">
        <div class="col-12">
            <h6>Observaciones</h6>
            <p><?php echo nl2br(h($red['observaciones'])); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <hr>

    <div id="remote-config-section" class="mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Configuración remota (rápida)</h6>
            <div>
                <button id="btn-obtener-config" class="btn btn-sm btn-primary">
                    <span id="spinner-config" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Obtener configuración remota
                </button>
            </div>
        </div>

        <div id="remote-config-result" style="display:none;">
            <div class="card card-body">
                <p><strong>IP:</strong> <span id="rc-ip"></span></p>
                <p><strong>Reachable (ping):</strong> <span id="rc-reachable"></span></p>
                <p><strong>Hostname (reverse DNS):</strong> <span id="rc-hostname"></span></p>
                <p><strong>MAC (ARP):</strong> <span id="rc-mac"></span></p>
                <p><strong>Puerta de enlace (ruta local):</strong> <span id="rc-gateway"></span></p>
                <p><strong>Máscara (interfaz local):</strong> <span id="rc-netmask"></span></p>
                <p><strong>DNS (servidor):</strong> <span id="rc-dns"></span></p>
                <p><strong>Puertos abiertos:</strong> <span id="rc-ports"></span></p>
                <p><strong>OS (inferencia):</strong> <span id="rc-os"></span></p>
                <div id="rc-notes" class="mt-2"></div>
            </div>
        </div>

        <div id="remote-config-error" class="alert alert-danger mt-2 d-none"></div>
    </div>

</div>
<div class="modal-footer">
    <a href="editar.php?id=<?php echo intval($red['id']); ?>" class="btn btn-warning">
        <i class="fas fa-edit"></i> Editar
    </a>
    <a href="confirmar_eliminacion.php?id=<?php echo intval($red['id']); ?>" class="btn btn-danger">
        <i class="fas fa-trash"></i> Eliminar
    </a>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
</div>

<script>
(function(){
    const ip = "<?php echo h($red['direccion_ip']); ?>";
    const btn = document.getElementById('btn-obtener-config');
    const spinner = document.getElementById('spinner-config');
    const resultBox = document.getElementById('remote-config-result');
    const errorBox = document.getElementById('remote-config-error');
    const alertBox = document.getElementById('remote-config-alert');

    function setLoading(on) {
        if (on) {
            spinner.classList.remove('d-none');
            btn.disabled = true;
            resultBox.style.display = 'none';
            errorBox.classList.add('d-none');
            alertBox.innerHTML = '';
        } else {
            spinner.classList.add('d-none');
            btn.disabled = false;
        }
    }

    btn.addEventListener('click', function(e){
        e.preventDefault();
        if (!ip) return;
        if (!confirm('¿Obtener configuración remota de ' + ip + '?\n\nSe ejecutarán comprobaciones rápidas (ping, ARP, puertos).')) return;

        setLoading(true);

        fetch('api_get_network_config.php?ip=' + encodeURIComponent(ip))
        .then(resp => {
            if (!resp.ok) throw new Error('Respuesta del servidor: ' + resp.status);
            return resp.json();
        })
        .then(data => {
            setLoading(false);

            if (!data.success) {
                errorBox.textContent = data.error || 'Error desconocido';
                errorBox.classList.remove('d-none');
                return;
            }

            // rellenar resultados
            document.getElementById('rc-ip').textContent = data.ip || '';
            document.getElementById('rc-reachable').textContent = data.reachable ? 'Sí' : 'No';
            document.getElementById('rc-hostname').textContent = data.hostname || 'N/D';
            document.getElementById('rc-mac').textContent = data.mac || 'N/D';
            document.getElementById('rc-ports').textContent = (data.open_ports && data.open_ports.length) ? data.open_ports.join(', ') : 'Ninguno detectado';
            document.getElementById('rc-os').textContent = data.os_guess || 'N/D';

            // nueva información: gateway, netmask, dns
            document.getElementById('rc-gateway').textContent = data.gateway || 'N/D';
            document.getElementById('rc-netmask').textContent = data.netmask || 'N/D';
            document.getElementById('rc-dns').textContent = (data.dns && data.dns.length) ? data.dns.join(', ') : 'N/D';

            // notas (si existen)
            const notesEl = document.getElementById('rc-notes');
            notesEl.innerHTML = '';
            if (data.notes && data.notes.length) {
                const ul = document.createElement('ul');
                ul.className = 'mb-0';
                data.notes.forEach(n => {
                    const li = document.createElement('li');
                    li.textContent = n;
                    ul.appendChild(li);
                });
                notesEl.appendChild(ul);
            }

            // mostrar alerta si no alcanzable
            if (!data.reachable) {
                alertBox.innerHTML = '<div class="alert alert-warning">La IP no responde a ping. Es posible que ICMP esté bloqueado; aun así se muestran puertos detectados y la información inferida del servidor.</div>';
            }

            resultBox.style.display = 'block';
            errorBox.classList.add('d-none');
        })
        .catch(err => {
            setLoading(false);
            errorBox.textContent = 'Error al obtener la configuración remota: ' + err.message;
            errorBox.classList.remove('d-none');
        });
    });
})();
</script>