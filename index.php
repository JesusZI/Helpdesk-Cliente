<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";

require_once "controladores/prioridades.controlador.php";
require_once "controladores/departamentos.controlador.php";
require_once "controladores/tickets.controlador.php";
require_once "controladores/historiales.controlador.php";
require_once "controladores/faq.controlador.php";
require_once "controladores/comentarios.controlador.php";
require_once "controladores/archivos.controlador.php";
require_once "controladores/interfaz.controlador.php";

require_once "modelos/interfaz.modelo.php";
require_once "modelos/faq.modelo.php";
require_once "modelos/historiales.modelo.php";
require_once "modelos/tickets.modelo.php";
require_once "modelos/departamentos.modelo.php";
require_once "modelos/prioridades.modelo.php";
require_once "modelos/comentarios.modelo.php";
require_once "modelos/archivos.modelo.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/rutas.php";

require_once "extensiones/vendor/autoload.php";


require_once "extensiones/PHPMailer/PHPMailerAutoload.php";

$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();