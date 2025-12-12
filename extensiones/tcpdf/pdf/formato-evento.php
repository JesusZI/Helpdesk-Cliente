<?php

require_once "../../../controladores/citas.controlador.php";
require_once "../../../modelos/citas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/iglesias.controlador.php";
require_once "../../../modelos/iglesias.modelo.php";

require_once "../../../controladores/estados.controlador.php";
require_once "../../../modelos/estados.modelo.php";

require_once "../../../controladores/municipios.controlador.php";
require_once "../../../modelos/municipios.modelo.php";
require_once "../../../controladores/parroquias.controlador.php";
require_once "../../../modelos/parroquias.modelo.php";


class imprimirCita{

public $codigo;

public function traerImpresionCita(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemVenta = "id";
$valorVenta = $this->codigo;

$respuestaVenta = ControladorCitas::ctrMostrarCitas($itemVenta, $valorVenta);



//TRAEMOS LA INFORMACIÓN DEL CLIENTE



$itemVendedor = "id";
$valorVendedor = $respuestaVenta["id_cliente"];

$respuestaVendedor = ControladorClientes::ctrMostrarClientes($itemVendedor, $valorVendedor);

 $itemM = "id";
                  $valorM = $respuestaVenta["id_iglesia"];

                  $respuestaM = ControladorIglesias::ctrMostrarIglesias($itemM, $valorM);

                  $itemEstado = "id";
                 $valorEstado = $respuestaVenta["id_estado"];

                 $respuestaEstado = ControladorEstados::ctrMostrarEstados($itemEstado, $valorEstado);

                 $itemMunicipio = "id";
                 $valorMunicipio = $respuestaVenta["id_municipio"];

                 $respuestaMunicipio = ControladorMunicipios::ctrMostrarMunicipios($itemMunicipio, $valorMunicipio);

                 $itemParroquia = "id";
                 $valorParroquia = $respuestaVenta["id_parroquia"];

                 $respuestaParroquia = ControladorParroquias::ctrMostrarParroquias($itemParroquia, $valorParroquia);

                  


//REQUERIMOS LA CLASE TCPDF


require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage('P', 'A4');

// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table style="border: -20px solid #FFF; background-color:white;">
		
		<tr style=" background-color:white;">
			
			<td style=" border: -1px solid #FFF; background-color:white; width:157px"><img src="images/logo.png"></td>
<td style="border: 0px solid #FFF; background-color:white; width:157px"></td><td style=" background-color:white; width:50px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:157px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:265px"></td>
			

			

			

		</tr>

	</table>
	<p style="text-align:center">FORMATO N° $respuestaVenta[id]</p>
<p style="text-align:center">Registro de Actividades</p>
	<br>


EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');


// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:540px; height:25px; text-align:left">NOMBRE Y APELLIDO DEL RESPONSABLE:  &nbsp; $respuestaVendedor[nombre]</td>


		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');


// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:540px; height:25px; text-align:left">IGLESIA:  &nbsp; $respuestaM[nombre]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

// ---------------------------------------------------------


$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">TELEFONO:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaM[telefono]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

// ---------------------------------------------------------

$bloque5 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">ESTADO:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaEstado[nombre]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');

// ---------------------------------------------------------


$bloque6 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">MUNICIPIO:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaMunicipio[nombre]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

// ---------------------------------------------------------

$bloque7 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">PARROQUIA:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaParroquia[nombre]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque7, false, false, false, false, '');

// ---------------------------------------------------------



$bloque8 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">FECHA:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaVenta[fecha_evento]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque8, false, false, false, false, '');

// ---------------------------------------------------------





$bloque8 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">HORA:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaVenta[hora_evento]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque8, false, false, false, false, '');

// ---------------------------------------------------------


$bloque9 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">LUGAR:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaVenta[lugar]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque9, false, false, false, false, '');

// ---------------------------------------------------------

$bloque10 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:140px; height:25px; text-align:left">DESCRIPCION DE LA ACTIVIDAD:</td>
		<td style="border: 1px solid #666; background-color:white; width:400px; height:25px; text-align:left">&nbsp; $respuestaVenta[descripcion]</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque10, false, false, false, false, '');

// ---------------------------------------------------------


$bloque11 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:540px; height:25px; text-align:left">REGISTRO FOTOGRAFICO</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque11, false, false, false, false, '');

// ---------------------------------------------------------


$bloque11 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:540px; height:300px; text-align:center"><img src="../../../$respuestaVenta[registro_foto]"></td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque11, false, false, false, false, '');

// ---------------------------------------------------------


$bloque12 = <<<EOF

		<br>
		
		<p style="background-color:white; width:1px; height:25px; text-align:center">Correo: asuntosreligiosos.secretaria@gmail.com</p>


	

EOF;

$pdf->writeHTML($bloque12, false, false, false, false, '');

// ---------------------------------------------------------

// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('factura-cita.pdf');

}

}

$factura = new imprimirCita();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionCita();

?>