<?php

require_once "../../../controladores/consultas.controlador.php";
require_once "../../../modelos/consultas.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/mascotas.controlador.php";
require_once "../../../modelos/mascotas.modelo.php";

require_once "../../../controladores/medicos.controlador.php";
require_once "../../../modelos/medicos.modelo.php";


class imprimirConsulta{

public $codigo;

public function traerImpresionConsulta(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemVenta = "id";
$valorVenta = $this->codigo;

$respuestaVenta = ControladorConsultas::ctrMostrarConsultas($itemVenta, $valorVenta);



//TRAEMOS LA INFORMACIÓN DEL CLIENTE



$itemVendedor = "id";
$valorVendedor = $respuestaVenta["id_usuario"];

$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

 $itemM = "id";
                  $valorM = $respuestaVenta["id_mascota"];

                  $respuestaM = ControladorMascotas::ctrMostrarMascotas($itemM, $valorM);

                    $itemMed = "id";
                  $valorMed = $respuestaVenta["id_medico"];

                  $respuestaMed = ControladorMedicos::ctrMostrarMedicos($itemMed, $valorMed);


//REQUERIMOS LA CLASE TCPDF


require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage('P', 'A3');

// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table>
		
		<tr style=" background-color:white;">
			
			<td style=" background-color:white; width:150px"><img src="images/logo.png"></td>
<td style=" background-color:white; width:150px"></td>
<td style=" background-color:white; width:150px">Reporte: Consulta N° $respuestaVenta[id] </td>

			<td style="background-color:white; width:165px">
				
				<div style="font-size:8.5px; text-align:right; line-height:15px;">
					
					<br>
					RIF: J-741.741.741

					<br>
					Dirección: 

				</div>

			</td>

			<td style="background-color:white; width:170px">

				<div style="font-size:8.5px; text-align:right; line-height:15px;">
					
					<br>
					Teléfono: 02121544569
					
					<br>
					Calle 100 Libertador Local Nº 10-10 Sector Casco Central

				</div>
				
			</td>

			

		</tr>

	</table>

	<br>
	<br>
	<br>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');


// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">N° de Consulta</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Propietario</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Mascota</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Medico</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de Consulta</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Descripcion</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Diagnostico</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de realizacion</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');


// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$respuestaVenta[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center"> 
				$respuestaVendedor[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaM[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaMed[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaVenta[fecha_consulta]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaVenta[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaVenta[diagnostico]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaVenta[fecha_registro]
			</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

// ---------------------------------------------------------




// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('consulta.pdf');

}

}

$factura = new imprimirConsulta();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionConsulta();

?>