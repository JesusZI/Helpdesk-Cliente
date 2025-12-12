  <?php

error_reporting(0);

if(isset($_GET["fechaInicial"])){

    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];

}else{

$fechaInicial = null;
$fechaFinal = null;

}

$respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

$arrayFechas = array();
$arrayVentas = array();
$sumaPagosMes = array();

foreach ($respuesta as $key => $value) {

  $fecha = substr($value["fecha"],0,7);

  array_push($arrayFechas, $fecha);

  $arrayVentas = array($fecha => $value["total"]);

  foreach ($arrayVentas as $key => $value) {
    
    $sumaPagosMes[$key] += $value;
  }

}


$noRepetirFechas = array_unique($arrayFechas);


?>
   <div class="col-md-12">
        
           
           
            <div class="card bg-gradient-info">
              <div class="card-header border-0">
                <h3 class="card-title">
                  <i class="fas fa-th mr-1"></i>
                  Grafico de Ingresos
                </h3>

                
              </div>
              <div class="card-body border-radius-none  nuevoGraficoVentas">
                <div class="chart" id="line-chart-ventas" style="height: 250px;"></div>
              </div>
              <!-- /.card-body --> 
              
              <!-- /.card-footer -->
            </div>
         
          <!-- /.col -->
        </div>
<script>
 var line = new Morris.Line({
    element          : 'line-chart-ventas',
    resize           : true,
    data             : [

    <?php

    if($noRepetirFechas != null){

      foreach($noRepetirFechas as $key){

        echo "{ y: '".$key."', ventas: ".$sumaPagosMes[$key]." },";


      }

      echo "{y: '".$key."', ventas: ".$sumaPagosMes[$key]." }";

    }else{

       echo "{ y: '0', ventas: '0' }";

    }

    ?>

    ],
    xkey             : 'y',
    ykeys            : ['ventas'],
    labels           : ['ventas'],
    lineColors       : ['#efefef'],
    lineWidth        : 2,
    hideHover        : 'auto',
    gridTextColor    : '#fff',
    gridStrokeWidth  : 0.4,
    pointSize        : 4,
    pointStrokeColors: ['#efefef'],
    gridLineColor    : '#efefef',
    gridTextFamily   : 'Open Sans',
    preUnits         : 'BsS ',
    gridTextSize     : 10
  });

</script>
