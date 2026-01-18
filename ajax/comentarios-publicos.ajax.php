<?php
require_once "../controladores/tickets.controlador.php";
require_once "../controladores/usuarios.controlador.php";
require_once "../controladores/comentarios.controlador.php";
require_once "../extensiones/PHPMailer/PHPMailerAutoload.php";

session_start();

header('Content-Type: application/json');

if (!isset($_POST['ticketId']) && !isset($_POST['accion'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'helpdesk';
$dbPort = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306;

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error de conexión']);
    exit;
}

if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerComentarios') {
    $ticketId = intval($_POST['ticketId']);
    $usuarioId = intval($_POST['usuarioId']);
    
    try {
        $stmt = $conn->prepare("
            SELECT c.*, u.nombre, u.apellido, u.foto_perfil, u.email
            FROM comentarios c 
            INNER JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.ticket_id = ? AND (c.es_privado = 0 OR c.usuario_id = ?)
            ORDER BY c.fecha_creacion ASC
        ");
        $stmt->bind_param("ii", $ticketId, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            $stmtArchivos = $conn->prepare("SELECT * FROM archivos WHERE comentario_id = ?");
            $stmtArchivos->bind_param("i", $row['id']);
            $stmtArchivos->execute();
            $archivosResult = $stmtArchivos->get_result();
            
            $archivos = [];
            while ($archivo = $archivosResult->fetch_assoc()) {
                $archivos[] = [
                    'nombre' => $archivo['nombre'],
                    'ruta' => $archivo['ruta'],
                    'tipo' => $archivo['tipo'],
                    'tamano' => $archivo['tamano']
                ];
            }
            $stmtArchivos->close();
            
            $comentarios[] = [
                'id' => $row['id'],
                'contenido' => $row['contenido'],
                'fecha_creacion' => $row['fecha_creacion'],
                'es_privado' => $row['es_privado'],
                'usuario' => [
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'foto_perfil' => $row['foto_perfil'] ?: 'vistas/assets/img/avatars/default.jpg'
                ],
                'archivos' => $archivos
            ];
        }
        
        echo json_encode(['status' => 'ok', 'comentarios' => $comentarios]);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al cargar comentarios']);
    }
    
    $conn->close();
    exit;
}

$ticketId = intval($_POST['ticketId']);
$usuarioId = intval($_POST['usuarioId']);
$contenido = trim($_POST['contenidoComentario']);

if (empty($contenido)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'El comentario no puede estar vacío']);
    exit;
}

try {
    $stmtTicket = $conn->prepare("SELECT usuario_creador_id FROM tickets WHERE id = ?");
    $stmtTicket->bind_param("i", $ticketId);
    $stmtTicket->execute();
    $ticketResult = $stmtTicket->get_result();
    
    if ($ticketResult->num_rows === 0) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Ticket no encontrado']);
        exit;
    }
    
    $ticket = $ticketResult->fetch_assoc();
    $stmtTicket->close();
    
    if (isset($_SESSION['consulta_temporal']) || isset($_POST['emailValidacion'])) {
        $emailValidacion = isset($_POST['emailValidacion']) ? $_POST['emailValidacion'] : $_SESSION['consulta_temporal']['email'];
        
        $stmtCreador = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
        $stmtCreador->bind_param("i", $ticket['usuario_creador_id']);
        $stmtCreador->execute();
        $creadorResult = $stmtCreador->get_result();
        
        if ($creadorResult->num_rows === 0) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Usuario creador no encontrado']);
            exit;
        }
        
        $creador = $creadorResult->fetch_assoc();
        $stmtCreador->close();
        
        if ($emailValidacion !== $creador['email']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'El email no coincide con el del creador del ticket']);
            exit;
        }
        
        $usuarioId = $ticket['usuario_creador_id'];
    }
    
    if ($usuarioId != $ticket['usuario_creador_id']) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Solo el creador del ticket puede agregar comentarios']);
        exit;
    }
    
    $conn->begin_transaction();
    
    $esPrivado = isset($_POST['esPrivado']) ? 1 : 0;
    $stmtComentario = $conn->prepare("
        INSERT INTO comentarios (ticket_id, usuario_id, contenido, es_privado, fecha_creacion) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtComentario->bind_param("iisi", $ticketId, $usuarioId, $contenido, $esPrivado);
    
    if (!$stmtComentario->execute()) {
        throw new Exception('Error al insertar comentario');
    }
    
    $comentarioId = $conn->insert_id;
    $stmtComentario->close();
    
    if (isset($_FILES['archivoComentario']) && $_FILES['archivoComentario']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['archivoComentario'];
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($archivo['size'] > $maxSize) {
            throw new Exception('El archivo excede el tamaño máximo permitido (5MB)');
        }
        
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($archivo['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/helpdesk/vistas/assets/img/archivos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'comentario_' . $comentarioId . '_' . time() . '.' . $extension;
        $rutaCompleta = $uploadDir . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception('Error al subir el archivo');
        }
        
        $stmtArchivo = $conn->prepare("
            INSERT INTO archivos (ticket_id, comentario_id, nombre, ruta, tipo, tamano, fecha_subida, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $rutaRelativa = 'vistas/assets/img/archivos/' . $nombreArchivo;
        $stmtArchivo->bind_param("iissiii", 
            $ticketId,
            $comentarioId, 
            $archivo['name'], 
            $rutaRelativa, 
            $archivo['type'], 
            $archivo['size'],
            $usuarioId
        );
        
        if (!$stmtArchivo->execute()) {
            throw new Exception('Error al guardar información del archivo');
        }
        $stmtArchivo->close();
    }
    
    $stmtUpdate = $conn->prepare("UPDATE tickets SET fecha_actualizacion = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("i", $ticketId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    $conn->commit();
    
    try {
        require_once "../config/email.config.php";
        
        $stmtTicketData = $conn->prepare("
            SELECT t.*, 
                   uc.nombre as cliente_nombre, uc.apellido as cliente_apellido, uc.email as cliente_email,
                   ut.nombre as tecnico_nombre, ut.apellido as tecnico_apellido, ut.email as tecnico_email,
                   c.nombre as categoria_nombre, p.nombre as prioridad_nombre, d.nombre as departamento_nombre
            FROM tickets t
            LEFT JOIN usuarios uc ON t.usuario_creador_id = uc.id
            LEFT JOIN usuarios ut ON t.tecnico_asignado_id = ut.id
            LEFT JOIN categorias c ON t.categoria_id = c.id
            LEFT JOIN prioridades p ON t.prioridad_id = p.id
            LEFT JOIN departamentos d ON t.departamento_asignado_id = d.id
            WHERE t.id = ?
        ");
        $stmtTicketData->bind_param("i", $ticketId);
        $stmtTicketData->execute();
        $ticketData = $stmtTicketData->get_result()->fetch_assoc();
        $stmtTicketData->close();
        
        $stmtComentarista = $conn->prepare("SELECT nombre, apellido, email FROM usuarios WHERE id = ?");
        $stmtComentarista->bind_param("i", $usuarioId);
        $stmtComentarista->execute();
        $comentaristaData = $stmtComentarista->get_result()->fetch_assoc();
        $stmtComentarista->close();
        
        if ($ticketData && $comentaristaData) {
            $mail = new PHPMailer(true);
            configurarPHPMailer($mail);
            
            $comentaristaNombre = trim($comentaristaData['nombre'] . ' ' . $comentaristaData['apellido']);
            $clienteNombre = trim($ticketData['cliente_nombre'] . ' ' . $ticketData['cliente_apellido']);
            $tecnicoNombre = trim($ticketData['tecnico_nombre'] . ' ' . $ticketData['tecnico_apellido']);
            
            if ($usuarioId != $ticketData['usuario_creador_id'] && !empty($ticketData['cliente_email'])) {
                $mensajeCliente = crear_mensaje_comentario_cliente(
                    $ticketId, 
                    $ticketData['titulo'], 
                    $contenido, 
                    $comentaristaNombre,
                    $clienteNombre
                );
                
                $asuntoCliente = "Nuevo comentario en su ticket #$ticketId";
                enviarEmail($ticketData['cliente_email'], $clienteNombre, $asuntoCliente, $mensajeCliente, true);
            }
            
            if ($usuarioId == $ticketData['usuario_creador_id'] && !empty($ticketData['tecnico_email'])) {
                $mensajeTecnico = crear_mensaje_comentario_tecnico(
                    $ticketId, 
                    $ticketData['titulo'], 
                    $contenido, 
                    $comentaristaNombre,
                    $tecnicoNombre,
                    $ticketData
                );
                
                $asuntoTecnico = "Nuevo comentario del cliente en ticket #$ticketId";
                enviarEmail($ticketData['tecnico_email'], $tecnicoNombre, $asuntoTecnico, $mensajeTecnico, true);
            }
        }
    } catch (Exception $emailError) {
        error_log("Error al enviar emails de comentario: " . $emailError->getMessage());
    }
    
    echo json_encode(['status' => 'ok', 'mensaje' => 'Comentario agregado correctamente']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}

$conn->close();

function crear_mensaje_comentario_cliente($ticketId, $titulo, $contenido, $comentaristaNombre, $clienteNombre) {
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#28a745; color:white; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">💬 Nuevo Comentario en su Ticket</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Estimado/a <strong>'.$clienteNombre.'</strong>,</p>
                
                <p style="color:#666; line-height:1.6">Se ha añadido un nuevo comentario a su ticket de soporte:</p>
                
                <div style="background:#f8f9fa; padding:20px; border-radius:6px; margin:20px 0">
                    <table style="width:100%; border-collapse:collapse">
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Ticket:</td>
                            <td style="padding:8px 0; color:#007bff; font-weight:bold">#'.$ticketId.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Título:</td>
                            <td style="padding:8px 0; color:#333">'.htmlspecialchars($titulo).'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Comentario de:</td>
                            <td style="padding:8px 0; color:#333">'.$comentaristaNombre.'</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background:#e8f5e8; border-left:4px solid #28a745; padding:20px; margin:20px 0; border-radius:0 6px 6px 0">
                    <h4 style="color:#155724; margin:0 0 10px 0; font-size:16px">💬 Comentario:</h4>
                    <p style="color:#155724; margin:0; line-height:1.6; white-space:pre-wrap">'.htmlspecialchars($contenido).'</p>
                </div>
                
                <p style="color:#666; line-height:1.6">Para ver el ticket completo y responder, puede acceder a nuestro portal de soporte.</p>
                
                <div style="text-align:center; margin:30px 0">
                    <p style="color:#999; font-size:14px; margin:0">
                        📧 Este es un mensaje automático, por favor no responder a este correo.
                    </p>
                </div>
            </div>
        </div>
    </div>';
}

function crear_mensaje_comentario_tecnico($ticketId, $titulo, $contenido, $comentaristaNombre, $tecnicoNombre, $ticketData) {
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#ffc107; color:#212529; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">🔔 Respuesta del Cliente</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Hola <strong>'.$tecnicoNombre.'</strong>,</p>
                
                <p style="color:#666; line-height:1.6">El cliente ha añadido un nuevo comentario al ticket asignado:</p>
                
                <div style="background:#f8f9fa; padding:20px; border-radius:6px; margin:20px 0">
                    <table style="width:100%; border-collapse:collapse">
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Ticket:</td>
                            <td style="padding:8px 0; color:#007bff; font-weight:bold">#'.$ticketId.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Título:</td>
                            <td style="padding:8px 0; color:#333">'.htmlspecialchars($titulo).'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Cliente:</td>
                            <td style="padding:8px 0; color:#333">'.$comentaristaNombre.'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Categoría:</td>
                            <td style="padding:8px 0; color:#333">'.$ticketData['categoria_nombre'].'</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#666; font-weight:bold">Prioridad:</td>
                            <td style="padding:8px 0; color:#333">'.$ticketData['prioridad_nombre'].'</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:20px; margin:20px 0; border-radius:0 6px 6px 0">
                    <h4 style="color:#856404; margin:0 0 10px 0; font-size:16px">💬 Comentario del Cliente:</h4>
                    <p style="color:#856404; margin:0; line-height:1.6; white-space:pre-wrap">'.htmlspecialchars($contenido).'</p>
                </div>
                
                <div style="background:#d1ecf1; border-left:4px solid #bee5eb; padding:15px; margin:20px 0; border-radius:0 6px 6px 0">
                    <p style="color:#0c5460; margin:0; font-size:14px">
                        ⚡ <strong>Acción requerida:</strong> Por favor revise el comentario y responda al cliente lo antes posible.
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
