<?php

require_once "conexion.php";

class ModeloCategorias{

    /*=============================================
    MOSTRAR CATEGORIAS
    =============================================*/

    static public function mdlMostrarCategorias($tabla, $item, $valor){

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
    CREAR CATEGORIA
    =============================================*/

    static public function mdlIngresarCategoria($tabla, $datos){
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, descripcion, color, icono) VALUES (:nombre, :descripcion, :color, :icono)");
    
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
            $stmt->bindParam(":icono", $datos["icono"], PDO::PARAM_STR);
    
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
    EDITAR CATEGORIA
    =============================================*/

    static public function mdlEditarCategoria($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, descripcion = :descripcion, color = :color, icono = :icono WHERE id = :id");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
        $stmt->bindParam(":icono", $datos["icono"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    BORRAR CATEGORIA
    =============================================*/

    static public function mdlBorrarCategoria($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
}