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

function crear_mensaje_tecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData) {
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
?>
