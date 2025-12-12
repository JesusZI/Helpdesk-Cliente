<?php

require_once "conexion.php";

class ModeloArchivos {

    /*=============================================
    MOSTRAR ARCHIVOS
    =============================================*/
    static public function mdlMostrarArchivos($tabla, $item, $valor) {
        try {
            if ($item != null) {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
                $stmt->bindParam(":" . $item, $valor, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarArchivos: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    SUBIR ARCHIVO
    =============================================*/
    static public function mdlSubirArchivo($tabla, $datos) {
        try {
           
            if (!isset($datos['ticket_id']) || !isset($datos['comentario_id']) || 
                !isset($datos['nombre']) || !isset($datos['ruta']) || 
                !isset($datos['tipo']) || !isset($datos['usuario_id'])) {
                error_log("Faltan datos para subir archivo: " . print_r($datos, true));
                return "error: Datos incompletos para subir archivo";
            }
            
           
            $conn = Conexion::conectar();
            
           
            $sql = "INSERT INTO $tabla (ticket_id, comentario_id, nombre, ruta, tipo, tamano, fecha_subida, usuario_id) 
                    VALUES (:ticket_id, :comentario_id, :nombre, :ruta, :tipo, :tamano, NOW(), :usuario_id)";
            
            $stmt = $conn->prepare($sql);
            
            error_log("SQL preparado: " . $sql);
            
           
            $ticketId = (int)$datos["ticket_id"];
            $comentarioId = (int)$datos["comentario_id"];
            $usuarioId = (int)$datos["usuario_id"];
            
           
            $tamano = isset($datos["tamano"]) ? (int)$datos["tamano"] : 0;
            
            
            error_log("Valores para inserción: " . 
                      "ticket_id=$ticketId, " . 
                      "comentario_id=$comentarioId, " . 
                      "nombre={$datos['nombre']}, " . 
                      "ruta={$datos['ruta']}, " . 
                      "tipo={$datos['tipo']}, " . 
                      "tamano=$tamano, " . 
                      "usuario_id=$usuarioId");
            
            $stmt->bindParam(":ticket_id", $ticketId, PDO::PARAM_INT);
            $stmt->bindParam(":comentario_id", $comentarioId, PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":ruta", $datos["ruta"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":tamano", $tamano, PDO::PARAM_INT);
            $stmt->bindParam(":usuario_id", $usuarioId, PDO::PARAM_INT);
            
            
            if ($stmt->execute()) {
                $lastId = $conn->lastInsertId();
                error_log(" Archivo registrado correctamente en BD con ID: $lastId");
                return "ok";
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log(" Error al insertar archivo en BD: " . implode(", ", $errorInfo));
                return "error: " . implode(", ", $errorInfo);
            }
        } catch (PDOException $e) {
            error_log("Error PDO en mdlSubirArchivo: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Error general en mdlSubirArchivo: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }

    /*=============================================
    ELIMINAR ARCHIVO
    =============================================*/
    static public function mdlEliminarArchivo($tabla, $idArchivo) {
        try {
          
            $stmt = Conexion::conectar()->prepare("SELECT ruta FROM $tabla WHERE id = :id");
            $stmt->bindParam(":id", $idArchivo, PDO::PARAM_INT);
            $stmt->execute();
            $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
            $stmt->bindParam(":id", $idArchivo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                
                if ($archivo && file_exists($archivo['ruta'])) {
                    unlink($archivo['ruta']);
                }
                return "ok";
            } else {
                error_log("Error al eliminar archivo: " . implode(", ", $stmt->errorInfo()));
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarArchivo: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
}
