<?php

require_once "conexion.php";

class ModeloUsuarios{

    /*=============================================
    MOSTRAR USUARIOS
    =============================================*/

    static public function mdlMostrarUsuarios($tabla, $item, $valor){
        try {
            if($item != null){
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
                $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt -> execute();
                $resultado = $stmt -> fetch(PDO::FETCH_ASSOC);
                $stmt = null;
                
              
                return $resultado ? $resultado : false; 
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
                $stmt -> execute();
                $resultado = $stmt -> fetchAll(PDO::FETCH_ASSOC);
                $stmt = null;
                
                return $resultado ? $resultado : [];
            }
        } catch (Exception $e) {
            error_log("Error en mdlMostrarUsuarios: " . $e->getMessage());
            return $item != null ? false : [];
        }
    }

    /*=============================================
    CONTAR USUARIOS
    =============================================*/

    static public function mdlContarUsuarios($tabla, $item, $valor){
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) AS total FROM $tabla WHERE $item = :$item");
        $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
        $stmt -> execute();
        $resultado = $stmt -> fetch();
        return $resultado["total"];
        
        $stmt -> close();
        $stmt = null;
    }

    /*=============================================
    REGISTRO DE USUARIO
    =============================================*/

    static public function mdlIngresarUsuario($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, apellido, usuario, documento, email, emailEncriptado, telefono, direccion, fecha_nacimiento, password, perfil, foto_perfil, estado) VALUES (:nombre, :apellido, :usuario, :documento, :email, :emailEncriptado, :telefono, :direccion, :fecha_nacimiento, :password, :perfil, :foto_perfil, :estado)");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
        $stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":emailEncriptado", $datos["emailEncriptado"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
        $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
        $stmt->bindParam(":foto_perfil", $datos["foto_perfil"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
        
        if($stmt->execute()){
            return "ok";	
        }else{
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    EDITAR USUARIO
    =============================================*/

    static public function mdlEditarUsuario($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, apellido = :apellido, documento = :documento, email = :email, telefono = :telefono, direccion = :direccion, fecha_nacimiento = :fecha_nacimiento, password = :password, perfil = :perfil, foto_perfil = :foto_perfil WHERE id = :id");

        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
        $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
        $stmt->bindParam(":foto_perfil", $datos["foto_perfil"], PDO::PARAM_STR);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";	
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    ACTUALIZAR USUARIO
    =============================================*/

    static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2){
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");
        $stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
        $stmt -> bindParam(":".$item2, $valor2, PDO::PARAM_STR);

        if($stmt -> execute()){
            return "ok";
        }else{
            return "error";	
        }

        $stmt -> close();
        $stmt = null;
    }

    /*=============================================
    BORRAR USUARIO
    =============================================*/

    static public function mdlBorrarUsuario($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

        if($stmt -> execute()){
            return "ok";
        }else{
            return "error";	
        }

        $stmt -> close();
        $stmt = null;
    }

    /*=============================================
    MOSTRAR USUARIOS POR ROL
    =============================================*/

    static public function mdlMostrarUsuariosPorRol($tabla, $rol) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE rol = :rol");
        $stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR USUARIOS POR TIPO DE PERFIL
    =============================================*/

    static public function mdlMostrarUsuariosPorTipoPerfil($tabla, $tipoPerfil) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE tipo_perfil = :tipo_perfil");
        $stmt->bindParam(":tipo_perfil", $tipoPerfil, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR USUARIOS POR PERFIL
    =============================================*/

    static public function mdlMostrarUsuariosPorPerfil($tabla, $perfil) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE perfil = :perfil");
        $stmt->bindParam(":perfil", $perfil, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    ACTUALIZAR PERFIL DE USUARIO
    =============================================*/
    static public function mdlActualizarPerfilUsuario($tabla, $datos){
        try {
            error_log("=== DEBUG MODELO ACTUALIZAR PERFIL ===");
            error_log("Tabla: " . $tabla);
            error_log("Datos recibidos: " . print_r($datos, true));
            
            $sql = "UPDATE $tabla SET 
                    nombre = :nombre, 
                    apellido = :apellido, 
                    email = :email, 
                    emailEncriptado = :emailEncriptado, 
                    documento = :documento, 
                    telefono = :telefono, 
                    direccion = :direccion, 
                    fecha_nacimiento = :fecha_nacimiento, 
                    password = :password, 
                    foto_perfil = :foto_perfil 
                    WHERE id = :id";
                    
            error_log("SQL Query: " . $sql);
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":emailEncriptado", $datos["emailEncriptado"], PDO::PARAM_STR);
            $stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
            $stmt->bindParam(":foto_perfil", $datos["foto_perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            
            error_log("Parámetros vinculados");
            
            $resultado = $stmt->execute();
            
            if($resultado){
                $filasAfectadas = $stmt->rowCount();
                error_log("Query ejecutada correctamente. Filas afectadas: " . $filasAfectadas);
                return "ok";
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error en la ejecución: " . print_r($errorInfo, true));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Excepción PDO en mdlActualizarPerfilUsuario: " . $e->getMessage());
            return "error";
        } finally {
            if(isset($stmt)){
                $stmt = null;
            }
        }
    }
}