<?php

require_once "conexion.php";

class ModeloComentarios {

    /*=============================================
    MOSTRAR COMENTARIOS
    =============================================*/
    static public function mdlMostrarComentarios($tabla, $item, $valor) {
        try {
            if ($item != null) {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY fecha_creacion DESC");
                $stmt->bindParam(":" . $item, $valor, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY fecha_creacion DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarComentarios: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    /*=============================================
    CREAR COMENTARIO
    =============================================*/
    static public function mdlIngresarComentario($tabla, $datos) {
        try {
           
            $conn = Conexion::conectar();
            $checkStmt = $conn->prepare("SELECT id FROM $tabla WHERE ticket_id = :ticket_id AND usuario_id = :usuario_id AND contenido = :contenido AND fecha_creacion > DATE_SUB(NOW(), INTERVAL 10 SECOND)");
            
            $checkStmt->bindParam(":ticket_id", $datos["ticket_id"], PDO::PARAM_INT);
            $checkStmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
            $checkStmt->bindParam(":contenido", $datos["contenido"], PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                error_log("Intento de comentario duplicado detectado");
                return "error: Ya existe un comentario igual reciente";
            }
            
            
            $stmt = $conn->prepare("INSERT INTO $tabla(ticket_id, usuario_id, contenido, es_privado, fecha_creacion) VALUES (:ticket_id, :usuario_id, :contenido, :es_privado, NOW())");

            $stmt->bindParam(":ticket_id", $datos["ticket_id"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
            $stmt->bindParam(":contenido", $datos["contenido"], PDO::PARAM_STR);
            $stmt->bindParam(":es_privado", $datos["es_privado"], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $conn->lastInsertId(); 
            } else {
                error_log("Error al insertar comentario: " . implode(", ", $stmt->errorInfo()));
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            error_log("Error PDO en mdlIngresarComentario: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt = null;
            if (isset($checkStmt)) $checkStmt = null;
        }
    }
}
