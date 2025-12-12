<?php

class ControladorPrioridades{

    /*=============================================
    MOSTRAR PRIORIDADES
    =============================================*/

    static public function ctrMostrarPrioridades($item, $valor){

        $tabla = "prioridades";

        $respuesta = ModeloPrioridades::mdlMostrarPrioridades($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    CREAR PRIORIDAD
    =============================================*/

    static public function ctrCrearPrioridad(){

        if(isset($_POST["nuevaPrioridad"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaPrioridad"])){

                $tabla = "prioridades";

                $datos = array(
                    "nombre" => $_POST["nuevaPrioridad"],
                    "tiempo_respuesta" => $_POST["nuevoTiempoRespuesta"],
                    "color" => $_POST["nuevoColor"]
                );

                $respuesta = ModeloPrioridades::mdlIngresarPrioridad($tabla, $datos);

                if($respuesta == "ok"){

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La prioridad ha sido guardada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "prioridades";
                            }
                        });
                    </script>';
                }

            }else{

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡La prioridad no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "prioridades";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR PRIORIDAD
    =============================================*/

    static public function ctrEditarPrioridad(){

        if(isset($_POST["editarPrioridad"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarPrioridad"])){

                $tabla = "prioridades";

                $datos = array(
                    "id" => $_POST["idPrioridad"],
                    "nombre" => $_POST["editarPrioridad"],
                    "tiempo_respuesta" => $_POST["editarTiempoRespuesta"],
                    "color" => $_POST["editarColor"]
                );

                $respuesta = ModeloPrioridades::mdlEditarPrioridad($tabla, $datos);

                if($respuesta == "ok"){

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La prioridad ha sido editada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "prioridades";
                            }
                        });
                    </script>';
                }

            }else{

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡La prioridad no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "prioridades";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    BORRAR PRIORIDAD
    =============================================*/

    static public function ctrBorrarPrioridad(){

        if(isset($_GET["idPrioridad"])){

            $tabla = "prioridades";
            $datos = $_GET["idPrioridad"];

            $respuesta = ModeloPrioridades::mdlBorrarPrioridad($tabla, $datos);

            if($respuesta == "ok"){

                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡La prioridad ha sido borrada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "prioridades";
                        }
                    });
                </script>';
            }
        }
    }
}