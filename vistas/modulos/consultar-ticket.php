<?php
$ticketId = isset($_GET['idTicket']) ? $_GET['idTicket'] : null;
$usuarioId = $_SESSION['id'] ?? null;
$ticket = null;
$tienePermiso = false;
$esSesionTemporal = false;
$errorConsulta = '';

if ($_POST && isset($_POST['consultarTicket'])) {
    $numeroTicket = $_POST['numeroTicket'] ?? '';
    $emailUsuario = $_POST['emailUsuario'] ?? '';
    
    if (!empty($numeroTicket) && !empty($emailUsuario)) {
        $ticketConsulta = ControladorTickets::ctrMostrarTickets("id", $numeroTicket);
        
        if ($ticketConsulta && is_array($ticketConsulta)) {
            $creadorTicket = ControladorUsuarios::ctrMostrarUsuarios("id", $ticketConsulta['usuario_creador_id']);
            
            if ($creadorTicket && $creadorTicket['email'] === $emailUsuario) {
                $_SESSION['consulta_temporal'] = [
                    'ticket_id' => $ticketConsulta['id'],
                    'usuario_id' => $ticketConsulta['usuario_creador_id'],
                    'email' => $emailUsuario,
                    'tipo' => 'consulta_publica'
                ];
                
                $ticketId = $ticketConsulta['id'];
                $ticket = $ticketConsulta;
                $tienePermiso = true;
                $esSesionTemporal = true;
                $usuarioId = $ticketConsulta['usuario_creador_id'];
            } else {
                $errorConsulta = "El número de ticket y email no coinciden.";
            }
        } else {
            $errorConsulta = "No se encontró un ticket con ese número.";
        }
    } else {
        $errorConsulta = "Por favor complete todos los campos.";
    }
}

if ($ticketId && !$ticket) {
    $ticket = ControladorTickets::ctrMostrarTickets("id", $ticketId);
    
    if ($ticket && is_array($ticket)) {
        if ($usuarioId && $usuarioId == $ticket['usuario_creador_id']) {
            $tienePermiso = true;
        } elseif (isset($_SESSION['consulta_temporal'])) {
            $consultaTemporal = $_SESSION['consulta_temporal'];
            if ($consultaTemporal['ticket_id'] == $ticketId) {
                $creadorTicket = ControladorUsuarios::ctrMostrarUsuarios("id", $ticket['usuario_creador_id']);
                if ($creadorTicket && $creadorTicket['email'] === $consultaTemporal['email']) {
                    $tienePermiso = true;
                    $esSesionTemporal = true;
                    $usuarioId = $ticket['usuario_creador_id'];
                }
            }
        }
    }
}

if (!$ticket || !$tienePermiso) {
    ?>
    <section class="section-py first-section-pt help-center-header position-relative overflow-hidden">
  <img class="banner-bg-img z-n1" src="vistas/assets/img/pages/header.png" alt="Help center header" />
  <h4 class="text-center text-primary">Hola, ¿cómo podemos ayudarte?</h4>
  <p class="text-center px-4 mb-0">¿Tienes alguna duda o comentario? Completa el formulario y nuestro equipo te asistirá.</p>
</section>
    <section class="section-py bg-body">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            <h4 class="mb-0">Consultar Estado del Ticket</h4>
                            <p class="text-muted mb-0">Ingrese los datos de su ticket para consultarlo</p>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errorConsulta)): ?>
                                <div class="alert alert-danger">
                                    <i class="bx bx-error-circle"></i> <?= $errorConsulta ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" id="formConsultarTicket">
                                <div class="mb-3">
                                    <label for="numeroTicket" class="form-label">Número de Ticket</label>
                                    <input type="number" class="form-control" id="numeroTicket" name="numeroTicket" 
                                           placeholder="Ej: 123" value="<?= isset($_POST['numeroTicket']) ? htmlspecialchars($_POST['numeroTicket']) : '' ?>" 
                                           min="1" step="1" pattern="[0-9]+" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese solo números positivos.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="emailUsuario" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="emailUsuario" name="emailUsuario" 
                                           placeholder="Su correo electrónico" value="<?= isset($_POST['emailUsuario']) ? htmlspecialchars($_POST['emailUsuario']) : '' ?>" required>
                                </div>
                                
                                <button type="submit" name="consultarTicket" class="btn btn-primary w-100">
                                    <i class="bx bx-search me-1"></i>Consultar Ticket
                                </button>
                            </form>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bx bx-info-circle"></i> 
                                    Use el email con el que creó el ticket
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const numeroTicketInput = document.getElementById('numeroTicket');
        const formConsultar = document.getElementById('formConsultarTicket');
        
        numeroTicketInput.addEventListener('input', function(e) {
            let value = e.target.value;
            
            value = value.replace(/[^0-9]/g, '');
            
            if (value.length > 1 && value.charAt(0) === '0') {
                value = value.substring(1);
            }
            
            e.target.value = value;
            
            if (value === '' || parseInt(value) < 1) {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            } else {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            }
        });
        
        numeroTicketInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            paste = paste.replace(/[^0-9]/g, '');
            
            if (paste && parseInt(paste) > 0) {
                e.target.value = paste;
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            }
        });
        
        numeroTicketInput.addEventListener('keydown', function(e) {
            if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        
        formConsultar.addEventListener('submit', function(e) {
            const numeroTicket = numeroTicketInput.value;
            
            if (!numeroTicket || parseInt(numeroTicket) < 1) {
                e.preventDefault();
                numeroTicketInput.classList.add('is-invalid');
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Número de ticket inválido',
                        text: 'Por favor ingrese un número de ticket válido (solo números positivos).'
                    });
                } else {
                    alert('Por favor ingrese un número de ticket válido (solo números positivos).');
                }
                
                numeroTicketInput.focus();
                return false;
            }
        });
    });
    </script>
    <?php
    return;
}

$tecnico = ControladorUsuarios::ctrMostrarUsuarios("id", $ticket['tecnico_asignado_id']);
$categoria = ControladorCategorias::ctrMostrarCategorias("id", $ticket['categoria_id']);
$prioridad = ControladorPrioridades::ctrMostrarPrioridades("id", $ticket['prioridad_id']);
$creador = ControladorUsuarios::ctrMostrarUsuarios("id", $ticket['usuario_creador_id']);
?>
<section class="section-py first-section-pt help-center-header position-relative overflow-hidden">
  <img class="banner-bg-img z-n1" src="vistas/assets/img/pages/header.png" alt="Help center header" />
  <h4 class="text-center text-primary">Hola, ¿cómo podemos ayudarte?</h4>
  <p class="text-center px-4 mb-0">¿Tienes alguna duda o comentario? Completa el formulario y nuestro equipo te asistirá.</p>
</section>
<section class="section-py bg-body">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Ticket #<?= htmlspecialchars($ticket['id']) ?></h5>
                           <?php if ($esSesionTemporal): ?>
                                                    <a href="index.php?ruta=consultar-ticket" class="btn btn-outline-secondary">
                                                        <i class="bx bx-arrow-back me-1"></i>Nueva consulta
                                                    </a>
                                                <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Pestañas -->
                        <ul class="nav nav-tabs" id="ticketTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="detalles-tab" data-bs-toggle="tab" data-bs-target="#detalles" type="button" role="tab" aria-controls="detalles" aria-selected="true">
                                    <i class="bx bx-info-circle me-1"></i>Detalles del Ticket
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="comentarios-tab" data-bs-toggle="tab" data-bs-target="#comentarios" type="button" role="tab" aria-controls="comentarios" aria-selected="false">
                                    <i class="bx bx-comment me-1"></i>Comentarios / Archivos
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-4" id="ticketTabsContent">
                            <!-- Detalles del Ticket -->
                            <div class="tab-pane fade show active" id="detalles" role="tabpanel" aria-labelledby="detalles-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Título:</h6>
                                            <p><?= htmlspecialchars($ticket['titulo'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Descripción:</h6>
                                            <p><?= nl2br(htmlspecialchars($ticket['descripcion'] ?? 'N/A')) ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Estado:</h6>
                                            <?php 
                                                $estadoClase = [
                                                    'abierto'     => 'bg-primary',
                                                    'en_proceso'  => 'bg-warning',
                                                    'resuelto'    => 'bg-success',
                                                    'cerrado'     => 'bg-secondary'
                                                ];
                                                $clase = $estadoClase[$ticket['estado']] ?? 'bg-info';
                                            ?>
                                            <span class="badge <?= $clase ?>"><?= ucfirst(str_replace('_', ' ', $ticket['estado'] ?? 'N/A')) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Creado por:</h6>
                                            <p><?= htmlspecialchars($creador['nombre'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Técnico asignado:</h6>
                                            <p><?= htmlspecialchars($tecnico['nombre'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Categoría:</h6>
                                            <p><?= htmlspecialchars($categoria['nombre'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Prioridad:</h6>
                                            <?php if (isset($prioridad['color'])): ?>
                                                <span class="badge" style="background-color: <?= $prioridad['color'] ?>">
                                                    <?= htmlspecialchars($prioridad['nombre'] ?? 'N/A') ?>
                                                </span>
                                            <?php else: ?>
                                                <p><?= htmlspecialchars($prioridad['nombre'] ?? 'N/A') ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Fecha de creación:</h6>
                                            <p><?= htmlspecialchars($ticket['fecha_creacion'] ?? 'N/A') ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Última actualización:</h6>
                                            <p><?= htmlspecialchars($ticket['fecha_actualizacion'] ?? 'N/A') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Comentarios y Archivos -->
                            <div class="tab-pane fade" id="comentarios" role="tabpanel" aria-labelledby="comentarios-tab">
                                <div class="mb-4">
                                    <h5 class="mb-3">Comentarios del ticket</h5>
                                    <div id="comentarios-lista" class="mb-4">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-2">Cargando comentarios...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Agregar nuevo comentario</h5>
                                        <?php if ($esSesionTemporal): ?>
                                            <small class="text-muted">Solo el creador del ticket puede agregar comentarios</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" id="formAgregarComentario" enctype="multipart/form-data">
                                            <input type="hidden" name="accion" value="agregarComentario">
                                            <input type="hidden" name="ticketId" value="<?= htmlspecialchars($ticketId) ?>">
                                            <input type="hidden" name="usuarioId" value="<?= htmlspecialchars($usuarioId) ?>">

                                            <?php if ($esSesionTemporal): ?>
                                                <div class="mb-3">
                                                    <label for="emailValidacion" class="form-label">
                                                        Email de validación <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" class="form-control" id="emailValidacion" name="emailValidacion" placeholder="Confirme su email para poder comentar" required>
                                                    <div class="form-text">
                                                        <i class="bx bx-info-circle"></i> Debe usar el mismo email con el que creó el ticket
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <label for="contenidoComentario" class="form-label">Comentario <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="contenidoComentario" name="contenidoComentario" rows="3" required placeholder="Escriba su comentario aquí..."></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label for="archivoComentario" class="form-label">Adjuntar archivo (opcional)</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" id="archivoComentario" name="archivoComentario" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                                    <button class="btn btn-outline-secondary" type="button" id="clearFileBtn">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">
                                                    <span><i class="bx bx-info-circle"></i> Formatos permitidos: PDF, Word, Excel, imágenes (.jpg, .png, .gif)</span><br>
                                                    <span><i class="bx bx-data"></i> Tamaño máximo: 5MB</span>
                                                </div>
                                                <div id="filePreview" class="mt-2 d-none">
                                                    <div class="card bg-light p-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file fs-3 me-2"></i>
                                                            <span id="fileName"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (!$esSesionTemporal): ?>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="esPrivado" name="esPrivado">
                                                    <label class="form-check-label" for="esPrivado">
                                                        Comentario privado <small>(solo visible para técnicos y administradores)</small>
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <h6 class="alert-heading mb-2">
                                                        <i class="bx bx-info-circle"></i> Información importante
                                                    </h6>
                                                    <ul class="mb-0 small">
                                                        <li>Su comentario será público y visible para el equipo de soporte</li>
                                                        <li>Recibirá notificaciones de respuestas en su email</li>
                                                        <li>Para mejor gestión, considere registrarse en el sistema</li>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>

                                            <div class="d-grid gap-2 d-md-flex">
                                                <button type="submit" class="btn btn-primary" id="btnEnviarComentario">
                                                    <i class="bx bx-send me-1"></i>Agregar Comentario
                                                </button>
                                                
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- /.tab-content -->
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            </div> <!-- /.col-12 -->
        </div> <!-- /.row -->
    </div> <!-- /.container-xxl -->
</section>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketId = <?= json_encode($ticketId) ?>;
    const usuarioId = <?= json_encode($usuarioId) ?>;
    const esSesionTemporal = <?= json_encode($esSesionTemporal) ?>;
    const emailCreador = <?= json_encode($creador['email'] ?? '') ?>;
    const fileInput = document.getElementById('archivoComentario');
    const clearBtn = document.getElementById('clearFileBtn');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const formComentario = document.getElementById('formAgregarComentario');
    const btnEnviar = document.getElementById('btnEnviarComentario');
    
    const comentariosTab = document.getElementById('comentarios-tab');
    comentariosTab.addEventListener('shown.bs.tab', function() {
        cargarComentarios();
    });
    
    fileInput.addEventListener('change', function(e) {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El archivo no puede exceder 5MB'
                });
                fileInput.value = '';
                return;
            }
            
            fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });
    
    clearBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
    });
    
    formComentario.addEventListener('submit', function(e) {
        e.preventDefault();
        enviarComentario();
    });
    
    function cargarComentarios() {
        const listaComentarios = document.getElementById('comentarios-lista');
        
        fetch('ajax/comentarios-publicos.ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=obtenerComentarios&ticketId=${ticketId}&usuarioId=${usuarioId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                mostrarComentarios(data.comentarios);
            } else {
                listaComentarios.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle"></i> ${data.mensaje || 'Error al cargar comentarios'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            listaComentarios.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle"></i> Error de conexión al cargar comentarios
                </div>
            `;
        });
    }
    
    function timeAgo(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const diffInSeconds = Math.floor((now - past) / 1000);
        
        if (diffInSeconds < 60) {
            return 'hace un momento';
        }
        
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `hace ${diffInMinutes} minuto${diffInMinutes > 1 ? 's' : ''}`;
        }
        
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `hace ${diffInHours} hora${diffInHours > 1 ? 's' : ''}`;
        }
        
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 7) {
            return `hace ${diffInDays} día${diffInDays > 1 ? 's' : ''}`;
        }
        
        const diffInWeeks = Math.floor(diffInDays / 7);
        if (diffInWeeks < 4) {
            return `hace ${diffInWeeks} semana${diffInWeeks > 1 ? 's' : ''}`;
        }
        
        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `hace ${diffInMonths} mes${diffInMonths > 1 ? 'es' : ''}`;
        }
        
        const diffInYears = Math.floor(diffInDays / 365);
        return `hace ${diffInYears} año${diffInYears > 1 ? 's' : ''}`;
    }
    
    function mostrarComentarios(comentarios) {
        const listaComentarios = document.getElementById('comentarios-lista');
        
        if (comentarios.length === 0) {
            listaComentarios.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bx bx-comment fs-1"></i>
                    <p>No hay comentarios aún</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        comentarios.forEach(comentario => {
            const fechaFormateada = timeAgo(comentario.fecha_creacion);
            const esPrivado = comentario.es_privado == 1;
            
            html += `
                <div class="card mb-3 ${esPrivado ? 'border-warning' : ''}">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <img src="${comentario.usuario.foto_perfil}" alt="Avatar" 
                                 class="rounded-circle me-3" width="40" height="40"
                                 onerror="this.src='vistas/assets/img/avatars/default.jpg'">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0">${comentario.usuario.nombre} ${comentario.usuario.apellido}</h6>
                                        <small class="text-muted">${fechaFormateada}</small>
                                        ${esPrivado ? '<span class="badge bg-warning ms-2">Privado</span>' : ''}
                                    </div>
                                </div>
                                <p class="mb-2">${comentario.contenido.replace(/\n/g, '<br>')}</p>
                                ${mostrarArchivos(comentario.archivos)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        listaComentarios.innerHTML = html;
    }
    
    function mostrarArchivos(archivos) {
        if (!archivos || archivos.length === 0) {
            return '';
        }
        
        let html = '<div class="mt-2"><h6 class="small mb-2">Archivos adjuntos:</h6>';
        archivos.forEach(archivo => {
            const icono = getIconoArchivo(archivo.tipo);
            const tamano = formatFileSize(archivo.tamano);
            
            html += `
                <div class="d-flex align-items-center mb-1">
                    <i class="${icono} me-2"></i>
                    <a href="/helpdesk/${archivo.ruta}" target="_blank" class="text-decoration-none me-2">
                        ${archivo.nombre}
                    </a>
                    <small class="text-muted">(${tamano})</small>
                </div>
            `;
        });
        html += '</div>';
        
        return html;
    }
    
    function enviarComentario() {
        const formData = new FormData(formComentario);
        
        if (esSesionTemporal) {
            const emailValidacion = document.getElementById('emailValidacion').value;
            if (!emailValidacion) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email requerido',
                    text: 'Debe ingresar su email para validar que puede comentar en este ticket'
                });
                return;
            }
            
            if (emailValidacion !== emailCreador) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email incorrecto',
                    text: 'El email ingresado no coincide con el del creador del ticket'
                });
                return;
            }
        }
        
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';
        
        fetch('ajax/comentarios-publicos.ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Comentario agregado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                formComentario.reset();
                filePreview.classList.add('d-none');
                
                cargarComentarios();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje || 'Error al agregar comentario'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión'
            });
        })
        .finally(() => {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = '<i class="bx bx-send me-1"></i>Agregar Comentario';
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + " bytes";
        else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + " KB";
        else return (bytes / 1048576).toFixed(2) + " MB";
    }
    
    function getIconoArchivo(tipo) {
        if (tipo.includes('pdf')) return 'bx bxs-file-pdf text-danger';
        if (tipo.includes('word') || tipo.includes('document')) return 'bx bxs-file-doc text-primary';
        if (tipo.includes('excel') || tipo.includes('sheet')) return 'bx bxs-file text-success';
        if (tipo.includes('image')) return 'bx bxs-image text-info';
        return 'bx bx-file';
    }
});
</script>
