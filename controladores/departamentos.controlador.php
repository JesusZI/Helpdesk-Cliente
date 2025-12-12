<?php

class ControladorDepartamentos{

    /*=============================================
    MOSTRAR DEPARTAMENTOS
    =============================================*/

    static public function ctrMostrarDepartamentos($item, $valor){

        $tabla = "departamentos";

        $respuesta = ModeloDepartamentos::mdlMostrarDepartamentos($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    CREAR DEPARTAMENTO
    =============================================*/

    static public function ctrCrearDepartamento(){

        if(isset($_POST["nuevoDepartamento"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoDepartamento"])){

                $tabla = "departamentos";

                $datos = array(
                    "nombre" => $_POST["nuevoDepartamento"],
                    "descripcion" => $_POST["nuevaDescripcion"]
                );

                $respuesta = ModeloDepartamentos::mdlIngresarDepartamento($tabla, $datos);

                if($respuesta == "ok"){

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡El departamento ha sido guardado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "departamentos";
                            }
                        });
                    </script>';
                }

            }else{

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El departamento no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "departamentos";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR DEPARTAMENTO
    =============================================*/

    static public function ctrEditarDepartamento(){

        if(isset($_POST["editarDepartamento"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDepartamento"])){

                $tabla = "departamentos";

                $datos = array(
                    "id" => $_POST["idDepartamento"],
                    "nombre" => $_POST["editarDepartamento"],
                    "descripcion" => $_POST["editarDescripcion"]
                );

                $respuesta = ModeloDepartamentos::mdlEditarDepartamento($tabla, $datos);

                if($respuesta == "ok"){

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡El departamento ha sido editado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "departamentos";
                            }
                        });
                    </script>';
                }

            }else{

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El departamento no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "departamentos";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    BORRAR DEPARTAMENTO
    =============================================*/

    static public function ctrBorrarDepartamento(){

        if(isset($_GET["idDepartamento"])){

            $tabla = "departamentos";
            $datos = $_GET["idDepartamento"];

            $respuesta = ModeloDepartamentos::mdlBorrarDepartamento($tabla, $datos);

            if($respuesta == "ok"){

                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El departamento ha sido borrado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "departamentos";
                        }
                    });
                </script>';
            }
        }
    }
}