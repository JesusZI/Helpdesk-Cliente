<?php

require_once "conexion.php";

class ModeloPrioridades{

    /*=============================================
    MOSTRAR PRIORIDADES
    =============================================*/

    static public function mdlMostrarPrioridades($tabla, $item, $valor){

        if($item != null){

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

            $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

            $stmt -> execute();

            return $stmt -> fetch();

        }else{

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

            $stmt -> execute();

            return $stmt -> fetchAll();

        }
        
        $stmt -> close();
        $stmt = null;
    }

    /*=============================================
    CREAR PRIORIDAD
    =============================================*/

    static public function mdlIngresarPrioridad($tabla, $datos){
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, tiempo_respuesta, color) VALUES (:nombre, :tiempo_respuesta, :color)");
    
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":tiempo_respuesta", $datos["tiempo_respuesta"], PDO::PARAM_INT);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
    
            if($stmt->execute()){
                return "ok";
            } else {
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch(PDOException $e) {
            return "error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    EDITAR PRIORIDAD
    =============================================*/

    static public function mdlEditarPrioridad($tabla, $datos){
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, tiempo_respuesta = :tiempo_respuesta, color = :color WHERE id = :id");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":tiempo_respuesta", $datos["tiempo_respuesta"], PDO::PARAM_INT);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

            if($stmt->execute()){
                return "ok";
            } else {
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch(PDOException $e) {
            return "error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    BORRAR PRIORIDAD
    =============================================*/

    static public function mdlBorrarPrioridad($tabla, $datos){
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
            $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

            if($stmt->execute()){
                return "ok";
            } else {
                return "error: " . implode(", ", $stmt->errorInfo());
            }
        } catch(PDOException $e) {
            return "error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
}