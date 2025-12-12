<?php

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxCategorias {

    /*=============================================
    EDITAR CATEGORIA
    =============================================*/	
    public $idCategoria;

    public function ajaxEditarCategoria(){
        $item = "id";
        $valor = $this->idCategoria;
        $respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    VALIDAR CATEGORÍA EXISTENTE
    =============================================*/
    public $validarCategoria;

    public function ajaxValidarCategoria(){
        $item = "nombre";
        $valor = $this->validarCategoria;
        $respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    CREAR CATEGORIA
    =============================================*/
    public $nuevaCategoria;
    public $nuevaDescripcion;
    public $nuevoColor;
    public $nuevoIcono;

    public function ajaxCrearCategoria(){
        if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $this->nuevaCategoria)){
            $tabla = "categorias";
            
            $datos = array(
                "nombre" => $this->nuevaCategoria,
                "descripcion" => $this->nuevaDescripcion,
                "color" => $this->nuevoColor,
                "icono" => $this->nuevoIcono
            );
    
            $respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $datos);
            echo $respuesta;
        } else {
            echo "error-validacion";
        }
    }

    /*=============================================
    TRAER LISTADO DE CATEGORÍAS
    =============================================*/
    public function ajaxMostrarCategorias(){
        $item = null;
        $valor = null;
        $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
        
        
        $data = array();
        
        foreach ($categorias as $key => $value) {
            $acciones = '<div class="d-flex align-items-center justify-content-center gap-2">
                <button class="btn btn-sm btn-icon btn-primary btnEditarCategoria" idCategoria="'.$value["id"].'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditCategoria">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-danger btnEliminarCategoria" idCategoria="'.$value["id"].'">
                    <i class="bx bx-trash"></i>
                </button>
            </div>';
            
            $categoria = array(
                "id" => $value["id"],
                "categories" => $value["nombre"],
                "category_detail" => strip_tags($value["descripcion"]),
                "total_products" => $value["color"],
                "total_earnings" => $value["icono"],
                "actions" => $acciones
            );
            
            $data[] = $categoria;
        }
        
        echo json_encode(array("data" => $data));
    }
}

/*=============================================
EDITAR CATEGORIA
=============================================*/
if(isset($_POST["idCategoria"]) && !isset($_POST["editarCategoria"])){
    $categoria = new AjaxCategorias();
    $categoria->idCategoria = $_POST["idCategoria"];
    $categoria->ajaxEditarCategoria();
}

/*=============================================
VALIDAR CATEGORÍA EXISTENTE
=============================================*/
if(isset($_POST["validarCategoria"])){
    $valCategoria = new AjaxCategorias();
    $valCategoria->validarCategoria = $_POST["validarCategoria"];
    $valCategoria->ajaxValidarCategoria();
}

/*=============================================
CREAR CATEGORIA
=============================================*/
if(isset($_POST["nuevaCategoria"])){
    $crearCategoria = new AjaxCategorias();
    $crearCategoria->nuevaCategoria = $_POST["nuevaCategoria"];
    $crearCategoria->nuevaDescripcion = $_POST["nuevaDescripcion"];
    $crearCategoria->nuevoColor = $_POST["nuevoColor"];
    $crearCategoria->nuevoIcono = $_POST["nuevoIcono"];
    $crearCategoria->ajaxCrearCategoria();
}

/*=============================================
TRAER LISTADO DE CATEGORÍAS
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarCategorias"){
    $categorias = new AjaxCategorias();
    $categorias->ajaxMostrarCategorias();
}