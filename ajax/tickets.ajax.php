<?php

require_once "../controladores/tickets.controlador.php";
require_once "../modelos/tickets.modelo.php";

class AjaxTickets {

    /*=============================================
    EDITAR TICKET
    =============================================*/
    public $idTicket;

    public function ajaxEditarTicket() {
        $item = "id";
        $valor = $this->idTicket;
        $respuesta = ControladorTickets::ctrMostrarTickets($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    CREAR TICKET
    =============================================*/
    public $nuevoTitulo;
    public $nuevaDescripcion;
    public $usuarioCreadorId;
    public $tecnicoAsignadoId;
    public $categoriaId;
    public $prioridadId;
    public $estado;

    public function ajaxCrearTicket() {
       
        if (!$this->usuarioCreadorId) {
            echo "error: ID de usuario no proporcionado";
            return;
        }

       
        $this->usuarioCreadorId = (int)$this->usuarioCreadorId;

       
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
        $stmt->execute([$this->usuarioCreadorId]);
        $usuarioExiste = $stmt->fetchColumn();

        if (!$usuarioExiste) {
            echo "error: El usuario con ID " . $this->usuarioCreadorId . " no existe";
            return;
        }

        $datos = array(
            "titulo" => $this->nuevoTitulo,
            "descripcion" => $this->nuevaDescripcion,
            "usuario_creador_id" => $this->usuarioCreadorId,
            "tecnico_asignado_id" => $this->tecnicoAsignadoId,
            "categoria_id" => $this->categoriaId,
            "prioridad_id" => $this->prioridadId,
            "estado" => $this->estado
        );

        try {
            $respuesta = ModeloTickets::mdlIngresarTicket("tickets", $datos);
            echo $respuesta;
        } catch (Exception $e) {
            echo "error: " . $e->getMessage();
        }
    }

    /*=============================================
    TRAER LISTADO DE TICKETS
    =============================================*/
    public function ajaxMostrarTickets() {
        $item = null;
        $valor = null;
        $estado = isset($_POST['estado']) && $_POST['estado'] !== '' ? $_POST['estado'] : null;

        if ($estado) {
            $item = "estado";
            $valor = $estado;
        }

        $tickets = ControladorTickets::ctrMostrarTickets($item, $valor);

       
        if ($tickets && isset($tickets['id'])) {
            $tickets = [$tickets];
        } elseif (!$tickets) {
            $tickets = [];
        }

       
        $estadisticas = $this->obtenerEstadisticasEstados();

        $data = array();
        foreach ($tickets as $key => $value) {
            $acciones = '<div class="d-flex align-items-center justify-content-center gap-2">
                <a href="index.php?ruta=consultar-ticket&idTicket=' . $value["id"] . '" class="btn btn-sm btn-icon btn-info">
                    <i class="bx bx-search"></i>
                </a>
                <button class="btn btn-sm btn-icon btn-primary btnEditarTicket" idTicket="' . $value["id"] . '" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditTicket">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-danger btnEliminarTicket" idTicket="' . $value["id"] . '">
                    <i class="bx bx-trash"></i>
                </button>
            </div>';

            $ticket = array(
                "id" => $value["id"],
                "titulo" => $value["titulo"],
                "descripcion" => strip_tags($value["descripcion"]),
                "estado" => $value["estado"],
                "acciones" => $acciones
            );

            $data[] = $ticket;
        }

        echo json_encode(array("data" => $data, "estadisticas" => $estadisticas));
    }

   
    public function obtenerEstadisticasEstados() {
        $todos = ControladorTickets::ctrMostrarTickets(null, null);
        $estadisticas = [
            "abierto" => 0,
            "en_proceso" => 0,
            "resuelto" => 0,
            "cerrado" => 0
        ];
        if ($todos && is_array($todos)) {
            foreach ($todos as $t) {
                if (isset($estadisticas[$t["estado"]])) {
                    $estadisticas[$t["estado"]]++;
                }
            }
        }
        return $estadisticas;
    }
}

/*=============================================
EDITAR TICKET
=============================================*/
if (isset($_POST["idTicket"])) {
    $ticket = new AjaxTickets();
    $ticket->idTicket = $_POST["idTicket"];
    $ticket->ajaxEditarTicket();
}

/*=============================================
CREAR TICKET
=============================================*/
if (isset($_POST["nuevoTitulo"])) {
    $crearTicket = new AjaxTickets();
    $crearTicket->nuevoTitulo = $_POST["nuevoTitulo"];
    $crearTicket->nuevaDescripcion = $_POST["nuevaDescripcion"];
    $crearTicket->usuarioCreadorId = $_POST["usuarioCreadorId"];
    $crearTicket->tecnicoAsignadoId = $_POST["tecnicoAsignadoId"];
    $crearTicket->categoriaId = $_POST["categoriaId"];
    $crearTicket->prioridadId = $_POST["prioridadId"];
    if (isset($_POST["estado"])) {
        $crearTicket->estado = $_POST["estado"];
    }
    $crearTicket->ajaxCrearTicket();
}

/*=============================================
TRAER LISTADO DE TICKETS
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "mostrarTickets") {
    $tickets = new AjaxTickets();
    $tickets->ajaxMostrarTickets();
}


if (isset($_POST["accion"]) && $_POST["accion"] == "estadisticasEstados") {
    $tickets = new AjaxTickets();
    echo json_encode(["estadisticas" => $tickets->obtenerEstadisticasEstados()]);
    exit;
}