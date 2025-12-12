<?php

class ControladorFaqs{

    /*=============================================
    MOSTRAR FAQS
    =============================================*/

    static public function ctrMostrarFaqs($item, $valor){

        $tabla = "faqs";

        $respuesta = ModeloFaqs::mdlMostrarFaqs($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    CREAR FAQ
    =============================================*/

    static public function ctrCrearFaq(){
        if(isset($_POST["nuevaPregunta"])){
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ .,¿?!¡]+$/', $_POST["nuevaPregunta"])){
                $tabla = "faqs";

                $datos = array(
                    "pregunta" => $_POST["nuevaPregunta"],
                    "respuesta" => $_POST["nuevaRespuesta"],
                    "categoria_id" => $_POST["nuevaCategoriaFaq"]
                );

                $respuesta = ModeloFaqs::mdlIngresarFaq($tabla, $datos);

                if($respuesta == "ok"){
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La pregunta frecuente ha sido guardada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "faq";
                            }
                        });
                    </script>';
                }
            }else{
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡La pregunta no puede ir vacía o llevar caracteres especiales no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "faq";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR FAQ
    =============================================*/

    static public function ctrEditarFaq(){

        if(isset($_POST["editarPregunta"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ .,¿?!¡]+$/', $_POST["editarPregunta"])){

                $tabla = "faqs";

                $datos = array(
                    "id" => $_POST["idFaq"],
                    "pregunta" => $_POST["editarPregunta"],
                    "respuesta" => $_POST["editarRespuesta"],
                    "categoria_id" => $_POST["editarCategoriaFaq"]
                );

                $respuesta = ModeloFaqs::mdlEditarFaq($tabla, $datos);

                if($respuesta == "ok"){

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La pregunta frecuente ha sido editada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "faq";
                            }
                        });
                    </script>';
                }

            }else{

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡La pregunta no puede ir vacía o llevar caracteres especiales no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "faq";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    BORRAR FAQ
    =============================================*/

    static public function ctrBorrarFaq(){

        if(isset($_GET["idFaq"])){

            $tabla = "faqs";
            $datos = $_GET["idFaq"];

            $respuesta = ModeloFaqs::mdlBorrarFaq($tabla, $datos);

            if($respuesta == "ok"){

                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡La pregunta frecuente ha sido eliminada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "faq";
                        }
                    });
                </script>';
            }
        }
    }
}
