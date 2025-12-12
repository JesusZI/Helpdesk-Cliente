<?php

session_start();

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

class AjaxUsuarios {

    /*=============================================
    EDITAR USUARIO
    =============================================*/	
    public $idUsuario;

    public function ajaxEditarUsuario(){
        $item = "id";
        $valor = $this->idUsuario;
        $respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
        echo json_encode($respuesta);
    }

    /*=============================================
    ACTIVAR USUARIO
    =============================================*/	
    public $activarUsuario;
    public $activarId;

    public function ajaxActivarUsuario(){
        $tabla = "usuarios";
        $item1 = "estado";
        $valor1 = $this->activarUsuario;
        $item2 = "id";
        $valor2 = $this->activarId;

        $respuesta = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);
        
        if ($respuesta === "ok") {
            echo json_encode([
                "status" => "ok", 
                "mensaje" => "Estado actualizado correctamente",
                "nuevo_estado" => $valor1
            ]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error al actualizar estado"]);
        }
    }

    /*=============================================
    CREAR USUARIO VIA AJAX
    =============================================*/	
    public $datosUsuario;

    public function ajaxCrearUsuario(){
        try {
            $datos = json_decode($this->datosUsuario, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en formato JSON: " . json_last_error_msg());
            }
            
            $respuesta = ControladorUsuarios::ctrCrearUsuarioAjax($datos);
            echo json_encode($respuesta);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error", 
                "mensaje" => "Error al procesar la solicitud: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    VALIDAR NO REPETIR USUARIO
    =============================================*/	
    public $validarUsuario;

    public function ajaxValidarUsuario(){
        $item = "usuario";
        $valor = $this->validarUsuario;
        $respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
        
       
        if(empty($respuesta) || !is_array($respuesta) || count($respuesta) == 0) {
            echo json_encode(false);
        } else {
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    VALIDAR NO REPETIR EMAIL
    =============================================*/	
    public $validarEmail;

    public function ajaxValidarEmail(){
        $item = "email";
        $valor = $this->validarEmail;
        $respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
        
        if(empty($respuesta) || !is_array($respuesta) || count($respuesta) == 0) {
            echo json_encode(false);
        } else {
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    VALIDAR NO REPETIR DOCUMENTO
    =============================================*/	
    public $validarDocumento;

    public function ajaxValidarDocumento(){
        $item = "documento";
        $valor = $this->validarDocumento;
        $respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
        
        if(empty($respuesta) || !is_array($respuesta) || count($respuesta) == 0) {
            echo json_encode(false);
        } else {
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    BORRAR USUARIO VIA AJAX
    =============================================*/
    public function ajaxBorrarUsuario(){
        $idUsuario = $this->idUsuario;
        $respuesta = ControladorUsuarios::ctrBorrarUsuarioAjax($idUsuario);
        echo json_encode($respuesta);
    }

    /*=============================================
    TRAER LISTADO DE USUARIOS
    =============================================*/
    public function ajaxMostrarUsuarios(){
        $item = null;
        $valor = null;
        $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
        
        $data = array();
        
        if(!empty($usuarios) && is_array($usuarios)) {
            foreach ($usuarios as $key => $value) {
             
                $foto_perfil_url = "vistas/assets/img/avatars/default.jpg";
                if (!empty($value["foto_perfil"])) {
                    if (strpos($value["foto_perfil"], "vistas/") === 0) {
                        if (file_exists($value["foto_perfil"])) {
                            $foto_perfil_url = $value["foto_perfil"];
                        }
                    } else {
                        $rutaCompleta = "vistas/assets/img/usuarios/" . $value["foto_perfil"];
                        if (file_exists($rutaCompleta)) {
                            $foto_perfil_url = $rutaCompleta;
                        }
                    }
                }
                
             
                $usuario = '<div class="d-flex justify-content-start align-items-center user-name">
                              <div class="avatar-wrapper">
                                <div class="avatar avatar-sm me-3">
                                  <img src="'.$foto_perfil_url.'" alt="Avatar" class="rounded-circle" width="32" height="32">
                                </div>
                              </div>
                              <div class="d-flex flex-column">
                                <span class="text-heading fw-medium">'.$value["nombre"].' '.$value["apellido"].'</span>
                                <span class="text-truncate mb-0 d-none d-sm-block"><small>@'.$value["usuario"].'</small></span>
                              </div>
                            </div>';
                
           
                $estado = (int)$value["estado"];
                $estadoFormateado = '<div class="text-center">
                                       <span class="badge '.($estado === 1 ? 'bg-label-success' : 'bg-label-danger').'" data-estado="'.$estado.'">
                                         '.($estado === 1 ? 'Activo' : 'Inactivo').'
                                       </span>
                                     </div>';
                
           
                $acciones = '<div class="d-flex align-items-center justify-content-center gap-1">
                              <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end m-0">
                                <button class="dropdown-item btnEditarUsuario" idUsuario="'.$value["id"].'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser">
                                  <i class="bx bx-edit me-2"></i>Editar
                                </button>
                                <button class="dropdown-item btnActivar" idUsuario="'.$value["id"].'" estadoUsuario="'.($estado === 1 ? 0 : 1).'">
                                  <i class="bx '.($estado === 1 ? 'bx-user-x' : 'bx-user-check').' me-2"></i>'.($estado === 1 ? 'Desactivar' : 'Activar').'
                                </button>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item text-danger btnEliminarUsuario" idUsuario="'.$value["id"].'">
                                  <i class="bx bx-trash me-2"></i>Eliminar
                                </button>
                              </div>
                            </div>';
                
                $usuario_data = array(
                    "id" => $value["id"],
                    "usuario" => $value["usuario"],
                    "nombre" => $value["nombre"],
                    "apellido" => $value["apellido"],
                    "foto_perfil_url" => $foto_perfil_url,
                    "perfil" => '<span class="badge bg-label-info">'.$value["perfil"].'</span>',
                    "documento" => $value["documento"] ? $value["documento"] : '<span class="text-muted">-</span>',
                    "email" => '<div class="text-truncate" style="max-width: 200px;" title="'.$value["email"].'">'.$value["email"].'</div>',
                    "estado" => $estadoFormateado,
                    "acciones" => $acciones
                );
                $data[] = $usuario_data;
            }
        }
        
        echo json_encode(array("data" => $data));
    }
}

/*=============================================
EDITAR USUARIO
=============================================*/
if(isset($_POST["idUsuario"])){
    $editar = new AjaxUsuarios();
    $editar -> idUsuario = $_POST["idUsuario"];
    $editar -> ajaxEditarUsuario();
}

/*=============================================
ACTIVAR USUARIO
=============================================*/	
if(isset($_POST["activarUsuario"]) && isset($_POST["activarId"])){
    $activarUsuario = new AjaxUsuarios();
    $activarUsuario -> activarUsuario = $_POST["activarUsuario"];
    $activarUsuario -> activarId = $_POST["activarId"];
    $activarUsuario -> ajaxActivarUsuario();
}

/*=============================================
CREAR USUARIO VIA AJAX
=============================================*/
if(isset($_POST["datosUsuario"])){
    $crearUsuario = new AjaxUsuarios();
    $crearUsuario -> datosUsuario = $_POST["datosUsuario"];
    $crearUsuario -> ajaxCrearUsuario();
}

/*=============================================
VALIDAR NO REPETIR USUARIO
=============================================*/
if(isset($_POST["validarUsuario"])){
    $valUsuario = new AjaxUsuarios();
    $valUsuario -> validarUsuario = $_POST["validarUsuario"];
    $valUsuario -> ajaxValidarUsuario();
}

/*=============================================
VALIDAR NO REPETIR EMAIL
=============================================*/
if(isset($_POST["validarEmail"])){
    $valEmail = new AjaxUsuarios();
    $valEmail -> validarEmail = $_POST["validarEmail"];
    $valEmail -> ajaxValidarEmail();
}

/*=============================================
VALIDAR NO REPETIR DOCUMENTO
=============================================*/
if(isset($_POST["validarDocumento"])){
    $valDocumento = new AjaxUsuarios();
    $valDocumento -> validarDocumento = $_POST["validarDocumento"];
    $valDocumento -> ajaxValidarDocumento();
}

/*=============================================
BORRAR USUARIO VIA AJAX
=============================================*/
if(isset($_GET["accion"]) && $_GET["accion"] == "borrar" && isset($_GET["idUsuario"])){
    $borrarUsuario = new AjaxUsuarios();
    $borrarUsuario -> idUsuario = $_GET["idUsuario"];
    $borrarUsuario -> ajaxBorrarUsuario();
}

/*=============================================
TRAER LISTADO DE USUARIOS
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "mostrarUsuarios"){
    $usuarios = new AjaxUsuarios();
    $usuarios->ajaxMostrarUsuarios();
}

/*=============================================
EDITAR USUARIO VIA FORM POST DIRECTO
=============================================*/
if(isset($_POST["editarUsuario"]) && isset($_POST["idUsuario"])){
    require_once "../controladores/usuarios.controlador.php";
    require_once "../modelos/usuarios.modelo.php";
    
    $controlador = new ControladorUsuarios();
    $resultado = $controlador->ctrEditarUsuarioAjax($_POST, $_FILES);
    
    echo json_encode($resultado);
    exit;
}