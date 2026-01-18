<?php

require_once "../controladores/comentarios.controlador.php";
require_once "../modelos/comentarios.modelo.php";
require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";
require_once "../modelos/archivos.modelo.php";
require_once "../extensiones/PHPMailer/PHPMailerAutoload.php";


ini_set('display_errors', 0); 
error_reporting(E_ALL); 

class AjaxComentarios {

    /*=============================================
    CREAR COMENTARIO
    =============================================*/
    public function ajaxCrearComentario() {
        try {
          
            if (!isset($_POST["ticketId"]) || !isset($_POST["usuarioId"]) || !isset($_POST["contenidoComentario"])) {
                echo "error: Faltan datos obligatorios";
                return;
            }

            $datos = array(
                "ticket_id" => $_POST["ticketId"],
                "usuario_id" => $_POST["usuarioId"],
                "contenido" => $_POST["contenidoComentario"],
                "es_privado" => isset($_POST["esPrivado"]) ? 1 : 0
            );

           
            error_log("Datos recibidos para crear comentario: " . print_r($datos, true));

           
            if (isset($_FILES["archivoComentario"]) && !empty($_FILES["archivoComentario"]["name"])) {
              
                $rutaBase   = $_SERVER['DOCUMENT_ROOT'] . '/helpdesk/';
                $directorio = $rutaBase . "vistas/assets/img/archivos/";
                if (!file_exists($directorio)) {
                    if (!mkdir($directorio, 0755, true)) {
                        echo "error_archivo: No se pudo crear el directorio de archivos";
                        error_log("Error al crear directorio: " . error_get_last()['message']);
                        error_log("Ruta del directorio: " . $directorio);
                        return;
                    }
                }
                
             
                if (!is_writable($directorio)) {
                    echo "error_archivo: El directorio no tiene permisos de escritura";
                    error_log("El directorio no tiene permisos de escritura: " . $directorio);
                    return;
                }
                
            
                $extension = pathinfo($_FILES["archivoComentario"]["name"], PATHINFO_EXTENSION);
                $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
                
                if (!in_array(strtolower($extension), $extensionesPermitidas)) {
                    echo "error_archivo: Tipo de archivo no permitido";
                    return;
                }
                
                if ($_FILES["archivoComentario"]["size"] > 5000000) // 5MB
                {
                    echo "error_archivo: El archivo es demasiado grande (máximo 5MB)";
                    return;
                }
                
             
                $nombreArchivoUnico = time() . "_" . $_FILES["archivoComentario"]["name"];
                $archivo = $directorio . $nombreArchivoUnico;

                if (move_uploaded_file($_FILES["archivoComentario"]["tmp_name"], $archivo)) {
                   
                    if (file_exists($archivo)) {
                      
                        $rutaRelativa = "vistas/assets/img/archivos/" . $nombreArchivoUnico;
                        $datos["archivo"] = array(
                            "nombre" => $_FILES["archivoComentario"]["name"],
                            "ruta"   => $rutaRelativa,
                            "tipo"   => $_FILES["archivoComentario"]["type"],
                            "tamano" => $_FILES["archivoComentario"]["size"]
                        );
                        
                        error_log("✅ Archivo físico subido correctamente: " . $archivo);
                        error_log("Datos del archivo para BD: " . print_r($datos["archivo"], true));
                        
                       
                        chmod($archivo, 0644);
                    } else {
                        error_log("❌ El archivo no existe después de subirlo: " . $archivo);
                        echo "error_archivo: Archivo subido pero no encontrado";
                        return;
                    }
                } else {
                  
                    $errorMsg = error_get_last();
                    error_log("Error al subir archivo: " . ($errorMsg ? $errorMsg['message'] : 'Desconocido'));
                    error_log("Origen: " . $_FILES["archivoComentario"]["tmp_name"]);
                    error_log("Destino: " . $archivo);
                    error_log("Permisos: " . substr(sprintf('%o', fileperms($directorio)), -4));
                    
                    echo "error_archivo: No se pudo subir el archivo. Vea el log para más detalles.";
                    return;
                }
            }

           
            $respuesta = ControladorComentarios::ctrCrearComentario($datos);
            
            if ($respuesta === "ok") {
                try {
                    require_once "../config/email.config.php";
                    
                    $dbHost = getenv('DB_HOST') ?: 'localhost';
                    $dbUser = getenv('DB_USER') ?: 'root';
                    $dbPass = getenv('DB_PASS') ?: '';
                    $dbName = getenv('DB_NAME') ?: 'helpdesk';
                    $dbPort = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306;

                    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
                    if (!$conn->connect_error) {
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
                        $stmtTicketData->bind_param("i", $datos["ticket_id"]);
                        $stmtTicketData->execute();
                        $ticketData = $stmtTicketData->get_result()->fetch_assoc();
                        
                        $stmtComentarista = $conn->prepare("SELECT nombre, apellido, email FROM usuarios WHERE id = ?");
                        $stmtComentarista->bind_param("i", $datos["usuario_id"]);
                        $stmtComentarista->execute();
                        $comentaristaData = $stmtComentarista->get_result()->fetch_assoc();
                        
                        if ($ticketData && $comentaristaData) {
                            $mail = new PHPMailer(true);
                            configurarPHPMailer($mail);
                            
                            $comentaristaNombre = trim($comentaristaData['nombre'] . ' ' . $comentaristaData['apellido']);
                            $clienteNombre = trim($ticketData['cliente_nombre'] . ' ' . $ticketData['cliente_apellido']);
                            $tecnicoNombre = trim($ticketData['tecnico_nombre'] . ' ' . $ticketData['tecnico_apellido']);
                            
                            if ($datos["usuario_id"] != $ticketData['usuario_creador_id'] && !empty($ticketData['cliente_email'])) {
                                $mensajeCliente = crear_mensaje_comentario_cliente_admin(
                                    $datos["ticket_id"], 
                                    $ticketData['titulo'], 
                                    $datos["contenido"], 
                                    $comentaristaNombre,
                                    $clienteNombre
                                );
                                
                                $asuntoCliente = "Nuevo comentario en su ticket #" . $datos["ticket_id"];
                                enviarEmail($ticketData['cliente_email'], $clienteNombre, $asuntoCliente, $mensajeCliente, true);
                            }
                            
                            if ($datos["usuario_id"] == $ticketData['usuario_creador_id'] && !empty($ticketData['tecnico_email'])) {
                                $mensajeTecnico = crear_mensaje_comentario_tecnico_admin(
                                    $datos["ticket_id"], 
                                    $ticketData['titulo'], 
                                    $datos["contenido"], 
                                    $comentaristaNombre,
                                    $tecnicoNombre,
                                    $ticketData
                                );
                                
                                $asuntoTecnico = "Nuevo comentario del cliente en ticket #" . $datos["ticket_id"];
                                enviarEmail($ticketData['tecnico_email'], $tecnicoNombre, $asuntoTecnico, $mensajeTecnico, true);
                            }
                        }
                        
                        $conn->close();
                    }
                } catch (Exception $emailError) {
                    error_log("Error al enviar emails de comentario desde admin: " . $emailError->getMessage());
                }
            }
            
            echo $respuesta;
            
        } catch (Exception $e) {
            error_log("Error en ajaxCrearComentario: " . $e->getMessage());
            echo "error: " . $e->getMessage();
        }
    }

    /*=============================================
    MOSTRAR COMENTARIOS
    =============================================*/
    public $ticketId;

    public function ajaxMostrarComentarios() {
        try {
            if (!isset($this->ticketId) || !is_numeric($this->ticketId)) {
                echo json_encode(["error" => "ID de ticket inválido"]);
                return;
            }
            
            $item = "ticket_id";
            $valor = $this->ticketId;
            $comentarios = ControladorComentarios::ctrMostrarComentarios($item, $valor);
            
          
            if (!$comentarios) {
                echo json_encode([]);
                return;
            }

            foreach ($comentarios as &$comentario) {
              
                $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $comentario["usuario_id"]);
                $comentario["nombre_usuario"] = $usuario["nombre"] ?? "Usuario desconocido";
                
               
                if (isset($comentario["fecha_creacion"])) {
                    $fecha = new DateTime($comentario["fecha_creacion"]);
                    $comentario["fecha_creacion_formateada"] = $fecha->format('d/m/Y H:i:s');
                }
                
                
                if (class_exists('ModeloArchivos')) {
                    $archivos = ModeloArchivos::mdlMostrarArchivos("archivos", "comentario_id", $comentario["id"]);
                    
                    
                    if ($archivos && !empty($archivos)) {
                        foreach ($archivos as &$archivo) {
                            
                            $archivo['ruta'] = str_replace("\\", "/", $archivo['ruta']);
                            
                            
                            error_log("Ruta de archivo procesada: " . $archivo['ruta']);
                        }
                        error_log("Archivos encontrados para comentario ID " . $comentario["id"] . ": " . count($archivos));
                    }
                    
                    $comentario["archivos"] = $archivos ?: [];
                } else {
                    $comentario["archivos"] = [];
                    error_log("La clase ModeloArchivos no existe");
                }
            }

           
            ob_clean();
            echo json_encode($comentarios);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
            error_log("Error en ajaxMostrarComentarios: " . $e->getMessage());
        }
    }
}


set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error PHP [$errno] $errstr en $errfile:$errline");
});


ob_start();

/*=============================================
CREAR COMENTARIO
=============================================*/
if (isset($_POST["contenidoComentario"])) {
    $crearComentario = new AjaxComentarios();
    $crearComentario->ajaxCrearComentario();
}

/*=============================================
MOSTRAR COMENTARIOS
=============================================*/
if (isset($_POST["ticketId"]) && isset($_POST["accion"]) && $_POST["accion"] == "mostrarComentarios") {
    $comentarios = new AjaxComentarios();
    $comentarios->ticketId = $_POST["ticketId"];
    $comentarios->ajaxMostrarComentarios();
}


restore_error_handler();
restore_error_handler();

function crear_mensaje_comentario_cliente_admin($ticketId, $titulo, $contenido, $comentaristaNombre, $clienteNombre) {
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#28a745; color:white; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">💬 Respuesta de Soporte Técnico</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Estimado/a <strong>'.$clienteNombre.'</strong>,</p>
                
                <p style="color:#666; line-height:1.6">Nuestro equipo de soporte técnico ha respondido a su ticket:</p>
                
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
                            <td style="padding:8px 0; color:#666; font-weight:bold">Respondido por:</td>
                            <td style="padding:8px 0; color:#333">'.$comentaristaNombre.'</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background:#e8f5e8; border-left:4px solid #28a745; padding:20px; margin:20px 0; border-radius:0 6px 6px 0">
                    <h4 style="color:#155724; margin:0 0 10px 0; font-size:16px">💬 Respuesta del Técnico:</h4>
                    <p style="color:#155724; margin:0; line-height:1.6; white-space:pre-wrap">'.htmlspecialchars($contenido).'</p>
                </div>
                
                <p style="color:#666; line-height:1.6">Si necesita más información o tiene alguna pregunta adicional, puede responder a través de nuestro portal de soporte.</p>
                
                <div style="text-align:center; margin:30px 0">
                    <p style="color:#999; font-size:14px; margin:0">
                        📧 Este es un mensaje automático del sistema de soporte técnico.
                    </p>
                </div>
            </div>
        </div>
    </div>';
}

function crear_mensaje_comentario_tecnico_admin($ticketId, $titulo, $contenido, $comentaristaNombre, $tecnicoNombre, $ticketData) {
    return '
    <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
        <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
            <div style="background:#ffc107; color:#212529; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                <h2 style="margin:0; font-weight:300">🔔 Nuevo Comentario del Cliente</h2>
            </div>
            
            <div style="padding:30px">
                <p style="color:#333; font-size:16px; margin-bottom:20px">Hola <strong>'.$tecnicoNombre.'</strong>,</p>
                
                <p style="color:#666; line-height:1.6">El cliente ha añadido un comentario adicional al ticket:</p>
                
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
                    <h4 style="color:#856404; margin:0 0 10px 0; font-size:16px">💬 Comentario Adicional:</h4>
                    <p style="color:#856404; margin:0; line-height:1.6; white-space:pre-wrap">'.htmlspecialchars($contenido).'</p>
                </div>
                
                <div style="background:#d1ecf1; border-left:4px solid #bee5eb; padding:15px; margin:20px 0; border-radius:0 6px 6px 0">
                    <p style="color:#0c5460; margin:0; font-size:14px">
                        ⚡ <strong>Acción requerida:</strong> El cliente ha proporcionado información adicional. Por favor revise y responda si es necesario.
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