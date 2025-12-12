<?php


require_once "../../../controladores/medicos.controlador.php";
require_once "../../../modelos/medicos.modelo.php";





class imprimirMedicos{

public $codigo;

public function traerImpresionMedicos(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$item = null;
        $valor = null;

        $usuarios = ControladorMedicos::ctrMostrarMedicos($item, $valor);






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
<td style=" background-color:white; width:150px">Reportes: Medicos</td>

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
		
		
		<td style="border: 1px solid #666; background-color:white; width:30px; text-align:center">#</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Cedula</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Nombre</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Apellido</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">E-mail</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Telefono</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Direccion</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Especialidad</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Fecha de Registro</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($usuarios as $key => $item) {

  

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:30px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center"> 
				$item[documento]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[apellido]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[correo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[direccion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[especialidad]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$item[fecha_ingreso]
			</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


}


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('medicos.pdf', 'D');

}

}

$factura = new imprimirMedicos();
$factura -> traerImpresionMedicos();

?>