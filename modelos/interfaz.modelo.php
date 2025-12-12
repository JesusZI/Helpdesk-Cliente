<?php

require_once "conexion.php";

class ModeloInterfaz{


static public function mdlSeleccionarPlantilla($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}





}