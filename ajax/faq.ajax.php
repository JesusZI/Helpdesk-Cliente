<?php

require_once "../controladores/faq.controlador.php";
require_once "../modelos/faq.modelo.php";
require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxFaqs {

    /*=============================================
    EDITAR FAQ
    =============================================*/	
    public $idFaq;

    public function ajaxEditarFaq(){
        $item = "id";
        $valor = $this->idFaq;
        $respuesta = ControladorFaqs::ctrMostrarFaqs($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    VALIDAR PREGUNTA EXISTENTE
    =============================================*/
    public $validarPregunta;

    public function ajaxValidarPregunta(){
        $item = "pregunta";
        $valor = $this->validarPregunta;
        $respuesta = ControladorFaqs::ctrMostrarFaqs($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    CREAR FAQ
    =============================================*/
    public $nuevaPregunta;
    public $nuevaRespuesta;
    public $nuevaCategoriaFaq;

    public function ajaxCrearFaq(){
        if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ .,¿?!¡]+$/', $this->nuevaPregunta)){
            $tabla = "faqs";
            
            $datos = array(
                "pregunta" => $this->nuevaPregunta,
                "respuesta" => $this->nuevaRespuesta,
                "categoria_id" => $this->nuevaCategoriaFaq
            );
    
            $respuesta = ModeloFaqs::mdlIngresarFaq($tabla, $datos);
            echo $respuesta;
        } else {
            echo "error-validacion";
        }
    }

    /*=============================================
    TRAER LISTADO DE FAQS
    =============================================*/
    public function ajaxMostrarFaqs(){
        $item = null;
        $valor = null;
        $faqs = ControladorFaqs::ctrMostrarFaqs($item, $valor);
        

        $data = array();
        
        foreach ($faqs as $key => $value) {
        
            $itemCat = "id";
            $valorCat = $value["categoria_id"];
            $categoria = ControladorCategorias::ctrMostrarCategorias($itemCat, $valorCat);
            
            $acciones = '<div class="d-flex align-items-center justify-content-center gap-2">
                <button class="btn btn-sm btn-icon btn-primary btnEditarFaq" idFaq="'.$value["id"].'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditFaq">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-danger btnEliminarFaq" idFaq="'.$value["id"].'">
                    <i class="bx bx-trash"></i>
                </button>
            </div>';
            
            $faq = array(
                "id" => $value["id"],
                "question" => $value["pregunta"],
                "answer" => $value["respuesta"],
                "category_id" => $value["categoria_id"],
                "category_name" => $categoria ? $categoria["nombre"] : "Sin categoría",
                "actions" => $acciones
            );
            
            $data[] = $faq;
        }
        
        echo json_encode(array("data" => $data));
    }
}

/*=============================================
EDITAR FAQ
=============================================*/
if(isset($_POST["idFaq"]) && !isset($_POST["editarPregunta"])){
    $faq = new AjaxFaqs();
    $faq->idFaq = $_POST["idFaq"];
    $faq->ajaxEditarFaq();
}

/*=============================================
VALIDAR PREGUNTA EXISTENTE
=============================================*/
if(isset($_POST["validarPregunta"])){
    $valPregunta = new AjaxFaqs();
    $valPregunta->validarPregunta = $_POST["validarPregunta"];
    $valPregunta->ajaxValidarPregunta();
}

/*=============================================
CREAR FAQ
=============================================*/
if(isset($_POST["nuevaPregunta"])){
    $crearFaq = new AjaxFaqs();
    $crearFaq->nuevaPregunta = $_POST["nuevaPregunta"];
    $crearFaq->nuevaRespuesta = $_POST["nuevaRespuesta"];
    $crearFaq->nuevaCategoriaFaq = $_POST["nuevaCategoriaFaq"];
    $crearFaq->ajaxCrearFaq();
}

/*=============================================
TRAER LISTADO DE FAQS
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarFaqs"){
    $faqs = new AjaxFaqs();
    $faqs->ajaxMostrarFaqs();
}
