<?php


require_once __DIR__ . "/../modelos/archivos.modelo.php";

class ControladorComentarios {

    /*=============================================
    MOSTRAR COMENTARIOS
    =============================================*/
    static public function ctrMostrarComentarios($item, $valor) {
        $tabla = "comentarios";
        $respuesta = ModeloComentarios::mdlMostrarComentarios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    CREAR COMENTARIO
    =============================================*/
    static public function ctrCrearComentario($datos) {
        try {
          
            if (empty($datos["ticket_id"]) || empty($datos["usuario_id"]) || empty($datos["contenido"])) {
                return "error: Faltan datos obligatorios";
            }

          
            $tablaTickets = "tickets";
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT id FROM $tablaTickets WHERE id = :id");
            $stmt->bindParam(":id", $datos["ticket_id"], PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return "error: El ticket no existe";
            }
            
            $tabla = "comentarios";

            $idComentario = ModeloComentarios::mdlIngresarComentario($tabla, $datos);
            
          
            if (!is_numeric($idComentario) || $idComentario <= 0) {
                error_log("Error al crear comentario: " . $idComentario);
                return $idComentario; 
            }

            error_log(" Comentario creado correctamente con ID: " . $idComentario);

         
            if (isset($datos["archivo"])) {
                $tablaArchivos = "archivos";
                
                try {
                 
                    $conexion = Conexion::conectar();
                    
                   
                    $checkStmt = $conexion->prepare("SHOW TABLES LIKE 'archivos'");
                    $checkStmt->execute();
                    
                    if ($checkStmt->rowCount() == 0) {
                        error_log(" La tabla 'archivos' no existe en la base de datos");
                        return "error: La tabla 'archivos' no existe";
                    }
                    
                 
                    $columnasStmt = $conexion->prepare("DESCRIBE archivos");
                    $columnasStmt->execute();
                    $columnas = $columnasStmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    error_log("Columnas en tabla archivos: " . implode(", ", $columnas));
                    
                    
                    $datosArchivo = array(
                        "ticket_id" => (int)$datos["ticket_id"],
                        "comentario_id" => (int)$idComentario,
                        "nombre" => $datos["archivo"]["nombre"],
                        "ruta" => $datos["archivo"]["ruta"],
                        "tipo" => $datos["archivo"]["tipo"],
                        "tamano" => (int)$datos["archivo"]["tamano"], 
                        "usuario_id" => (int)$datos["usuario_id"]
                    );
                    
                    error_log("Enviando datos del archivo a la BD: " . print_r($datosArchivo, true));
                    
                 
                    $respuestaArchivo = ModeloArchivos::mdlSubirArchivo($tablaArchivos, $datosArchivo);
                    
                    if ($respuestaArchivo == "ok") {
                        error_log(" Archivo registrado correctamente en BD");
                    } else {
                        error_log(" Error al guardar archivo en BD: " . $respuestaArchivo);
                    }
                } catch (Exception $e) {
                    error_log(" Excepción al guardar archivo: " . $e->getMessage());
                }
            }

            return "ok";
        } catch (Exception $e) {
            error_log("Error en ctrCrearComentario: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
}
