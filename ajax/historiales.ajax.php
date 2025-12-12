<?php

require_once "../controladores/historiales.controlador.php";
require_once "../modelos/historiales.modelo.php";
require_once "../controladores/tickets.controlador.php";
require_once "../modelos/tickets.modelo.php";
require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

class AjaxHistoriales {

    /*=============================================
    MOSTRAR HISTORIALES
    =============================================*/
    public $filtroFechaDesde;
    public $filtroFechaHasta;
    public $filtroUsuarioId;
    public $filtroTicketId;
    public $filtroAccion;

    public function ajaxMostrarHistoriales() {
        try {
  
            $historiales = ControladorHistoriales::ctrMostrarHistoriales(
                $this->filtroFechaDesde ?? null,
                $this->filtroFechaHasta ?? null,
                $this->filtroUsuarioId ?? null,
                $this->filtroTicketId ?? null,
                $this->filtroAccion ?? null
            );

           
            $data = array();

            if ($historiales) {
                foreach ($historiales as $key => $historial) {
               
                    $ticket = ControladorTickets::ctrMostrarTickets("id", $historial["ticket_id"]);
                    $ticketTitulo = $ticket ? $ticket["titulo"] : "Ticket no encontrado";

               
                    $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $historial["usuario_id"]);
                    $usuarioNombre = $usuario ? $usuario["nombre"] : "Usuario desconocido";

            
                    $fecha = new DateTime($historial["fecha"]);
                    $fechaFormateada = $fecha->format('d/m/Y H:i:s');

          
                    $data[] = array(
                        "id" => $historial["id"],
                        "ticket_id" => $historial["ticket_id"],
                        "ticket_titulo" => $ticketTitulo,
                        "ticket_info" => $ticketTitulo, 
                        "usuario_id" => $historial["usuario_id"],
                        "usuario_nombre" => $usuarioNombre,
                        "accion" => $historial["accion"],
                        "detalles" => $historial["detalles"],
                        "fecha" => $fechaFormateada,
                        "actions" => "" 
                    );
                }
            }

            echo json_encode(array("data" => $data));
            
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

/*=============================================
TRAER LISTADO DE HISTORIALES
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "mostrarHistoriales") {
    $historiales = new AjaxHistoriales();
    

    if (isset($_POST["filtroFechaDesde"]) && !empty($_POST["filtroFechaDesde"])) {
        $historiales->filtroFechaDesde = $_POST["filtroFechaDesde"];
    }
    
    if (isset($_POST["filtroFechaHasta"]) && !empty($_POST["filtroFechaHasta"])) {
        $historiales->filtroFechaHasta = $_POST["filtroFechaHasta"];
    }
    
    if (isset($_POST["filtroUsuarioId"]) && !empty($_POST["filtroUsuarioId"])) {
        $historiales->filtroUsuarioId = $_POST["filtroUsuarioId"];
    }
    
    if (isset($_POST["filtroTicketId"]) && !empty($_POST["filtroTicketId"])) {
        $historiales->filtroTicketId = $_POST["filtroTicketId"];
    }
    
    if (isset($_POST["filtroAccion"]) && !empty($_POST["filtroAccion"])) {
        $historiales->filtroAccion = $_POST["filtroAccion"];
    }
    
    $historiales->ajaxMostrarHistoriales();
}
