<?php

class ControladorInterfaz{





	static public function ctrSeleccionarPlantilla(){

		$tabla = "contact";

		$respuesta = ModeloInterfaz::mdlSeleccionarPlantilla($tabla);

		return $respuesta;

	}




	}