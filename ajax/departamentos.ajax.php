<?php

require_once "../controladores/departamentos.controlador.php";
require_once "../modelos/departamentos.modelo.php";


ini_set('display_errors', 0); 
error_reporting(E_ALL); 

class AjaxDepartamentos {

    /*=============================================
    EDITAR DEPARTAMENTO
    =============================================*/	
    public $idDepartamento;

    public function ajaxEditarDepartamento(){
        try {
            $item = "id";
            $valor = $this->idDepartamento;
            $respuesta = ControladorDepartamentos::ctrMostrarDepartamentos($item, $valor);
            echo json_encode($respuesta);
        } catch(Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
            error_log("Error en ajaxEditarDepartamento: " . $e->getMessage());
        }
    }

    /*=============================================
    CREAR DEPARTAMENTO
    =============================================*/
    public $nuevoDepartamento;
    public $nuevaDescripcion;

    public function ajaxCrearDepartamento(){
        try {
            
            if(!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $this->nuevoDepartamento)){
                echo "error-validacion";
                return;
            }
            
            $tabla = "departamentos";
            
            $datos = array(
                "nombre" => $this->nuevoDepartamento,
                "descripcion" => $this->nuevaDescripcion
            );
            
            error_log("Datos para crear departamento: " . print_r($datos, true));
    
            $respuesta = ModeloDepartamentos::mdlIngresarDepartamento($tabla, $datos);
            echo $respuesta;
        } catch(Exception $e) {
            echo "error: " . $e->getMessage();
            error_log("Error en ajaxCrearDepartamento: " . $e->getMessage());
        }
    }

    /*=============================================
    TRAER LISTADO DE DEPARTAMENTOS
    =============================================*/
    public function ajaxMostrarDepartamentos(){
        try {
            $item = null;
            $valor = null;
            $departamentos = ControladorDepartamentos::ctrMostrarDepartamentos($item, $valor);
            
            
            $data = array();
            
            foreach ($departamentos as $key => $value) {
                $acciones = '<div class="d-flex align-items-center justify-content-center gap-2">
                    <button class="btn btn-sm btn-icon btn-primary btnEditarDepartamento" idDepartamento="'.$value["id"].'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditDepartamento">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-danger btnEliminarDepartamento" idDepartamento="'.$value["id"].'">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>';
                
                $departamento = array(
                    "id" => $value["id"],
                    "nombre" => $value["nombre"],
                    "descripcion" => $value["descripcion"],
                    "actions" => $acciones
                );
                
                $data[] = $departamento;
            }
            
            echo json_encode(array("data" => $data));
        } catch(Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
            error_log("Error en ajaxMostrarDepartamentos: " . $e->getMessage());
        }
    }
}


ob_start();

/*=============================================
EDITAR DEPARTAMENTO
=============================================*/
if(isset($_POST["idDepartamento"]) && !isset($_POST["editarDepartamento"])){
    $departamento = new AjaxDepartamentos();
    $departamento->idDepartamento = $_POST["idDepartamento"];
    $departamento->ajaxEditarDepartamento();
}

/*=============================================
CREAR DEPARTAMENTO
=============================================*/
if(isset($_POST["nuevoDepartamento"])){
    $crearDepartamento = new AjaxDepartamentos();
    $crearDepartamento->nuevoDepartamento = $_POST["nuevoDepartamento"];
    $crearDepartamento->nuevaDescripcion = $_POST["nuevaDescripcion"];
    $crearDepartamento->ajaxCrearDepartamento();
}

/*=============================================
TRAER LISTADO DE DEPARTAMENTOS
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarDepartamentos"){
    $departamentos = new AjaxDepartamentos();
    $departamentos->ajaxMostrarDepartamentos();
}


ob_end_flush();