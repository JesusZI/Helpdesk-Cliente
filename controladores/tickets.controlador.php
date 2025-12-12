<?php

class ControladorTickets {

    /*=============================================
    MOSTRAR TICKETS
    =============================================*/
    static public function ctrMostrarTickets($item, $valor) {
        $tabla = "tickets";
        $respuesta = ModeloTickets::mdlMostrarTickets($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    CREAR TICKET
    =============================================*/
    static public function ctrCrearTicket() {
        if (isset($_POST["nuevoTitulo"])) {
            $tabla = "tickets";

            $datos = array(
                "titulo" => $_POST["nuevoTitulo"],
                "descripcion" => $_POST["nuevaDescripcion"],
                "usuario_creador_id" => isset($_POST["usuarioCreadorId"]) ? $_POST["usuarioCreadorId"] : null,
                "tecnico_asignado_id" => isset($_POST["tecnicoAsignadoId"]) ? $_POST["tecnicoAsignadoId"] : null,
                "categoria_id" => isset($_POST["categoriaId"]) ? $_POST["categoriaId"] : null,
                "prioridad_id" => isset($_POST["prioridadId"]) ? $_POST["prioridadId"] : null,
                "estado" => isset($_POST["estado"]) ? $_POST["estado"] : 'abierto'
            );

            if (is_null($datos["usuario_creador_id"]) || is_null($datos["tecnico_asignado_id"]) || is_null($datos["categoria_id"]) || is_null($datos["prioridad_id"])) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Todos los campos son obligatorios.",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $respuesta = ModeloTickets::mdlIngresarTicket($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El ticket ha sido creado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "tickets";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR TICKET
    =============================================*/
    static public function ctrEditarTicket() {
        if (isset($_POST["editarTitulo"])) {
            $tabla = "tickets";

            $datos = array(
                "id" => $_POST["idTicket"],
                "titulo" => $_POST["editarTitulo"],
                "descripcion" => $_POST["editarDescripcion"],
                "tecnico_asignado_id" => isset($_POST["tecnicoAsignadoId"]) ? $_POST["tecnicoAsignadoId"] : null,
                "categoria_id" => isset($_POST["categoriaId"]) ? $_POST["categoriaId"] : null,
                "prioridad_id" => isset($_POST["prioridadId"]) ? $_POST["prioridadId"] : null,
                "estado" => isset($_POST["editarEstado"]) ? $_POST["editarEstado"] : 'abierto'
            );

            if (is_null($datos["tecnico_asignado_id"]) || is_null($datos["categoria_id"]) || is_null($datos["prioridad_id"])) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Todos los campos son obligatorios.",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $respuesta = ModeloTickets::mdlEditarTicket($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El ticket ha sido editado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "tickets";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    BORRAR TICKET
    =============================================*/
    static public function ctrBorrarTicket() {
        if (isset($_GET["idTicket"])) {
            $tabla = "tickets";
            $datos = $_GET["idTicket"];

            $respuesta = ModeloTickets::mdlBorrarTicket($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        type: "success",
                        title: "¡El ticket ha sido borrado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "tickets";
                        }
                    });
                </script>';
            }
        }
    }
}