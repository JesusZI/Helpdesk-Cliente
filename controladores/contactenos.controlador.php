<?php

class ControladorContactenos {
    
    static public function ctrCrearTicket() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $mensaje = '';
        $tipoMensaje = '';
        $ticketId = '';

        if (isset($_GET['ticket_creado']) && isset($_SESSION['ticket_creado'])) {
            $ticketInfo = $_SESSION['ticket_creado'];
            
            if ((time() - $ticketInfo['timestamp']) < 300) { 
                if ($ticketInfo['email_enviado']) {
                    $mensaje = "¡Ticket creado exitosamente! Su número de ticket es: <strong>#" . $ticketInfo['id'] . "</strong>.<br>Se ha enviado un email de confirmación a: <strong>" . $ticketInfo['email'] . "</strong>";
                } else {
                    $mensaje = "¡Ticket creado exitosamente! Su número de ticket es: <strong>#" . $ticketInfo['id'] . "</strong>.<br><span class='text-warning'>Nota: No se pudo enviar el email de confirmación.</span>";
                }
                $tipoMensaje = 'success';
                $ticketId = $ticketInfo['id'];
            }
            
            unset($_SESSION['ticket_creado']);
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {
            error_log("Procesando creación de ticket...");
            
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $mensaje = 'Token de seguridad inválido. Por favor, intente nuevamente.';
                $tipoMensaje = 'error';
                error_log("Token CSRF inválido en creación de ticket");
            } else {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
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
                }
                
                if (empty($email)) {
                    $errores[] = 'El email es obligatorio.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = 'El email no tiene un formato válido.';
                }
                
                if (!empty($telefono) && !preg_match('/^[\+]?[0-9\s\-\(\)]{7,20}$/', $telefono)) {
                    $errores[] = 'El formato del teléfono no es válido.';
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
                    $dbHost = getenv('DB_HOST') ?: 'localhost';
                    $dbUser = getenv('DB_USER') ?: 'root';
                    $dbPass = getenv('DB_PASS') ?: '';
                    $dbName = getenv('DB_NAME') ?: 'helpdesk';
                    $dbPort = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306;

                    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

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
                            
                            $stmtUpdate = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE id = ?");
                            $stmtUpdate->bind_param("ssi", $nombre_completo, $telefono, $usuario_id);
                            $stmtUpdate->execute();
                            $stmtUpdate->close();
                        } else {
                            $password_temp = password_hash('temporal123', PASSWORD_DEFAULT);
                            
                            $nombres = explode(' ', $nombre_completo, 2);
                            $nombre = $nombres[0];
                            $apellido = isset($nombres[1]) ? $nombres[1] : '';
                            
                            $stmtNewUser = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, emailEncriptado, usuario, telefono, password, perfil, estado, ultimo_login, departamento_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            
                            $emailEncriptado = base64_encode($email);
                            $usuario_name = strtolower(str_replace(' ', '', $nombre . $apellido));
                            $perfil = 'cliente';
                            $estado = 1;
                            $ultimo_login = date('Y-m-d H:i:s');
                            
                            $stmtNewUser->bind_param("ssssssssiis", 
                                $nombre, $apellido, $email, $emailEncriptado, 
                                $usuario_name, $telefono, $password_temp, $perfil, $estado, $ultimo_login, $departamento_id
                            );
                            
                            if (!$stmtNewUser->execute()) {
                                throw new Exception('Error al crear el usuario: ' . $stmtNewUser->error);
                            }
                            
                            $usuario_id = $conn->insert_id;
                            $stmtNewUser->close();
                        }
                        $stmtUser->close();
                        
                        $stmtTecnico = $conn->prepare("SELECT id FROM usuarios WHERE perfil IN ('admin', 'tecnico') ORDER BY id ASC LIMIT 1");
                        $stmtTecnico->execute();
                        $tecnicoResult = $stmtTecnico->get_result();
                        $tecnico_id = $tecnicoResult->num_rows > 0 ? $tecnicoResult->fetch_assoc()['id'] : 1;
                        $stmtTecnico->close();
                        
                        $stmtTicket = $conn->prepare("INSERT INTO tickets (titulo, descripcion, usuario_creador_id, tecnico_asignado_id, categoria_id, prioridad_id, estado, fecha_creacion, fecha_actualizacion, departamento_origen_id, departamento_asignado_id) VALUES (?, ?, ?, ?, ?, ?, 'abierto', NOW(), NOW(), ?, ?)");
                        $stmtTicket->bind_param("ssiiiiii", $titulo, $descripcion, $usuario_id, $tecnico_id, $categoria_id, $prioridad_id, $departamento_id, $departamento_id);
                        
                        if (!$stmtTicket->execute()) {
                            throw new Exception('Error al crear el ticket');
                        }
                        
                        $ticketId = $conn->insert_id;
                        $stmtTicket->close();
                        
                        $conn->commit();
                        
                        require_once "config/email.config.php";
                        
                        date_default_timezone_set("America/Caracas");
                        
                        $stmtTecnicoEmail = $conn->prepare("SELECT email, nombre, apellido FROM usuarios WHERE id = ?");
                        $stmtTecnicoEmail->bind_param("i", $tecnico_id);
                        $stmtTecnicoEmail->execute();
                        $tecnicoData = $stmtTecnicoEmail->get_result()->fetch_assoc();
                        $stmtTecnicoEmail->close();
                        
                        $stmtDatos = $conn->prepare("
                            SELECT c.nombre as categoria_nombre, p.nombre as prioridad_nombre, d.nombre as departamento_nombre 
                            FROM categorias c, prioridades p, departamentos d 
                            WHERE c.id = ? AND p.id = ? AND d.id = ?
                        ");
                        $stmtDatos->bind_param("iii", $categoria_id, $prioridad_id, $departamento_id);
                        $stmtDatos->execute();
                        $datosTicket = $stmtDatos->get_result()->fetch_assoc();
                        $stmtDatos->close();
                        
                        $mensajeCliente = self::crearMensajeCliente($ticketId, $titulo, $descripcion, $nombre_completo, $datosTicket);
                        $mensajeTecnico = self::crearMensajeTecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData);
                        
                        $mail = new PHPMailer(true);
                        configurarPHPMailer($mail);
                        
                        $asuntoCliente = "Ticket creado exitosamente #$ticketId";
                        $resultadoCliente = enviarEmail($email, $nombre_completo, $asuntoCliente, $mensajeCliente, true);
                        
                        if ($tecnicoData) {
                            $tecnicoNombreCompleto = trim($tecnicoData['nombre'] . ' ' . $tecnicoData['apellido']);
                            $asuntoTecnico = "Nuevo ticket asignado #$ticketId";
                            $resultadoTecnico = enviarEmail($tecnicoData['email'], $tecnicoNombreCompleto, $asuntoTecnico, $mensajeTecnico, true);
                        }
                        
                        $_SESSION['ticket_creado'] = [
                            'id' => $ticketId,
                            'email' => $email,
                            'nombre' => $nombre_completo,
                            'email_enviado' => $resultadoCliente['success'],
                            'timestamp' => time()
                        ];
                        
                        header("Location: " . $_SERVER['REQUEST_URI'] . "?ticket_creado=1");
                        exit();
                        
                    } catch (Exception $e) {
                        $conn->rollback();
                        $mensaje = 'Ocurrió un error al procesar su solicitud. Por favor, intente nuevamente.';
                        $tipoMensaje = 'error';
                        
                        error_log("Error al crear ticket: " . $e->getMessage());
                    }
                    
                    $conn->close();
                }
            }
        }

        return array(
            'mensaje' => $mensaje,
            'tipoMensaje' => $tipoMensaje,
            'ticketId' => $ticketId
        );
    }

    static public function crearMensajeCliente($ticketId, $titulo, $descripcion, $nombre_completo, $datosTicket) {
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
                            <tr>
                                <td style="padding:8px 0; color:#666; font-weight:bold">Estado:</td>
                                <td style="padding:8px 0; color:#28a745; font-weight:bold">Abierto</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="background:#e8f4fd; border-left:4px solid #007bff; padding:15px; margin:20px 0">
                        <p style="margin:0; color:#004085"><strong>Descripción:</strong></p>
                        <p style="margin:10px 0 0 0; color:#004085">'.nl2br(htmlspecialchars($descripcion)).'</p>
                    </div>
                    
                    <p style="color:#666; line-height:1.6">Nuestro equipo de soporte revisará su solicitud y se pondrá en contacto con usted lo antes posible.</p>
                    
                    <div style="text-align:center; margin:30px 0">
                        <div style="background:#28a745; color:white; padding:15px; border-radius:6px; display:inline-block">
                            <strong>Tiempo estimado de respuesta: 24 horas</strong>
                        </div>
                    </div>
                    
                    <hr style="border:none; border-top:1px solid #eee; margin:30px 0">
                    
                    <p style="color:#999; font-size:14px; text-align:center; margin:0">
                        Este es un correo automático, por favor no responda a esta dirección.<br>
                        Para consultas adicionales, utilice el número de ticket #'.$ticketId.'
                    </p>
                </div>
            </div>
        </div>';
    }

    static public function crearMensajeTecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData) {
        $tecnicoNombreCompleto = trim($tecnicoData['nombre'] . ' ' . $tecnicoData['apellido']);
        
        return '
        <div style="width:100%; background:#f8f9fa; position:relative; font-family:Arial, sans-serif; padding:40px 0">
            <div style="position:relative; margin:auto; max-width:600px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1)">
                <div style="background:#dc3545; color:white; padding:30px; text-align:center; border-radius:8px 8px 0 0">
                    <h2 style="margin:0; font-weight:300">Nuevo Ticket Asignado</h2>
                </div>
                
                <div style="padding:30px">
                    <p style="color:#333; font-size:16px; margin-bottom:20px">Estimado/a <strong>'.$tecnicoNombreCompleto.'</strong>,</p>
                    
                    <p style="color:#666; line-height:1.6">Se le ha asignado un nuevo ticket de soporte. Por favor revise los detalles y proceda según corresponda:</p>
                    
                    <div style="background:#fff3cd; border:1px solid #ffeaa7; padding:20px; border-radius:6px; margin:20px 0">
                        <table style="width:100%; border-collapse:collapse">
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Número de Ticket:</td>
                                <td style="padding:8px 0; color:#dc3545; font-weight:bold">#'.$ticketId.'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Cliente:</td>
                                <td style="padding:8px 0; color:#333">'.$nombre_completo.'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Email del Cliente:</td>
                                <td style="padding:8px 0; color:#007bff">'.$email.'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Teléfono:</td>
                                <td style="padding:8px 0; color:#333">'.($telefono ?: 'No proporcionado').'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Título:</td>
                                <td style="padding:8px 0; color:#333">'.htmlspecialchars($titulo).'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Categoría:</td>
                                <td style="padding:8px 0; color:#333">'.$datosTicket['categoria_nombre'].'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Prioridad:</td>
                                <td style="padding:8px 0; color:#333">'.$datosTicket['prioridad_nombre'].'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#856404; font-weight:bold">Departamento:</td>
                                <td style="padding:8px 0; color:#333">'.$datosTicket['departamento_nombre'].'</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="background:#f8d7da; border-left:4px solid #dc3545; padding:15px; margin:20px 0">
                        <p style="margin:0; color:#721c24"><strong>Descripción del problema:</strong></p>
                        <p style="margin:10px 0 0 0; color:#721c24">'.nl2br(htmlspecialchars($descripcion)).'</p>
                    </div>
                    
                    <div style="text-align:center; margin:30px 0">
                        <div style="background:#007bff; color:white; padding:15px; border-radius:6px; display:inline-block">
                            <strong>¡Acción requerida! Por favor revisar y responder</strong>
                        </div>
                    </div>
                    
                    <hr style="border:none; border-top:1px solid #eee; margin:30px 0">
                    
                    <p style="color:#999; font-size:14px; text-align:center; margin:0">
                        Por favor inicie sesión en el sistema para gestionar este ticket.<br>
                        Ticket #'.$ticketId.' - Creado el '.date('d/m/Y H:i:s').'
                    </p>
                </div>
            </div>
        </div>';
    }
}
