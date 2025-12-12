<?php

require_once "../controladores/prioridades.controlador.php";
require_once "../modelos/prioridades.modelo.php";

class AjaxPrioridades {

    /*=============================================
    EDITAR PRIORIDAD
    =============================================*/	
    public $idPrioridad;

    public function ajaxEditarPrioridad(){
        $item = "id";
        $valor = $this->idPrioridad;
        $respuesta = ControladorPrioridades::ctrMostrarPrioridades($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    CREAR PRIORIDAD
    =============================================*/
    public $nuevaPrioridad;
    public $nuevoTiempoRespuesta;
    public $nuevoColor;

    public function ajaxCrearPrioridad(){
        if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $this->nuevaPrioridad)){
            $tabla = "prioridades";
            
            $datos = array(
                "nombre" => $this->nuevaPrioridad,
                "tiempo_respuesta" => $this->nuevoTiempoRespuesta,
                "color" => $this->nuevoColor
            );
    
            $respuesta = ModeloPrioridades::mdlIngresarPrioridad($tabla, $datos);
            echo $respuesta;
        } else {
            echo "error-validacion";
        }
    }

    /*=============================================
    TRAER LISTADO DE PRIORIDADES
    =============================================*/
    public function ajaxMostrarPrioridades(){
        $item = null;
        $valor = null;
        $prioridades = ControladorPrioridades::ctrMostrarPrioridades($item, $valor);
        
  
        $data = array();
        
        foreach ($prioridades as $key => $value) {
            $acciones = '<div class="d-flex align-items-center justify-content-center gap-2">
                <button class="btn btn-sm btn-icon btn-primary btnEditarPrioridad" idPrioridad="'.$value["id"].'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditPrioridad">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-danger btnEliminarPrioridad" idPrioridad="'.$value["id"].'">
                    <i class="bx bx-trash"></i>
                </button>
            </div>';
            
            $prioridad = array(
                "id" => $value["id"],
                "nombre" => $value["nombre"],
                "tiempo_respuesta" => $value["tiempo_respuesta"] . ' horas',
                "color" => $value["color"],
                "actions" => $acciones
            );
            
            $data[] = $prioridad;
        }
        
        echo json_encode(array("data" => $data));
    }
}

/*=============================================
EDITAR PRIORIDAD
=============================================*/
if(isset($_POST["idPrioridad"]) && !isset($_POST["editarPrioridad"])){
    $prioridad = new AjaxPrioridades();
    $prioridad->idPrioridad = $_POST["idPrioridad"];
    $prioridad->ajaxEditarPrioridad();
}

/*=============================================
CREAR PRIORIDAD
=============================================*/
if(isset($_POST["nuevaPrioridad"])){
    $crearPrioridad = new AjaxPrioridades();
    $crearPrioridad->nuevaPrioridad = $_POST["nuevaPrioridad"];
    $crearPrioridad->nuevoTiempoRespuesta = $_POST["nuevoTiempoRespuesta"];
    $crearPrioridad->nuevoColor = $_POST["nuevoColor"];
    $crearPrioridad->ajaxCrearPrioridad();
}

/*=============================================
TRAER LISTADO DE PRIORIDADES
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarPrioridades"){
    $prioridades = new AjaxPrioridades();
    $prioridades->ajaxMostrarPrioridades();
}