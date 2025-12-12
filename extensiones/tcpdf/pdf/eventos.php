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




class imprimirEventos{

public $codigo;

public function traerImpresionEventos(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemVenta = null;
$valorVenta = null;

$usuarios = ControladorCitas::ctrMostrarCitas($itemVenta, $valorVenta);
  date_default_timezone_set("America/Caracas");
$date = new DateTime();



$date2 = date_format($date, "Y/m/d H:i:s");
//TRAEMOS LA INFORMACIÓN DEL CLIENTE





require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage('P', 'A1');

// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table style="border: -20px solid #FFF; background-color:white;">
		
		<tr style=" background-color:white;">
			
			<td style=" border: -1px solid #FFF; background-color:white; width:157px"><img src="images/logo.png"></td>
<td style="border: 0px solid #FFF; background-color:white; width:157px"></td><td style=" background-color:white; width:50px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:265px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:135px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:157px">Reportes: Eventos</td>
<td style="border: -1px solid #FFF; background-color:white; width:275px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:147px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:40px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:250px">&nbsp;Fecha de Expedicion:  $date2</td>	


			

			

		</tr>

	</table>
	<br>
	<br>
	<br>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------



// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:40px; text-align:center">#ID</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">Usuario</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">Iglesia</td>
		<td style="border: 1px solid #666; background-color:white; width:115px; text-align:center">Telefono</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">Lugar</td>
		<td style="border: 1px solid #666; background-color:white; width:105px; text-align:center">Estado</td>
		<td style="border: 1px solid #666; background-color:white; width:105px; text-align:center">Municipio</td>
		<td style="border: 1px solid #666; background-color:white; width:105px; text-align:center">Parroquia</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha Evento</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Hora Evento</td>
		<td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">Actividad</td>
		<td style="border: 1px solid #666; background-color:white; width:155px; text-align:center">Descripcion</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Estado</td>
		<td style="border: 1px solid #666; background-color:white; width:130px; text-align:center">Registro</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($usuarios as $key => $item) {


$itemcliente = "id";
$valorcliente = $item["id_cliente"];

$respuestacliente = ControladorClientes::ctrMostrarClientes($itemcliente, $valorcliente);
 
 $itemM = "id";
                  $valorM = $item["id_iglesia"];

                  $respuestaM = ControladorIglesias::ctrMostrarIglesias($itemM, $valorM);

 

$itemEstado = "id";
                 $valorEstado = $item["id_estado"];

                 $respuestaEstado = ControladorEstados::ctrMostrarEstados($itemEstado, $valorEstado);

                 $itemMunicipio = "id";
                 $valorMunicipio = $item["id_municipio"];

                 $respuestaMunicipio = ControladorMunicipios::ctrMostrarMunicipios($itemMunicipio, $valorMunicipio);

                 $itemParroquia = "id";
                 $valorParroquia = $item["id_parroquia"];

                 $respuestaParroquia = ControladorParroquias::ctrMostrarParroquias($itemParroquia, $valorParroquia);
if($item['estado'] == 0){

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestacliente[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestaM[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
			$respuestacliente[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				$item[lugar]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaEstado[nombre]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaMunicipio[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaParroquia[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[hora_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; text-align:center">
				$item[tipo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:155px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">Pendiente

			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:130px; text-align:center">
				$item[fecha_registro]
			</td>
			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}elseif ($item['estado'] == 1) {
$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestacliente[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestaM[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
			$respuestacliente[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				$item[lugar]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaEstado[nombre]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaMunicipio[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaParroquia[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[hora_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; text-align:center">
				$item[tipo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:155px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">En Curso

			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:130px; text-align:center">
				$item[fecha_registro]
			</td>
			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}elseif($item['estado'] == 2) {
$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestacliente[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestaM[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
			$respuestacliente[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				$item[lugar]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaEstado[nombre]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaMunicipio[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaParroquia[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[hora_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; text-align:center">
				$item[tipo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:155px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">Finalizado

			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:130px; text-align:center">
				$item[fecha_registro]
			</td>
			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}else{

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestacliente[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$respuestaM[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
			$respuestacliente[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				$item[lugar]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaEstado[nombre]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaMunicipio[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:105px; text-align:center">
				$respuestaParroquia[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[hora_evento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; text-align:center">
				$item[tipo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:155px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">Cancelado

			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:130px; text-align:center">
				$item[fecha_registro]
			</td>
			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}

}


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('eventos.pdf');
//$pdf->Output('iglesias.pdf', 'D');

}

}

$factura = new imprimirEventos();
$factura -> traerImpresionEventos();

?>