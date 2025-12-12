<?php

require_once "conexion.php";

class ModeloHistoriales {

    /*=============================================
    MOSTRAR HISTORIALES
    =============================================*/
    static public function mdlMostrarHistoriales($tabla, $fechaDesde = null, $fechaHasta = null, $usuarioId = null, $ticketId = null, $accion = null) {
        try {
         
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = array();
            
           
            if ($fechaDesde !== null) {
                $sql .= " AND DATE(fecha) >= :fechaDesde";
                $params[':fechaDesde'] = $fechaDesde;
            }
            
            if ($fechaHasta !== null) {
                $sql .= " AND DATE(fecha) <= :fechaHasta";
                $params[':fechaHasta'] = $fechaHasta;
            }
            
            if ($usuarioId !== null) {
                $sql .= " AND usuario_id = :usuarioId";
                $params[':usuarioId'] = $usuarioId;
            }
            
            if ($ticketId !== null) {
                $sql .= " AND ticket_id = :ticketId";
                $params[':ticketId'] = $ticketId;
            }
            
            if ($accion !== null) {
                $sql .= " AND accion LIKE :accion";
                $params[':accion'] = '%' . $accion . '%';
            }
            
           
            $sql .= " ORDER BY fecha DESC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
           
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarHistoriales: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }
    
    /*=============================================
    REGISTRAR HISTORIAL
    =============================================*/
    static public function mdlRegistrarHistorial($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(ticket_id, usuario_id, accion, detalles, fecha) VALUES (:ticket_id, :usuario_id, :accion, :detalles, NOW())");
            
            $stmt->bindParam(":ticket_id", $datos["ticket_id"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
            $stmt->bindParam(":accion", $datos["accion"], PDO::PARAM_STR);
            $stmt->bindParam(":detalles", $datos["detalles"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarHistorial: " . $e->getMessage());
            return "error: " . $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt = null;
        }
    }
}
