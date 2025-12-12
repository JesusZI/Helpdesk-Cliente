<?php


require_once "../../../controladores/iglesias.controlador.php";
require_once "../../../modelos/iglesias.modelo.php";

require_once "../../../controladores/estados.controlador.php";
require_once "../../../modelos/estados.modelo.php";
require_once "../../../controladores/municipios.controlador.php";
require_once "../../../modelos/municipios.modelo.php";
require_once "../../../controladores/parroquias.controlador.php";
require_once "../../../modelos/parroquias.modelo.php";



class imprimirIglesias{

public $codigo;

public function traerImpresionIglesias(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$item = null;
        $valor = null;

        $usuarios = ControladorIglesias::ctrMostrarIglesias($item, $valor);






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
<td style="border: -1px solid #FFF; background-color:white; width:157px">Reportes: Iglesias</td>
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
		
		
		<td style="border: 1px solid #666; background-color:white; width:40px; text-align:center">#ID</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">Nombre</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Telefono</td>
		<td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">Estado</td>
		<td style="border: 1px solid #666; background-color:white; width:115px; text-align:center">Municipio</td>
		<td style="border: 1px solid #666; background-color:white; width:115px; text-align:center">Parroquia</td>
		<td style="border: 1px solid #666; background-color:white; width:145px; text-align:center">Descripcion</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($usuarios as $key => $item) {

$itemEstado = "id";
                 $valorEstado = $item["id_estado"];

                 $respuestaEstado = ControladorEstados::ctrMostrarEstados($itemEstado, $valorEstado);

                 $itemMunicipio = "id";
                 $valorMunicipio = $item["id_municipio"];

                 $respuestaMunicipio = ControladorMunicipios::ctrMostrarMunicipios($itemMunicipio, $valorMunicipio);

                 $itemParroquia = "id";
                 $valorParroquia = $item["id_parroquia"];

                 $respuestaParroquia = ControladorParroquias::ctrMostrarParroquias($itemParroquia, $valorParroquia);


$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			

			<td style="border: 1px solid #666; color:#333; background-color:white; width:40px; text-align:center">
				$item[id]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center"> 
				$item[nombre]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
			$item[telefono]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; text-align:center">
				$respuestaEstado[nombre]
			</td>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
				$respuestaMunicipio[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:115px; text-align:center">
				$respuestaParroquia[nombre]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:145px; text-align:center">
				$item[descripcion]
			</td>
			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


}


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

$pdf->Output('iglesias.pdf');
//$pdf->Output('iglesias.pdf', 'D');

}

}

$factura = new imprimirIglesias();
$factura -> traerImpresionIglesias();

?>