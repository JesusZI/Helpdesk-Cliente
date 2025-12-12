<?php


require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";





class imprimirClientes{

public $codigo;

public function traerImpresionClientes(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$item = null;
        $valor = null;

        $usuarios = ControladorClientes::ctrMostrarClientes($item, $valor);






require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage('P', 'A3');

// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table style="border: -20px solid #FFF; background-color:white;">
		
		<tr style=" background-color:white;">
			
			<td style=" border: -1px solid #FFF; background-color:white; width:157px"><img src="images/logo.png"></td>
<td style="border: 0px solid #FFF; background-color:white; width:157px"></td><td style=" background-color:white; width:50px"></td>
<td style="border: -1px solid #FFF; background-color:white; width:157px">Reportes: Usuarios</td>
<td style="border: -1px solid #FFF; background-color:white; width:265px"></td>
			

			

			

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
		
		
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">#</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Nombre</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Cedula</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">E-mail</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Telefono</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Direccion</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de Nacimiento</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Fecha de Registro</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($usuarios as $key => $item) {




$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center"> 
				$item[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[documento]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[email]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[direccion]
			</td>
		
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha_nacimiento]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$item[fecha]
			</td>


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');



	




}


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('clientes.pdf');

}

}

$factura = new imprimirClientes();
$factura -> traerImpresionClientes();

?>