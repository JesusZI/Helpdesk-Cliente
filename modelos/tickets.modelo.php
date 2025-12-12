<?php

require_once "conexion.php";

class ModeloTickets {

    /*=============================================
    MOSTRAR TICKETS
    =============================================*/
    static public function mdlMostrarTickets($tabla, $item, $valor) {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    CREAR TICKET
    =============================================*/
    static public function mdlIngresarTicket($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO $tabla
                (titulo, descripcion, usuario_creador_id, tecnico_asignado_id, categoria_id, prioridad_id, estado, fecha_creacion, fecha_actualizacion)
                VALUES (:titulo, :descripcion, :usuario_creador_id, :tecnico_asignado_id, :categoria_id, :prioridad_id, :estado, NOW(), NOW())"
            );

            $stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario_creador_id", $datos["usuario_creador_id"], PDO::PARAM_INT);
            $stmt->bindParam(":tecnico_asignado_id", $datos["tecnico_asignado_id"], PDO::PARAM_INT);
            $stmt->bindParam(":categoria_id", $datos["categoria_id"], PDO::PARAM_INT);
            $stmt->bindParam(":prioridad_id", $datos["prioridad_id"], PDO::PARAM_INT);
            $estado = isset($datos["estado"]) && $datos["estado"] ? $datos["estado"] : 'abierto';
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
        $stmt = null;
    }

    /*=============================================
    EDITAR TICKET
    =============================================*/
    static public function mdlEditarTicket($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET
                titulo = :titulo,
                descripcion = :descripcion,
                tecnico_asignado_id = :tecnico_asignado_id,
                categoria_id = :categoria_id,
                prioridad_id = :prioridad_id,
                estado = :estado,
                fecha_actualizacion = NOW()
            WHERE id = :id"
        );

        $stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":tecnico_asignado_id", $datos["tecnico_asignado_id"], PDO::PARAM_INT);
        $stmt->bindParam(":categoria_id", $datos["categoria_id"], PDO::PARAM_INT);
        $stmt->bindParam(":prioridad_id", $datos["prioridad_id"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    BORRAR TICKET
    =============================================*/
    static public function mdlBorrarTicket($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
}