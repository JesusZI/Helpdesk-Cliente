<?php

class ControladorArchivos {

    /*=============================================
    MOSTRAR ARCHIVOS
    =============================================*/
    static public function ctrMostrarArchivos($item, $valor) {
        $tabla = "archivos";
        $respuesta = ModeloArchivos::mdlMostrarArchivos($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    SUBIR ARCHIVO
    =============================================*/
    static public function ctrSubirArchivo() {
        if (isset($_FILES["archivo"]["name"])) {
            $tabla = "archivos";

            $ruta = "archivos/" . basename($_FILES["archivo"]["name"]);
            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $ruta)) {
                $datos = array(
                    "ticket_id" => $_POST["ticketId"],
                    "comentario_id" => null,
                    "nombre" => $_FILES["archivo"]["name"],
                    "ruta" => $ruta,
                    "tipo" => $_FILES["archivo"]["type"],
                    "tamaño" => $_FILES["archivo"]["size"] / 1024, 
                    "usuario_id" => $_SESSION["id"]
                );

                $respuesta = ModeloArchivos::mdlSubirArchivo($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡El archivo ha sido subido correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?ruta=archivos&idTicket=' . $_POST["ticketId"] . '";
                            }
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "No se pudo subir el archivo.",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ELIMINAR ARCHIVO
    =============================================*/
    static public function ctrEliminarArchivo() {
        if (isset($_POST["idArchivo"])) {
            $tabla = "archivos";
            $idArchivo = $_POST["idArchivo"];

            $archivo = ModeloArchivos::mdlMostrarArchivos($tabla, "id", $idArchivo);

            if (unlink($archivo["ruta"])) {
                $respuesta = ModeloArchivos::mdlEliminarArchivo($tabla, $idArchivo);

                if ($respuesta == "ok") {
                    echo "ok";
                } else {
                    echo "error";
                }
            } else {
                echo "error";
            }
        }
    }
}
