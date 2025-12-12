<?php



require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../controladores/mascotas.controlador.php";
require_once "../../../controladores/citas.controlador.php";
require_once "../../../controladores/consultas.controlador.php";

require_once "../../../controladores/medicos.controlador.php";


require_once "../../../modelos/usuarios.modelo.php";
require_once "../../../modelos/mascotas.modelo.php";
require_once "../../../modelos/citas.modelo.php";
require_once "../../../modelos/consultas.modelo.php";
require_once "../../../modelos/medicos.modelo.php";



class imprimirExpediente{

public $idMascota;

public function traerExpediente(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemVenta = "id";
$valorVenta = $this->idMascota;

$respuestaVenta = ControladorMascotas::ctrMostrarMascotas($itemVenta, $valorVenta);

$itemVendedor = "id";
$valorVendedor = $respuestaVenta["id_usuario"];

$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);


//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage();

// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table>
    
    <tr>
      
      <td style=" background-color:white; width:150px"><img src="images/logo.png"></td>

      <td style="background-color:white; width:100px">
        
        <div style="font-size:8.5px; text-align:right">
          
          <br>
        RIF: J-741.741.741

          <br>
          Dirección: 

        </div>

      </td>

      <td style="background-color:white; width:170px">

        <div style="font-size:8.5px; text-align:right">
          
          <br>
          Teléfono: 02121544569
          
          <br>
        Calle 100 Libertador Local Nº 10-10 Sector Casco Central

        </div>
        
      </td>

      <td style="background-color:white; width:120px; text-align:center; color:red"><br><br>EXPEDIENTE N. $respuestaVenta[id]</td>

    </tr>

  </table>
 

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table>
    
    <tr>
      
    
      <td style="background-color:white; width:100px">
        
        <div style="font-size:8.5px; text-align:right">
          
        

          <br>
          Propietario: $respuestaVendedor[nombre]

        </div>

      </td>

      <td style="background-color:white; width:60px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            C.I:$respuestaVendedor[documento]

        </div>
        
      </td>

      <td style="background-color:white; width:170px">
        
        <div style="font-size:8.5px; text-align:right">
          
        

          <br>
            Registro: $respuestaVenta[fecha_registro]
         

        </div>

      </td>



    </tr>



  </table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');


$bloque2 = <<<EOF

  <table>
    
    <tr>
      
    
      <td style="background-color:white; width:80px">
        
        <div style="font-size:8.5px; text-align:right">
          
        

          <br>
          Nombre: $respuestaVenta[nombre]

        </div>

      </td>

      <td style="background-color:white; width:60px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            Raza: $respuestaVenta[raza]

        </div>
        
      </td>

       <td style="background-color:white; width:60px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            Especie: $respuestaVenta[especie]

        </div>
        
      </td>
       <td style="background-color:white; width:60px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
           Sexo: $respuestaVenta[sexo]

        </div>
        
      </td>
       <td style="background-color:white; width:40px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            Peso:$respuestaVenta[peso]

        </div>
        
      </td>
       
       



    </tr>



  </table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');


$bloque2 = <<<EOF

  <table>
    
    <tr>
      
    
      

       <td style="background-color:white; width:110px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
           Nacimiento: $respuestaVenta[fecha_nacimiento]

        </div>
        
      </td>

      <td style="background-color:white; width:170px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            Ultima Cita: $respuestaVenta[ultima_cita]

        </div>
        
      </td>

      <td style="background-color:white; width:170px">

        <div style="font-size:8.5px; text-align:right">
          
         
          
          <br>
            Ultima Consulta: $respuestaVenta[ultima_consulta]

        </div>
        
      </td>



    </tr>



  </table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');



$item = "id_mascota";
                    $valor = $this->idMascota;
        $educacion = ControladorCitas::ctrMostrarCitas2($item, $valor);

 if(!$educacion){

      $bloque3 = <<<EOF

    <h3 class="headline">Esta Mascota Actualmente no posee Citas Registradas</h3>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');
        
        }else{


 foreach ($educacion as $key => $value){



$bloque3 = <<<EOF

   <blockquote >
                  <p>Fecha: $value[fecha_cita] &nbsp;&nbsp;Tipo: $value[tipo] &nbsp;&nbsp;Precio:$value[precio]</p>
                   <p>Estado:$value[estado]&nbsp;&nbsp;Descripcion: $value[descripcion]&nbsp;&nbsp;</p>
                </blockquote>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}

}



$item2 = "id_mascota";
                    $valor2 = $this->idMascota;
        $educacion2 = ControladorConsultas::ctrMostrarConsultas2($item2, $valor2);

 if(!$educacion2){

      $bloque3 = <<<EOF

    <h3 class="headline">Esta Mascota Actualmente no posee Citas Registradas</h3>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');
        
        }else{


 foreach ($educacion2 as $key => $value2){

$item3 = "id";
                    $valor3 = $value2["id_medico"];
        $m = ControladorMedicos::ctrMostrarMedicos($item3, $valor3);

$bloque3 = <<<EOF

   <blockquote >
                  <p>Fecha: $value2[fecha_consulta] &nbsp;&nbsp;Descripcion: $value2[descripcion] &nbsp;&nbsp;Diagnostico:$value2[diagnostico]</p>
                   <p>Medico:$m[nombre]</p>
                </blockquote>


EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

}

}






$pdf->Output('expediente.pdf', 'D');

}

}

$factura = new imprimirExpediente();
$factura -> idMascota = $_GET["idMascota"];
$factura -> traerExpediente();

?>