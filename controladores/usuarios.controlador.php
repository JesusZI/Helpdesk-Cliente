<?php

class ControladorUsuarios{

    /*=============================================
    INGRESO DE USUARIO
    =============================================*/

    static public function ctrIngresoUsuario(){

        if(isset($_POST["ingUsuario"])){

            if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) &&
               preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])){

               	$encriptar = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                $tabla = "usuarios";

                $item = "usuario";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

                if($respuesta["usuario"] == $_POST["ingUsuario"] && $respuesta["password"] == $encriptar){

                    if($respuesta["estado"] == 1){

                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["id"] = $respuesta["id"];
                        $_SESSION["nombre"] = $respuesta["nombre"];
                        $_SESSION["apellido"] = $respuesta["apellido"];
                        $_SESSION["usuario"] = $respuesta["usuario"];
                        $_SESSION["foto_perfil"] = $respuesta["foto_perfil"];
                        $_SESSION["telefono"] = $respuesta["telefono"];
                        $_SESSION["direccion"] = $respuesta["direccion"];
                        $_SESSION["email"] = $respuesta["email"];
                        $_SESSION["emailEncriptado"] = $respuesta["emailEncriptado"];
                        $_SESSION["fecha_nacimiento"] = $respuesta["fecha_nacimiento"];
                        $_SESSION["documento"] = $respuesta["documento"];
                        $_SESSION["password"] = $respuesta["password"];
                        $_SESSION["perfil"] = $respuesta["perfil"];

                        /*=============================================
                        REGISTRAR FECHA PARA SABER EL ÚLTIMO LOGIN
                        =============================================*/

                        date_default_timezone_set('America/Caracas');

                        $fecha = date('Y-m-d');
                        $hora = date('H:i:s');

                        $fechaActual = $fecha.' '.$hora;

                        $item1 = "ultimo_login";
                        $valor1 = $fechaActual;

                        $item2 = "id";
                        $valor2 = $respuesta["id"];

                        $ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

                        if($ultimoLogin == "ok"){
                            echo '<script>
                                window.location = "inicio";
                            </script>';
                        }				
                        
                    }else{
                        echo '<br>
                            <div class="alert alert-danger">El usuario aún no está activado</div>';
                    }		

                }else{
                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
                }
            }	
        }
    }

    /*=============================================
    REGISTRO DE USUARIO
    =============================================*/

    static public function ctrCrearUsuario(){

        if(isset($_POST["nuevoUsuario"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
               preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoApellido"]) &&
               preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
               preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])){

                /*=============================================
                NO PERMITIR SUBIR IMAGEN AL CREAR - USAR DEFAULT
                =============================================*/
                $ruta = ""; 

                $tabla = "usuarios";

                $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                $encriptarEmail = md5($_POST["nuevoEmail"]);

                $datos = array(
                      "nombre" => $_POST["nuevoNombre"],
                      "apellido" => $_POST["nuevoApellido"],
                      "usuario" => $_POST["nuevoUsuario"],
                      "documento"=>$_POST["nuevoDocumento"],
                      "email" =>$_POST["nuevoEmail"],
                      "emailEncriptado"=>$encriptarEmail,
                      "telefono"=>$_POST["nuevoTelefono"],
                      "direccion"=>$_POST["nuevaDireccion"],
                      "fecha_nacimiento"=>$_POST["nuevaFechaNacimiento"],
                      "password" => $encriptar,
                      "perfil" => $_POST["nuevoPerfil"],
                      "foto_perfil" => $ruta,
                      "estado" => 1);

                $respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);
            
                if($respuesta == "ok"){

                    echo '<script>
                     	Swal.fire({
                          type: "success",
                          title: "¡El usuario ha sido guardado correctamente!",
                          showConfirmButton: true,
                          confirmButtonText: "Cerrar",
                          closeOnConfirm: false
                          }).then((result) => {
                            if (result.value) {
                                window.location = "usuarios";
                            }
                          })
                    </script>';
                }	
            }else{
                echo '<script>
                    Swal.fire({
                        type: "error",
                        title: "¡El nombre, apellido o usuario no pueden ir vacíos o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "usuarios";
                        }
                    })
                </script>';
            }
        }
    }
    
    /*=============================================
    MOSTRAR USUARIO
    =============================================*/

    static public function ctrMostrarUsuarios($item, $valor){
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    MOSTRAR USUARIOS POR TIPO DE PERFIL
    =============================================*/

    static public function ctrMostrarUsuariosPorTipoPerfil($tipoPerfil) {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuariosPorTipoPerfil($tabla, $tipoPerfil);
        return $respuesta;
    }

    /*=============================================
    MOSTRAR USUARIOS POR PERFIL
    =============================================*/

    static public function ctrMostrarUsuariosPorPerfil($perfil) {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuariosPorPerfil($tabla, $perfil);
        return $respuesta;
    }

    /*=============================================
    CONTAR USUARIOS POR PERFIL
    =============================================*/

    static public function ctrContarUsuariosPorPerfil($item, $valor){
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlContarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    CONTAR USUARIOS POR ESTADO
    =============================================*/

    static public function ctrContarUsuariosPorEstado($item, $valor){
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlContarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    EDITAR USUARIO
    =============================================*/

    static public function ctrEditarUsuario(){

        if(isset($_POST["editarUsuario"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"]) &&
               preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarApellido"])){

                /*=============================================
                VALIDAR IMAGEN - SOLO EN EDICIÓN
                =============================================*/

                $ruta = $_POST["fotoActual"];

                if(isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])){
                    
                    list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);

                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                  
                    $directorioAbsoluto = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/vistas/assets/img/usuarios/";
                    $directorioRelativo = "vistas/assets/img/usuarios/";
                    
                    if(!file_exists($directorioAbsoluto)){
                        mkdir($directorioAbsoluto, 0755, true);
                    }

                   
                    if(isset($_POST["fotoActual"]) && $_POST["fotoActual"] != "" && 
                       $_POST["fotoActual"] != "vistas/assets/img/avatars/default.jpg" &&
                       strpos($_POST["fotoActual"], "vistas/assets/img/usuarios/") !== false){
                        $rutaAbsolutaAnterior = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/" . $_POST["fotoActual"];
                        if(file_exists($rutaAbsolutaAnterior)) {
                            unlink($rutaAbsolutaAnterior);
                        }
                    }

                    
                    if($_FILES["editarFoto"]["type"] == "image/jpeg"){
                        
                        $aleatorio = mt_rand(100,999);
                        $rutaAbsoluta = $directorioAbsoluto.$aleatorio.".jpg";
                        $ruta = $directorioRelativo.$aleatorio.".jpg";
                        
                        $origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);						
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $rutaAbsoluta);

                    }

                    if($_FILES["editarFoto"]["type"] == "image/png"){
                        
                        $aleatorio = mt_rand(100,999);
                        $rutaAbsoluta = $directorioAbsoluto.$aleatorio.".png";
                        $ruta = $directorioRelativo.$aleatorio.".png";
                        
                        $origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);						
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                      
                        imagealphablending($destino, false);
                        imagesavealpha($destino, true);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $rutaAbsoluta);
                    }
                }

                $tabla = "usuarios";

                if($_POST["editarPassword"] != ""){
                    if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])){
                        $encriptar = crypt($_POST["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    }else{
                        echo'<script>
                            Swal.fire({
                                      type: "error",
                                      title: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
                                      showConfirmButton: true,
                                      confirmButtonText: "Cerrar"
                                      }).then(function(result) {
                                        if (result.value) {
                                        window.location = "usuarios";
                                        }
                                    })
                        </script>';
                        return;
                    }
                }else{
                    $encriptar = $_POST["passwordActual"];
                }

                $datos = array(
                    "id" => $_POST["idUsuario"],
                    "nombre" => $_POST["editarNombre"],
                    "apellido" => $_POST["editarApellido"],
                    "documento" => $_POST["editarDocumento"],
                    "email" => $_POST["editarEmail"],
                    "usuario" => $_POST["editarUsuario"],
                    "telefono" => $_POST["editarTelefono"],
                    "direccion" => $_POST["editarDireccion"],
                    "fecha_nacimiento" => $_POST["editarFechaNacimiento"],
                    "password" => $encriptar,
                    "perfil" => $_POST["editarPerfil"],
                    "foto_perfil" => $ruta
                );

                $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

                if($respuesta == "ok"){
                    
                    if(isset($_SESSION["id"]) && $_SESSION["id"] == $_POST["idUsuario"]) {
                        $_SESSION["nombre"] = $_POST["editarNombre"];
                        $_SESSION["apellido"] = $_POST["editarApellido"];
                        $_SESSION["documento"] = $_POST["editarDocumento"];
                        $_SESSION["email"] = $_POST["editarEmail"];
                        $_SESSION["usuario"] = $_POST["editarUsuario"];
                        $_SESSION["telefono"] = $_POST["editarTelefono"];
                        $_SESSION["direccion"] = $_POST["editarDireccion"];
                        $_SESSION["fecha_nacimiento"] = $_POST["editarFechaNacimiento"];
                        $_SESSION["password"] = $encriptar;
                        $_SESSION["perfil"] = $_POST["editarPerfil"];
                        $_SESSION["foto_perfil"] = $ruta;
                        $_SESSION["emailEncriptado"] = md5($_POST["editarEmail"]);
                    }
                    
                    echo'<script>
                        Swal.fire({
                            type: "success",
                            title: "El usuario ha sido editado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "usuarios";
                            }
                        })
                    </script>';
                }
            }else{
                echo'<script>
                    Swal.fire({
                        type: "error",
                        title: "¡El nombre o apellido no pueden ir vacíos o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "usuarios";
                        }
                    })
                </script>';
            }
        }
    }

    /*=============================================
    BORRAR USUARIO
    =============================================*/

    static public function ctrBorrarUsuario(){
        if(isset($_GET["idUsuario"])){
            $tabla ="usuarios";
            $datos = $_GET["idUsuario"];

           
            $usuario = ModeloUsuarios::MdlMostrarUsuarios($tabla, "id", $datos);
            
           
            if(is_array($usuario) && isset($usuario["foto_perfil"]) && !empty($usuario["foto_perfil"]) && $usuario["foto_perfil"] != "vistas/assets/img/avatars/default.jpg"){
               
                if(file_exists($usuario["foto_perfil"])){
                    unlink($usuario["foto_perfil"]);
                }
            }

            $respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

            if($respuesta == "ok"){
                echo'<script>
                    Swal.fire({
                        type: "success",
                        title: "El usuario ha sido borrado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "usuarios";
                        }
                    })
                </script>';
            }		
        }
    }

    /*=============================================
    REGISTRO DE USUARIO VIA AJAX
    =============================================*/

    static public function ctrCrearUsuarioAjax($datos){
        try {
          
            if(empty($datos["nuevoNombre"]) || 
               empty($datos["nuevoApellido"]) || 
               empty($datos["nuevoUsuario"]) ||
               empty($datos["nuevoPassword"])) {
                return [
                    "status" => "error",
                    "mensaje" => "Faltan datos obligatorios"
                ];
            }

          
            if(!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $datos["nuevoNombre"]) ||
               !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $datos["nuevoApellido"]) ||
               !preg_match('/^[a-zA-Z0-9]+$/', $datos["nuevoUsuario"]) ||
               !preg_match('/^[a-zA-Z0-9]+$/', $datos["nuevoPassword"])){
                return [
                    "status" => "error",
                    "mensaje" => "El nombre, apellido o usuario no pueden ir vacíos o llevar caracteres especiales"
                ];
            }

            $tabla = "usuarios";

           
            $usuarioExistente = ModeloUsuarios::mdlMostrarUsuarios($tabla, "usuario", $datos["nuevoUsuario"]);
            if(!empty($usuarioExistente) && is_array($usuarioExistente) && isset($usuarioExistente["id"])) {
                return [
                    "status" => "error", 
                    "mensaje" => "El nombre de usuario ya existe en el sistema"
                ];
            }

            
            if(!empty($datos["nuevoEmail"])) {
                if(!filter_var($datos["nuevoEmail"], FILTER_VALIDATE_EMAIL)) {
                    return [
                        "status" => "error",
                        "mensaje" => "El formato del email no es válido"
                    ];
                }
                
                $emailExistente = ModeloUsuarios::mdlMostrarUsuarios($tabla, "email", $datos["nuevoEmail"]);
                if(!empty($emailExistente) && is_array($emailExistente) && isset($emailExistente["id"])) {
                    return [
                        "status" => "error", 
                        "mensaje" => "El email ya está registrado"
                    ];
                }
            }

           
            if(!empty($datos["nuevoDocumento"])) {
                $documentoExistente = ModeloUsuarios::mdlMostrarUsuarios($tabla, "documento", $datos["nuevoDocumento"]);
                if(!empty($documentoExistente) && is_array($documentoExistente) && isset($documentoExistente["id"])) {
                    return [
                        "status" => "error", 
                        "mensaje" => "El documento ya está registrado"
                    ];
                }
            }

           
            $ruta = ""; 

           
            $encriptar = crypt($datos["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            $encriptarEmail = !empty($datos["nuevoEmail"]) ? md5($datos["nuevoEmail"]) : "";

            $datosDB = [
                "nombre" => $datos["nuevoNombre"],
                "apellido" => $datos["nuevoApellido"],
                "usuario" => $datos["nuevoUsuario"],
                "documento" => isset($datos["nuevoDocumento"]) ? $datos["nuevoDocumento"] : "",
                "email" => isset($datos["nuevoEmail"]) ? $datos["nuevoEmail"] : "",
                "emailEncriptado" => $encriptarEmail,
                "telefono" => isset($datos["nuevoTelefono"]) ? $datos["nuevoTelefono"] : "",
                "direccion" => isset($datos["nuevaDireccion"]) ? $datos["nuevaDireccion"] : "",
                "fecha_nacimiento" => isset($datos["nuevaFechaNacimiento"]) ? $datos["nuevaFechaNacimiento"] : null,
                "password" => $encriptar,
                "perfil" => isset($datos["nuevoPerfil"]) ? $datos["nuevoPerfil"] : "Cliente",
                "foto_perfil" => $ruta,
                "estado" => 1
            ];

            $respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datosDB);
            
            if($respuesta == "ok"){
                return [
                    "status" => "ok",
                    "mensaje" => "¡El usuario ha sido guardado correctamente!"
                ];
            } else {
                return [
                    "status" => "error",
                    "mensaje" => "Error al crear el usuario en la base de datos"
                ];
            }

        } catch (Exception $e) {
            return [
                "status" => "error", 
                "mensaje" => "Error: " . $e->getMessage()
            ];
        }
    }

    /*=============================================
    BORRAR USUARIO VIA AJAX
    =============================================*/

    static public function ctrBorrarUsuarioAjax($idUsuario){
        try {
            $tabla ="usuarios";
            $datos = $idUsuario;

           
            $usuario = ModeloUsuarios::MdlMostrarUsuarios($tabla, "id", $datos);
            
           
            if(is_array($usuario) && isset($usuario["foto_perfil"]) && !empty($usuario["foto_perfil"]) && $usuario["foto_perfil"] != "vistas/assets/img/avatars/default.jpg"){
                
                if(file_exists($usuario["foto_perfil"])){
                    unlink($usuario["foto_perfil"]);
                }
            }

            $respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

            if($respuesta == "ok"){
                return [
                    "status" => "ok",
                    "mensaje" => "El usuario ha sido borrado correctamente"
                ];
            } else {
                return [
                    "status" => "error",
                    "mensaje" => "Error al borrar el usuario"
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error",
                "mensaje" => "Error: " . $e->getMessage()
            ];
        }
    }

    /*=============================================
    EDITAR USUARIO VIA AJAX
    =============================================*/

    static public function ctrEditarUsuarioAjax($datos, $archivos){
        try {
            if(!isset($datos["editarNombre"]) || 
               !isset($datos["editarApellido"]) || 
               !isset($datos["idUsuario"])) {
                throw new Exception("Faltan datos obligatorios");
            }

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $datos["editarNombre"]) &&
               preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $datos["editarApellido"])){

                /*=============================================
                VALIDAR IMAGEN - SOLO EN EDICIÓN
                =============================================*/
                $ruta = $datos["fotoActual"];

                if(isset($archivos["editarFoto"]["tmp_name"]) && !empty($archivos["editarFoto"]["tmp_name"])){
                    
                    list($ancho, $alto) = getimagesize($archivos["editarFoto"]["tmp_name"]);

                    $nuevoAncho = 500;
                    $nuevoAlto = 500;
                    
                    $directorioAbsoluto = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/vistas/assets/img/usuarios/";
                    $directorioRelativo = "vistas/assets/img/usuarios/";
                    
                    if(!file_exists($directorioAbsoluto)){
                        mkdir($directorioAbsoluto, 0755, true);
                    }

                    
                    if(isset($datos["fotoActual"]) && $datos["fotoActual"] != "" && 
                       $datos["fotoActual"] != "vistas/assets/img/avatars/default.jpg" &&
                       strpos($datos["fotoActual"], "vistas/assets/img/usuarios/") !== false){
                        $rutaAbsolutaAnterior = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/" . $datos["fotoActual"];
                        if(file_exists($rutaAbsolutaAnterior)){
                            unlink($rutaAbsolutaAnterior);
                        }
                    }

                  
                    if($archivos["editarFoto"]["type"] == "image/jpeg"){
                        
                        $aleatorio = mt_rand(100,999);
                        $rutaAbsoluta = $directorioAbsoluto.$aleatorio.".jpg";
                        $ruta = $directorioRelativo.$aleatorio.".jpg";
                        
                        $origen = imagecreatefromjpeg($archivos["editarFoto"]["tmp_name"]);						
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $rutaAbsoluta);

                    }

                    if($archivos["editarFoto"]["type"] == "image/png"){
                        
                        $aleatorio = mt_rand(100,999);
                        $rutaAbsoluta = $directorioAbsoluto.$aleatorio.".png";
                        $ruta = $directorioRelativo.$aleatorio.".png";
                        
                        $origen = imagecreatefrompng($archivos["editarFoto"]["tmp_name"]);						
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                        
                        imagealphablending($destino, false);
                        imagesavealpha($destino, true);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $rutaAbsoluta);
                    }
                }

                $tabla = "usuarios";

                if($datos["editarPassword"] != ""){
                    if(preg_match('/^[a-zA-Z0-9]+$/', $datos["editarPassword"])){
                        $encriptar = crypt($datos["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    } else {
                        return [
                            "status" => "error",
                            "mensaje" => "La contraseña no puede ir vacía o llevar caracteres especiales"
                        ];
                    }
                } else {
                    $encriptar = $datos["passwordActual"];
                }

                $datosUsuario = array(
                    "id" => $datos["idUsuario"],
                    "nombre" => $datos["editarNombre"],
                    "apellido" => $datos["editarApellido"],
                    "documento" => $datos["editarDocumento"],
                    "email" => $datos["editarEmail"],
                    "usuario" => $datos["editarUsuario"],
                    "telefono" => $datos["editarTelefono"],
                    "direccion" => $datos["editarDireccion"],
                    "fecha_nacimiento" => $datos["editarFechaNacimiento"],
                    "password" => $encriptar,
                    "perfil" => $datos["editarPerfil"],
                    "foto_perfil" => $ruta
                );

                $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datosUsuario);

                if($respuesta == "ok"){
                    
                    if(isset($_SESSION["id"]) && $_SESSION["id"] == $datos["idUsuario"]) {
                        $_SESSION["nombre"] = $datos["editarNombre"];
                        $_SESSION["apellido"] = $datos["editarApellido"];
                        $_SESSION["documento"] = $datos["editarDocumento"];
                        $_SESSION["email"] = $datos["editarEmail"];
                        $_SESSION["usuario"] = $datos["editarUsuario"];
                        $_SESSION["telefono"] = $datos["editarTelefono"];
                        $_SESSION["direccion"] = $datos["editarDireccion"];
                        $_SESSION["fecha_nacimiento"] = $datos["editarFechaNacimiento"];
                        $_SESSION["password"] = $encriptar;
                        $_SESSION["perfil"] = $datos["editarPerfil"];
                        $_SESSION["foto_perfil"] = $ruta;
                        $_SESSION["emailEncriptado"] = md5($datos["editarEmail"]);
                    }
                    
                    return [
                        "status" => "ok",
                        "mensaje" => "El usuario ha sido editado correctamente"
                    ];
                } else {
                    return [
                        "status" => "error",
                        "mensaje" => "Error al editar el usuario"
                    ];
                }
            } else {
                return [
                    "status" => "error",
                    "mensaje" => "El nombre o apellido no pueden ir vacíos o llevar caracteres especiales"
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error",
                "mensaje" => "Error: " . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ACTUALIZAR PERFIL
    =============================================*/
    static public function ctrActualizarPerfil(){
        if(isset($_POST["idUsuario"])){
            
            error_log("=== DEBUG ACTUALIZAR PERFIL ===");
            error_log("POST recibido: " . print_r($_POST, true));
            error_log("FILES recibido: " . print_r($_FILES, true));
            error_log("SESSION actual: " . print_r($_SESSION, true));
           
            if(empty($_POST["editarNombre"]) || empty($_POST["editarApellido"]) || empty($_POST["editarEmail"])) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Los campos Nombre, Apellido y Email son obligatorios",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            if(!filter_var($_POST["editarEmail"], FILTER_VALIDATE_EMAIL)){
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El correo electrónico no tiene un formato válido",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $tabla = "usuarios";
            
            if($_SESSION["email"] != $_POST["editarEmail"]){
                $verificarEmail = ModeloUsuarios::mdlMostrarUsuarios($tabla, "email", $_POST["editarEmail"]);
                if($verificarEmail && $verificarEmail["id"] != $_POST["idUsuario"]){
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "El correo electrónico ya está siendo usado por otro usuario",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            $rutaFotoPerfil = $_SESSION["foto_perfil"] ?? "";
            error_log("Ruta foto inicial: " . $rutaFotoPerfil);
            
            if(isset($_POST["resetFoto"]) && $_POST["resetFoto"] == "1") {
                error_log("Reset foto solicitado");
                if(!empty($_SESSION["foto_perfil"]) && $_SESSION["foto_perfil"] != "vistas/assets/img/avatars/default.jpg") {
                    $rutaAbsolutaAnterior = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/";
                    if(strpos($_SESSION["foto_perfil"], "vistas/") === 0) {
                        $rutaAbsolutaAnterior .= $_SESSION["foto_perfil"];
                    } else {
                        $rutaAbsolutaAnterior .= "vistas/assets/img/usuarios/" . $_SESSION["foto_perfil"];
                    }
                    
                    error_log("Intentando eliminar archivo: " . $rutaAbsolutaAnterior);
                    if(file_exists($rutaAbsolutaAnterior)) {
                        if(unlink($rutaAbsolutaAnterior)) {
                            error_log("Archivo eliminado correctamente");
                        } else {
                            error_log("Error al eliminar archivo");
                        }
                    } else {
                        error_log("Archivo no existe en la ruta especificada");
                    }
                }
                $rutaFotoPerfil = "";
            }
            
            if(isset($_FILES["fotoPerfil"]["tmp_name"]) && !empty($_FILES["fotoPerfil"]["tmp_name"])) {
                error_log("Nueva imagen detectada");
                
                $validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if(!in_array($_FILES["fotoPerfil"]["type"], $validTypes)) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Solo se permiten archivos JPG, PNG o GIF",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                if($_FILES["fotoPerfil"]["size"] > 819200) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "La imagen no debe superar los 800KB",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                $directorioAbsoluto = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/vistas/assets/img/usuarios/";
                $directorioRelativo = "vistas/assets/img/usuarios/";
                
                if(!file_exists($directorioAbsoluto)){
                    mkdir($directorioAbsoluto, 0755, true);
                    error_log("Directorio creado: " . $directorioAbsoluto);
                }
                
                if(!empty($rutaFotoPerfil) && $rutaFotoPerfil != "vistas/assets/img/avatars/default.jpg") {
                    $rutaAbsolutaAnterior = $_SERVER['DOCUMENT_ROOT'] . "/helpdesk/";
                    if(strpos($rutaFotoPerfil, "vistas/") === 0) {
                        $rutaAbsolutaAnterior .= $rutaFotoPerfil;
                    } else {
                        $rutaAbsolutaAnterior .= "vistas/assets/img/usuarios/" . $rutaFotoPerfil;
                    }
                    
                    if(file_exists($rutaAbsolutaAnterior)) {
                        unlink($rutaAbsolutaAnterior);
                        error_log("Imagen anterior eliminada: " . $rutaAbsolutaAnterior);
                    }
                }
                
                $infoImagen = getimagesize($_FILES["fotoPerfil"]["tmp_name"]);
                if($infoImagen === false) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "El archivo no es una imagen válida",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                list($ancho, $alto) = $infoImagen;
                $nuevoAncho = 500;
                $nuevoAlto = 500;
                
                $extension = pathinfo($_FILES["fotoPerfil"]["name"], PATHINFO_EXTENSION);
                $aleatorio = mt_rand(100,999);
                $nombreArchivo = "usuario_" . $_POST["idUsuario"] . "_" . $aleatorio . "." . $extension;
                $rutaAbsoluta = $directorioAbsoluto . $nombreArchivo;
                $rutaFotoPerfil = $directorioRelativo . $nombreArchivo;
                
                error_log("Procesando imagen - Ruta absoluta: " . $rutaAbsoluta);
                error_log("Ruta relativa: " . $rutaFotoPerfil);
                
                $imagenProcesada = false;
                
                if($_FILES["fotoPerfil"]["type"] == "image/jpeg"){
                    $origen = imagecreatefromjpeg($_FILES["fotoPerfil"]["tmp_name"]);
                    if($origen !== false) {
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        $imagenProcesada = imagejpeg($destino, $rutaAbsoluta, 90);
                        imagedestroy($origen);
                        imagedestroy($destino);
                    }
                } elseif($_FILES["fotoPerfil"]["type"] == "image/png"){
                    $origen = imagecreatefrompng($_FILES["fotoPerfil"]["tmp_name"]);
                    if($origen !== false) {
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagealphablending($destino, false);
                        imagesavealpha($destino, true);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        $imagenProcesada = imagepng($destino, $rutaAbsoluta, 8);
                        imagedestroy($origen);
                        imagedestroy($destino);
                    }
                } else {
                    $imagenProcesada = move_uploaded_file($_FILES["fotoPerfil"]["tmp_name"], $rutaAbsoluta);
                }
                
                if(!$imagenProcesada) {
                    error_log("Error al procesar la imagen");
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error al procesar la imagen",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                } else {
                    error_log("Imagen procesada correctamente: " . $rutaAbsoluta);
                }
            }
            
            $password = $_POST["passActual"];
            if(!empty($_POST["editarPassword"])) {
                if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])){
                    $password = crypt($_POST["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    error_log("Nueva contraseña encriptada");
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "La contraseña no puede llevar caracteres especiales",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            $datos = array(
                "id" => $_POST["idUsuario"],
                "nombre" => $_POST["editarNombre"],
                "apellido" => $_POST["editarApellido"],
                "email" => $_POST["editarEmail"],
                "emailEncriptado" => md5($_POST["editarEmail"]),
                "documento" => $_POST["editarDocumento"] ?? "",
                "telefono" => $_POST["editarTelefono"] ?? "",
                "direccion" => $_POST["editarDireccion"] ?? "",
                "fecha_nacimiento" => $_POST["editarFecha"] ?? null,
                "password" => $password,
                "foto_perfil" => $rutaFotoPerfil
            );
            
            error_log("Datos a actualizar: " . print_r($datos, true));
            
            $respuesta = ModeloUsuarios::mdlActualizarPerfilUsuario($tabla, $datos);
            error_log("Respuesta del modelo: " . $respuesta);
            
            if($respuesta == "ok"){
                $_SESSION["nombre"] = $_POST["editarNombre"];
                $_SESSION["apellido"] = $_POST["editarApellido"];
                $_SESSION["email"] = $_POST["editarEmail"];
                $_SESSION["emailEncriptado"] = md5($_POST["editarEmail"]);
                $_SESSION["documento"] = $_POST["editarDocumento"] ?? "";
                $_SESSION["telefono"] = $_POST["editarTelefono"] ?? "";
                $_SESSION["direccion"] = $_POST["editarDireccion"] ?? "";
                $_SESSION["fecha_nacimiento"] = $_POST["editarFecha"] ?? "";
                $_SESSION["password"] = $password;
                $_SESSION["foto_perfil"] = $rutaFotoPerfil;
                
                error_log("Variables de sesión actualizadas");
                error_log("Nueva SESSION: " . print_r($_SESSION, true));
                
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Perfil actualizado!",
                        text: "Tu información ha sido actualizada correctamente",
                        confirmButtonText: "Continuar"
                    }).then(function(result){
                        if(result.value){
                            window.location.reload();
                        }
                    });
                </script>';
            } else {
                error_log("Error en la actualización de la base de datos");
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al actualizar tu perfil",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    DESACTIVAR CUENTA
    =============================================*/
    static public function ctrDesactivarCuenta(){
        if(isset($_POST["desactivarCuenta"])) {
            $tabla = "usuarios";
            $item1 = "estado";
            $valor1 = 0;
            $item2 = "id";
            $valor2 = $_POST["desactivarCuenta"];
            
            $respuesta = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);
            
            if($respuesta == "ok"){
                session_destroy();
                
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "Cuenta desactivada",
                        text: "Tu cuenta ha sido desactivada exitosamente",
                        confirmButtonText: "Continuar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "ingreso";
                        }
                    });
                </script>';
            }
        }
    }
}