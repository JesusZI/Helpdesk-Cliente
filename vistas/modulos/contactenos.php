<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST recibido en contactenos.php: " . print_r($_POST, true));
}

$conn = new mysqli("localhost", "root", "", "helpdesk");
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

$categorias = $conn->query("SELECT c.id, c.nombre, c.departamento_id, d.nombre as departamento_nombre FROM categorias c LEFT JOIN departamentos d ON c.departamento_id = d.id ORDER BY c.nombre");
if (!$categorias) {
    error_log("Error al obtener categorías: " . $conn->error);
    $categorias = [];
} else {
    $categorias = $categorias->fetch_all(MYSQLI_ASSOC);
}

$prioridades = $conn->query("SELECT id, nombre, color FROM prioridades ORDER BY id ASC");
if (!$prioridades) {
    error_log("Error al obtener prioridades: " . $conn->error);
    $prioridades = [];
} else {
    $prioridades = $prioridades->fetch_all(MYSQLI_ASSOC);
}

$departamentos = $conn->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
if (!$departamentos) {
    error_log("Error al obtener departamentos: " . $conn->error);
    $departamentos = [];
} else {
    $departamentos = $departamentos->fetch_all(MYSQLI_ASSOC);
}

$mensaje = '';
$tipoMensaje = '';
$ticketId = '';

if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['ticket_id']) && isset($_GET['mensaje'])) {
    $ticketId = intval($_GET['ticket_id']);
    $email_redirect = isset($_GET['email']) ? urldecode($_GET['email']) : '';
    $mensaje = str_replace("#$ticketId", "<strong>#$ticketId</strong>", urldecode($_GET['mensaje']));
    if (!empty($email_redirect)) {
        $mensaje = str_replace("$email_redirect", "<strong>$email_redirect</strong>", $mensaje);
    }
    $tipoMensaje = 'success';
}

if (isset($_GET['error']) && $_GET['error'] == '1' && isset($_GET['mensaje'])) {
    $mensaje = str_replace('. ', '<br>', urldecode($_GET['mensaje']));
    $tipoMensaje = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {
    error_log("Procesando creación de ticket...");
    
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $categoria_id = intval($_POST['categoria_id']);
    $prioridad_id = intval($_POST['prioridad_id']);
    $departamento_id = intval($_POST['departamento_id']);
    
    error_log("Datos del formulario - Título: $titulo, Email: $email, Categoría: $categoria_id");
    
    $errores = [];
    
    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio.';
    } elseif (strlen($titulo) < 5) {
        $errores[] = 'El título debe tener al menos 5 caracteres.';
    } elseif (strlen($titulo) > 200) {
        $errores[] = 'El título no puede exceder 200 caracteres.';
    }
    
    if (empty($descripcion)) {
        $errores[] = 'La descripción es obligatoria.';
    } elseif (strlen($descripcion) < 10) {
        $errores[] = 'La descripción debe tener al menos 10 caracteres.';
    } elseif (strlen($descripcion) > 1000) {
        $errores[] = 'La descripción no puede exceder 1000 caracteres.';
    }
    
    if (empty($nombre_completo)) {
        $errores[] = 'El nombre completo es obligatorio.';
    } elseif (strlen($nombre_completo) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre_completo)) {
        $errores[] = 'El nombre solo debe contener letras y espacios.';
    }
    
    if (empty($email)) {
        $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no tiene un formato válido.';
    }
    
    if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
    }
    
    if ($categoria_id <= 0) {
        $errores[] = 'Debe seleccionar una categoría.';
    }
    
    if ($prioridad_id <= 0) {
        $errores[] = 'Debe seleccionar una prioridad.';
    }
    
    if ($departamento_id <= 0) {
        $errores[] = 'Debe seleccionar un departamento.';
    }
    
    if (empty($errores)) {
        $validCategoria = $conn->query("SELECT id FROM categorias WHERE id = $categoria_id");
        $validPrioridad = $conn->query("SELECT id FROM prioridades WHERE id = $prioridad_id");
        $validDepartamento = $conn->query("SELECT id FROM departamentos WHERE id = $departamento_id");
        
        if ($validCategoria->num_rows === 0) {
            $errores[] = 'La categoría seleccionada no es válida.';
        }
        if ($validPrioridad->num_rows === 0) {
            $errores[] = 'La prioridad seleccionada no es válida.';
        }
        if ($validDepartamento->num_rows === 0) {
            $errores[] = 'El departamento seleccionado no es válido.';
        }
    }
    
    if (!empty($errores)) {
        $mensaje = implode('<br>', $errores);
        $tipoMensaje = 'error';
    } else {
        $conn->begin_transaction();
        
        try {
            $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtUser->bind_param("s", $email);
            $stmtUser->execute();
            $userResult = $stmtUser->get_result();
            
            if ($userResult->num_rows > 0) {
                $usuario = $userResult->fetch_assoc();
                $usuario_id = $usuario['id'];
                
                $stmtUpdate = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, fecha_actualizacion = NOW() WHERE id = ?");
                $stmtUpdate->bind_param("ssi", $nombre_completo, $telefono, $usuario_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                $stmtNewUser = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, perfil, estado, fecha_creacion, fecha_actualizacion) VALUES (?, ?, ?, 'cliente', 'activo', NOW(), NOW())");
                $stmtNewUser->bind_param("sss", $nombre_completo, $email, $telefono);
                
                if (!$stmtNewUser->execute()) {
                    throw new Exception('Error al crear el usuario');
                }
                
                $usuario_id = $conn->insert_id;
                $stmtNewUser->close();
            }
            $stmtUser->close();
            
            $tecnico_id = null;
            
            $stmtTecnicoDept = $conn->prepare("SELECT id FROM usuarios WHERE perfil = 'tecnico' AND departamento_id = ? AND estado = 'activo' ORDER BY RAND() LIMIT 1");
            $stmtTecnicoDept->bind_param("i", $departamento_id);
            $stmtTecnicoDept->execute();
            $tecnicoDeptResult = $stmtTecnicoDept->get_result();
            
            if ($tecnicoDeptResult->num_rows > 0) {
                $tecnico_id = $tecnicoDeptResult->fetch_assoc()['id'];
                error_log("Técnico asignado del departamento $departamento_id: $tecnico_id");
            } else {
                $stmtAdmin = $conn->prepare("SELECT id FROM usuarios WHERE perfil = 'admin' AND estado = 'activo' ORDER BY RAND() LIMIT 1");
                $stmtAdmin->execute();
                $adminResult = $stmtAdmin->get_result();
                
                if ($adminResult->num_rows > 0) {
                    $tecnico_id = $adminResult->fetch_assoc()['id'];
                    error_log("Admin asignado (no hay técnicos en departamento $departamento_id): $tecnico_id");
                } else {
                    $tecnico_id = 1;
                    error_log("Técnico por defecto asignado: $tecnico_id");
                }
                $stmtAdmin->close();
            }
            $stmtTecnicoDept->close();
            
            $stmtTicket = $conn->prepare("INSERT INTO tickets (titulo, descripcion, usuario_creador_id, tecnico_asignado_id, categoria_id, prioridad_id, estado, fecha_creacion, fecha_actualizacion, departamento_origen_id, departamento_asignado_id) VALUES (?, ?, ?, ?, ?, ?, 'abierto', NOW(), NOW(), ?, ?)");
            $stmtTicket->bind_param("ssiiiiii", $titulo, $descripcion, $usuario_id, $tecnico_id, $categoria_id, $prioridad_id, $departamento_id, $departamento_id);
            
            if (!$stmtTicket->execute()) {
                throw new Exception('Error al crear el ticket');
            }
            
            $ticketId = $conn->insert_id;
            $stmtTicket->close();
            
            $conn->commit();
            
            require_once "config/email.config.php";
            require_once "extensiones/PHPMailer/PHPMailerAutoload.php";
            
            date_default_timezone_set("America/Caracas");
            
            $stmtTecnicoEmail = $conn->prepare("SELECT u.email, u.nombre, u.apellido, u.perfil, u.departamento_id, d.nombre as departamento_nombre FROM usuarios u LEFT JOIN departamentos d ON u.departamento_id = d.id WHERE u.id = ?");
            $stmtTecnicoEmail->bind_param("i", $tecnico_id);
            $stmtTecnicoEmail->execute();
            $tecnicoData = $stmtTecnicoEmail->get_result()->fetch_assoc();
            $stmtTecnicoEmail->close();
            
            if ($tecnicoData) {
                error_log("Técnico asignado: " . $tecnicoData['nombre'] . " " . $tecnicoData['apellido'] . " (Perfil: " . $tecnicoData['perfil'] . ", Departamento: " . ($tecnicoData['departamento_nombre'] ?? 'Sin departamento') . ")");
            }
            
            $stmtDatos = $conn->prepare("
                SELECT c.nombre as categoria_nombre, p.nombre as prioridad_nombre, d.nombre as departamento_nombre 
                FROM categorias c, prioridades p, departamentos d 
                WHERE c.id = ? AND p.id = ? AND d.id = ?
            ");
            $stmtDatos->bind_param("iii", $categoria_id, $prioridad_id, $departamento_id);
            $stmtDatos->execute();
            $datosTicket = $stmtDatos->get_result()->fetch_assoc();
            $stmtDatos->close();
            
            $mensajeCliente = crear_mensaje_cliente($ticketId, $titulo, $descripcion, $nombre_completo, $datosTicket);
            $mensajeTecnico = crear_mensaje_tecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData);
            
            $mail = new PHPMailer(true);
            configurarPHPMailer($mail);
            
            $asuntoCliente = "Ticket creado exitosamente #$ticketId";
            $resultadoCliente = enviarEmail($email, $nombre_completo, $asuntoCliente, $mensajeCliente, true);
            
            if ($tecnicoData) {
                $tecnicoNombreCompleto = trim($tecnicoData['nombre'] . ' ' . $tecnicoData['apellido']);
                $asuntoTecnico = "Nuevo ticket asignado #$ticketId";
                $resultadoTecnico = enviarEmail($tecnicoData['email'], $tecnicoNombreCompleto, $asuntoTecnico, $mensajeTecnico, true);
            }
            
            if ($resultadoCliente['success']) {
                $mensaje = "¡Ticket creado exitosamente! Su número de ticket es: <strong>#$ticketId</strong>.<br>Se ha enviado un email de confirmación a: <strong>$email</strong>";
                if (isset($resultadoTecnico) && $resultadoTecnico['success']) {
                    $mensaje .= "<br>También se ha notificado al técnico asignado.";
                }
            } else {
                $mensaje = "¡Ticket creado exitosamente! Su número de ticket es: <strong>#$ticketId</strong>.<br><span class='text-warning'>Nota: No se pudo enviar el email de confirmación.</span>";
            }
            $tipoMensaje = 'success';
            
            $_POST = array();
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = 'Ocurrió un error al procesar su solicitud. Por favor, intente nuevamente.';
            $tipoMensaje = 'error';
            
            error_log("Error al crear ticket: " . $e->getMessage());
        }
    }
}

$conn->close();
?>
<section class="section-py first-section-pt help-center-header position-relative overflow-hidden">
  <img class="banner-bg-img z-n1" src="vistas/assets/img/pages/header.png" alt="Help center header" />
  <h4 class="text-center text-primary">Hola, ¿cómo podemos ayudarte?</h4>
  <p class="text-center px-4 mb-0">¿Tienes alguna duda o comentario? Completa el formulario y nuestro equipo te asistirá.</p>
</section>

<section id="landingContact" class="section-py bg-body landing-contact">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-label-primary">Contáctanos</span>
        </div>
        <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">Trabajemos
                <img src="vistas/assets/img/front-pages/icons/section-title-icon.png"
                     alt="laptop charging"
                     class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            juntos
        </h4>
        <p class="text-center mb-12 pb-md-4">¿Alguna pregunta o comentario? Crea un ticket y nuestro equipo te ayudará</p>
        <?php if (!empty($mensaje)): ?>
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8">
                <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'danger'; ?> alert-dismissible" role="alert">
                    <h6 class="alert-heading mb-1">
                        <i class="icon-base bx bx-<?php echo $tipoMensaje === 'success' ? 'check-circle' : 'error-circle'; ?> me-2"></i>
                        <?php echo $tipoMensaje === 'success' ? '¡Ticket creado exitosamente!' : 'Error al crear el ticket'; ?>
                    </h6>
                    <span><?php echo $mensaje; ?></span>
                    <?php if ($tipoMensaje === 'success' && !empty($ticketId)): ?>
                    <div class="mt-3 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Número de ticket:</small>
                                <strong class="text-success">#<?php echo $ticketId; ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Estado:</small>
                                <span class="badge bg-label-info">Abierto</span>
                            </div>                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="icon-base bx bx-info-circle me-1"></i>
                                    Recibirá una confirmación por email y actualizaciones sobre el progreso de su ticket.
                                </small>
                            </div>
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="index.php?ruta=consultar-ticket&idTicket=<?php echo $ticketId; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="icon-base bx bx-show me-1"></i>Ver Mi Ticket
                                    </a>
                                    <a href="index.php?ruta=consultar-ticket" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="icon-base bx bx-search me-1"></i>Consultar Otro Ticket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row g-6">
            <div class="col-lg-5">
                <div class="contact-img-box position-relative border p-2 h-100">
                    <img src="vistas/assets/img/front-pages/icons/contact-border.png"
                         alt="contact border"
                         class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
                    <img src="vistas/assets/img/front-pages/landing-page/contact-customer-service.png"
                         alt="contact customer service"
                         class="contact-img w-100 scaleX-n1-rtl" />
                    <div class="p-4 pb-2">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-12 col-xl-12">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-primary rounded p-1_5 me-3">
                                        <i class="icon-base bx bx-envelope icon-lg"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">Email</p>
                                        <h6 class="mb-0">
                                            <a href="mailto:soporte@helpdesk.com" class="text-heading">soporte@helpdesk.com</a>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-12 col-xl-12">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-success rounded p-1_5 me-3">
                                        <i class="icon-base bx bx-phone-call icon-lg"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">Teléfono</p>
                                        <h6 class="mb-0"><a  class="text-heading">+58 412 123 4567</a></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="mt-4 pt-4 border-top">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-label-info rounded p-1_5 me-3">
                                            <i class="icon-base bx bx-time icon-lg"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Horario de atención</p>
                                            <h6 class="mb-0 text-heading">Lunes a Viernes 8:00 AM - 6:00 PM</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-label-warning rounded p-1_5 me-3">
                                            <i class="icon-base bx bx-support icon-lg"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">Tiempo de respuesta</p>
                                            <h6 class="mb-0 text-heading">Máximo 24 horas</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-2">Crear un nuevo ticket</h4>
                        <p class="mb-6">
                            Complete el siguiente formulario y nuestro equipo de soporte<br class="d-none d-lg-block" />
                            se pondrá en contacto con usted lo antes posible.
                        </p>
                        
                        <form method="POST" id="ticketForm">
                            <div class="row g-4">
                                <!-- Información personal -->
                                <div class="col-md-6">
                                    <label class="form-label" for="nombre_completo">Nombre completo <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nombre_completo" 
                                           name="nombre_completo" 
                                           placeholder="Juan Pérez"
                                           value="<?php echo isset($_POST['nombre_completo']) ? htmlspecialchars($_POST['nombre_completo']) : ''; ?>"
                                           required />
                                    <div class="form-text">
                                        <small class="text-muted">Solo letras y espacios</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control"
                                           placeholder="juan@ejemplo.com"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required />
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label" for="telefono">Teléfono</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefono" 
                                           name="telefono" 
                                           placeholder="1234567890 (10 dígitos)"
                                           maxlength="10"
                                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>" />
                                    <div class="form-text">
                                        <small class="text-muted">Solo números, exactamente 10 dígitos</small>
                                    </div>
                                </div>
                                
                                <!-- Información del ticket -->
                                <div class="col-md-6">
                                    <label class="form-label" for="categoria_id">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                                        <option value="">Seleccione una categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>" 
                                                data-departamento-id="<?php echo $categoria['departamento_id'] ?? ''; ?>"
                                                data-departamento-nombre="<?php echo htmlspecialchars($categoria['departamento_nombre'] ?? ''); ?>"
                                                <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                            <?php 
                                            echo htmlspecialchars($categoria['nombre']);
                                            if (!empty($categoria['departamento_nombre'])) {
                                                echo ' → ' . htmlspecialchars($categoria['departamento_nombre']);
                                            }
                                            ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <small class="text-muted"><i class="bx bx-info-circle me-1"></i>Algunas categorías tienen un departamento específico asignado</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label" for="prioridad_id">Prioridad <span class="text-danger">*</span></label>
                                    <select class="form-select" id="prioridad_id" name="prioridad_id" required>
                                        <option value="">Seleccione la prioridad</option>
                                        <?php foreach ($prioridades as $prioridad): ?>
                                        <option value="<?php echo $prioridad['id']; ?>" 
                                                data-color="<?php echo $prioridad['color']; ?>"
                                                <?php echo (isset($_POST['prioridad_id']) && $_POST['prioridad_id'] == $prioridad['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prioridad['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label" for="departamento_id">Departamento <span class="text-danger">*</span></label>
                                    <select class="form-select" id="departamento_id" name="departamento_id" required disabled>
                                        <option value="">Seleccione primero una categoría</option>
                                        <?php foreach ($departamentos as $departamento): ?>
                                        <option value="<?php echo $departamento['id']; ?>"
                                                <?php echo (isset($_POST['departamento_id']) && $_POST['departamento_id'] == $departamento['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($departamento['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label" for="titulo">Título del ticket <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="titulo" 
                                           name="titulo" 
                                           placeholder="Resuma brevemente su consulta o problema"
                                           value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>"
                                           required />
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label" for="descripcion">Descripción del problema <span class="text-danger">*</span></label>
                                    <textarea id="descripcion" 
                                              name="descripcion" 
                                              class="form-control"
                                              rows="6"
                                              placeholder="Describa detalladamente su consulta o problema. Incluya cualquier información relevante que pueda ayudarnos a resolver su caso más rápidamente."
                                              required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                                    <div class="form-text">
                                        <i class="icon-base bx bx-info-circle me-1"></i>
                                        Sea lo más específico posible para obtener una respuesta más rápida y precisa.
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="acepto_terminos" required>
                                        <label class="form-check-label" for="acepto_terminos">
                                            Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la 
                                            <a href="#" class="text-primary">política de privacidad</a> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" name="crear_ticket" class="btn btn-primary me-3">
                                        <i class="icon-base bx bx-paper-plane me-2"></i>
                                        Crear ticket
                                    </button>
                                    <button type="reset" class="btn btn-label-secondary">
                                        <i class="icon-base bx bx-refresh me-2"></i>
                                        Limpiar formulario
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
          <!-- Información adicional -->
        <div class="row mt-12">
            <div class="col-md-6 mb-6">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <h5 class="text-white mb-3">
                            <i class="icon-base bx bx-help-circle me-2"></i>
                            ¿Necesita ayuda inmediata?
                        </h5>
                        <p class="text-white mb-4">
                            Consulte nuestro centro de ayuda para encontrar respuestas a las preguntas más frecuentes
                        </p>
                        <a href="index.php?ruta=faq" class="btn btn-white">
                            <i class="icon-base bx bx-book-open me-2"></i>
                            Ir al Centro de Ayuda
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-6">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body text-center">
                        <h5 class="text-white mb-3">
                            <i class="icon-base bx bx-search-alt me-2"></i>
                            ¿Ya tiene un ticket?
                        </h5>
                        <p class="text-white mb-4">
                            Consulte el estado de su ticket existente ingresando el número de ticket
                        </p>
                        <a href="index.php?ruta=consultar-ticket" class="btn btn-white">
                            <i class="icon-base bx bx-file-find me-2"></i>
                            Consultar Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.search.includes('success=1') || window.location.search.includes('error=1')) {
        console.log('Detectado mensaje de éxito/error en URL');
        console.log('URL actual:', window.location.href);
        
        setTimeout(function() {
            if (history.replaceState) {
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?ruta=contactenos";
                console.log('Limpiando URL a:', newUrl);
                history.replaceState(null, null, newUrl);
                console.log('URL después de limpiar:', window.location.href);
            }
        }, 5000); 
    }
    
    window.addEventListener('popstate', function(event) {
        console.log('Evento popstate detectado');
        console.log('Estado:', event.state);
        console.log('URL actual:', window.location.href);
        
        if (!window.location.search.includes('ruta=contactenos') && 
            !window.location.pathname.includes('contactenos')) {
            console.log('Redirigiendo de vuelta a contactenos');
            window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + "?ruta=contactenos";
        }
    });
    
    window.addEventListener('beforeunload', function(event) {
        console.log('Intentando salir de la página:', window.location.href);
    });
    
    const form = document.getElementById('ticketForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    console.log('Estado actual de la página:');
    console.log('- URL completa:', window.location.href);
    console.log('- Pathname:', window.location.pathname);
    console.log('- Search params:', window.location.search);
    console.log('- Tiene mensaje de éxito:', window.location.search.includes('success=1'));
    console.log('- Tiene mensaje de error:', window.location.search.includes('error=1'));
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        });
    });
    
    function validateField(field) {
        let isValid = true;
        let message = '';
        
        const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        field.classList.remove('is-invalid');
        
        switch(field.type) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !emailRegex.test(field.value)) {
                    isValid = false;
                    message = 'Por favor ingrese un email válido';
                }
                break;
                
            case 'tel':
                if (field.value && !/^[0-9]{10}$/.test(field.value)) {
                    isValid = false;
                    message = 'El teléfono debe tener exactamente 10 dígitos numéricos';
                }
                break;
                
            case 'text':
                if (field.name === 'nombre_completo') {
                    if (field.value && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(field.value)) {
                        isValid = false;
                        message = 'El nombre solo debe contener letras y espacios';
                    }
                }
                break;
        }
        
        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            message = 'Este campo es obligatorio';
        }
        
        if (!isValid) {
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            field.parentNode.appendChild(feedback);
        }
        
        return isValid;
    }
    
    form.addEventListener('submit', function(e) {
        let isFormValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        
        const checkbox = document.getElementById('acepto_terminos');
        if (!checkbox.checked) {
            isFormValid = false;
            checkbox.classList.add('is-invalid');
            
            const existingFeedback = checkbox.parentNode.querySelector('.invalid-feedback');
            if (!existingFeedback) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Debe aceptar los términos y condiciones';
                checkbox.parentNode.appendChild(feedback);
            }
        }
        
        if (!isFormValid) {
            e.preventDefault();
            
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    const descripcionTextarea = document.getElementById('descripcion');
    const charCounter = document.createElement('div');
    charCounter.className = 'form-text text-end mt-1';
    charCounter.innerHTML = '<span id="charCount">0</span>/1000 caracteres';
    descripcionTextarea.parentNode.appendChild(charCounter);
    
    descripcionTextarea.addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('charCount').textContent = count;
        
        if (count > 1000) {
            this.value = this.value.substring(0, 1000);
            document.getElementById('charCount').textContent = '1000';
        }
        
        const counter = document.getElementById('charCount');
        if (count > 900) {
            counter.style.color = '#ff4757';
        } else if (count > 700) {
            counter.style.color = '#ffa726';
        } else {
            counter.style.color = '#6c757d';
        }
    });
    
    const categoriaSelect = document.getElementById('categoria_id');
    const departamentoSelect = document.getElementById('departamento_id');
    
    categoriaSelect.addEventListener('change', function() {
        const categoriaId = this.value;
        
        if (categoriaId) {
            departamentoSelect.innerHTML = '<option value="">Cargando departamentos...</option>';
            departamentoSelect.disabled = true;
            
            const formData = new FormData();
            formData.append('categoria_id', categoriaId);
            
            fetch('ajax/departamentos-categoria.ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
                
                if (data.success && data.departamentos) {
                    data.departamentos.forEach(function(departamento) {
                        const option = document.createElement('option');
                        option.value = departamento.id;
                        option.textContent = departamento.nombre;
                        departamentoSelect.appendChild(option);
                    });
                    
                    if (data.departamentos.length === 1) {
                        departamentoSelect.value = data.departamentos[0].id;
                        departamentoSelect.dispatchEvent(new Event('change'));
                    }
                } else {
                    departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
                }
                
                departamentoSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
                departamentoSelect.disabled = false;
            });
        } else {
            departamentoSelect.innerHTML = '<option value="">Seleccione primero una categoría</option>';
            departamentoSelect.disabled = true;
        }
    });
    
    if (!categoriaSelect.value) {
        departamentoSelect.innerHTML = '<option value="">Seleccione primero una categoría</option>';
        departamentoSelect.disabled = true;
    } else {
       
        categoriaSelect.dispatchEvent(new Event('change'));
    }
    
    document.getElementById('telefono').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
        }
    });
    
    document.getElementById('nombre_completo').addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
});
</script>

<?php
function crear_mensaje_cliente($ticketId, $titulo, $descripcion, $nombre_completo, $datosTicket) {
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#007bff; color:white; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">¡Ticket Creado Exitosamente!</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Estimado/a <strong>'.$nombre_completo.'</strong>,</p>
                
                <p style="color:#666; line-height:1.6">Su ticket ha sido creado exitosamente en nuestro sistema de soporte. A continuación encontrará los detalles:</p>
                
                <div style="background:#f8f9fa; padding:20px; border-radius:6px; margin:20px 0">
                    <table style="width:100%; border-collapse:collapse">
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Número de Ticket:</td>
                            <td style="padding:8px 0; color:#007bff; font-weight:bold">#'.$ticketId.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Título:</td>
                            <td style="padding:8px 0; color:#333">'.htmlspecialchars($titulo).'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Categoría:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['categoria_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Prioridad:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['prioridad_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Departamento:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['departamento_nombre'].'</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background:#e8f4fd; border-left:4px solid #007bff; padding:15px; margin:20px 0">
                    <h4 style="color:#0056b3; margin:0 0 10px 0; font-size:16px">📋 Descripción del problema:</h4>
                    <p style="color:#0056b3; margin:0; line-height:1.6">'.nl2br(htmlspecialchars($descripcion)).'</p>
                </div>
                
                <p style="color:#666; line-height:1.6">Nuestro equipo de soporte revisará su solicitud y se pondrá en contacto con usted lo antes posible.</p>
                
                <div style="text-align:center; margin:30px 0">
                    <p style="color:#999; font-size:14px; margin:0">
                        📧 Este es un mensaje automático, por favor no responder a este correo.
                    </p>
                </div>
            </div>
        </div>
    </div>';
}

function crear_mensaje_tecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData) {
    $tecnicoNombreCompleto = trim($tecnicoData['nombre'] . ' ' . $tecnicoData['apellido']);
    $perfilTecnico = ucfirst($tecnicoData['perfil']);
    $departamentoTecnico = $tecnicoData['departamento_nombre'] ?? 'Sin departamento específico';
    
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#dc3545; color:white; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">🎫 Nuevo Ticket Asignado</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Hola <strong>'.$tecnicoNombreCompleto.'</strong> ('.$perfilTecnico.'),</p>
                
                <p style="color:#666; line-height:1.6">Se te ha asignado un nuevo ticket de soporte para el departamento <strong>'.$datosTicket['departamento_nombre'].'</strong>. Por favor revisa los detalles y procede con la atención correspondiente:</p>
                
                <div style="background:#f8f9fa; padding:20px; border-radius:6px; margin:20px 0">
                    <table style="width:100%; border-collapse:collapse">
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Ticket:</td>
                            <td style="padding:8px 0; color:#dc3545; font-weight:bold">#'.$ticketId.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Título:</td>
                            <td style="padding:8px 0; color:#333">'.htmlspecialchars($titulo).'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Cliente:</td>
                            <td style="padding:8px 0; color:#333">'.$nombre_completo.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Email:</td>
                            <td style="padding:8px 0; color:#333">'.$email.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Teléfono:</td>
                            <td style="padding:8px 0; color:#333">'.($telefono ?: 'No proporcionado').'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Categoría:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['categoria_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Prioridad:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['prioridad_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Departamento:</td>
                            <td style="padding:8px 0; color:#333">'.$datosTicket['departamento_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Tu Departamento:</td>
                            <td style="padding:8px 0; color:#333">'.$departamentoTecnico.'</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:20px; margin:20px 0; border-radius:0 6px 6px 0">
                    <h4 style="color:#856404; margin:0 0 10px 0; font-size:16px">📝 Descripción del problema:</h4>
                    <p style="color:#856404; margin:0; line-height:1.6; white-space:pre-wrap">'.htmlspecialchars($descripcion).'</p>
                </div>
                
                <div style="background:#d1ecf1; border-left:4px solid #bee5eb; padding:15px; margin:20px 0; border-radius:0 6px 6px 0">
                    <p style="color:#0c5460; margin:0; font-size:14px">
                        ⚡ <strong>Acción requerida:</strong> Por favor revise este ticket y contacte al cliente lo antes posible.
                        '.($tecnicoData['perfil'] === 'admin' ? '<br><strong>Nota:</strong> Has sido asignado como administrador debido a que no hay técnicos disponibles en este departamento.' : '').'
                    </p>
                </div>
                
                <div style="text-align:center; margin:30px 0">
                    <p style="color:#999; font-size:14px; margin:0">
                        📧 Notificación automática del sistema de tickets
                    </p>
                </div>
            </div>
        </div>
    </div>';
}
?>
