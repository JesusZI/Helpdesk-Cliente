<?php

require_once "conexion.php";

class ModeloFaqs{

    /*=============================================
    MOSTRAR FAQS
    =============================================*/

    static public function mdlMostrarFaqs($tabla, $item, $valor){

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
        
        $stmt = null;
    }

    /*=============================================
    CREAR FAQ
    =============================================*/

    static public function mdlIngresarFaq($tabla, $datos){
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(pregunta, respuesta, categoria_id) VALUES (:pregunta, :respuesta, :categoria_id)");
    
            $stmt->bindParam(":pregunta", $datos["pregunta"], PDO::PARAM_STR);
            $stmt->bindParam(":respuesta", $datos["respuesta"], PDO::PARAM_STR);
            $stmt->bindParam(":categoria_id", $datos["categoria_id"], PDO::PARAM_INT);
    
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
    EDITAR FAQ
    =============================================*/

    static public function mdlEditarFaq($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET pregunta = :pregunta, respuesta = :respuesta, categoria_id = :categoria_id WHERE id = :id");

        $stmt->bindParam(":pregunta", $datos["pregunta"], PDO::PARAM_STR);
        $stmt->bindParam(":respuesta", $datos["respuesta"], PDO::PARAM_STR);
        $stmt->bindParam(":categoria_id", $datos["categoria_id"], PDO::PARAM_INT);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";
        }

        $stmt = null;
    }

    /*=============================================
    BORRAR FAQ
    =============================================*/

    static public function mdlBorrarFaq($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";
        }

        $stmt = null;
    }
}
