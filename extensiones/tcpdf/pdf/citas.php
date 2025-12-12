<?php

require_once "../../../controladores/citas.controlador.php";
require_once "../../../modelos/citas.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/mascotas.controlador.php";
require_once "../../../modelos/mascotas.modelo.php";




class imprimirCitas{

public $codigo;

public function traerImpresionCitas(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$item = null;
        $valor = null;

        $usuarios = ControladorCitas::ctrMostrarCitas($item, $valor);






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
<td style=" background-color:white; width:150px">Reportes: Citas</td>

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



// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		
		<td style="border: 1px solid #666; background-color:white; width:40px; text-align:center">#</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Propietario</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Mascota</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Servicio</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de Cita</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Descripcion</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Precio</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Estado</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de realizacion</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($usuarios as $key => $item) {

 $itemUsuario = "id";
                  $valorUsuario = $item["id_usuario"];

                  $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);


                    $itemM = "id";
                  $valorM = $item["id_mascota"];

                  $respuestaM = ControladorMascotas::ctrMostrarMascotas($itemM, $valorM);

                  

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center"> 
				$respuestaUsuario[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaM[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$item[tipo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_cita]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$item[precio]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[estado]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_registro]
			</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}



// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('citas.pdf', 'D');

}

}

$factura = new imprimirCitas();
$factura -> traerImpresionCitas();

?>