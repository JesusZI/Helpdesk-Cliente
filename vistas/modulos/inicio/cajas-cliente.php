  

<?php

 $item = "id_usuario";
        $valor = $_SESSION["id"];

        //$usuarios = ControladorVehiculos::ctrMostrarVehiculos2($item, $valor);
$totalProveedores = count($usuarios);

  $item1 = "id_vendedor";
       

 //$respuesta = ControladorVentas::ctrMostrarVentas2($item1, $valor);
 $totalrespuesta = count($respuesta);

 //$ventas = ControladorVentas::ctrSumaTotalVentasc($item1, $valor);



?>

 
          <!-- /.col -->
          <div class="col-md-4">
            <div class="info-box mb-3 bg-info">
              <span class="info-box-icon"><i class="fa fa-car"></i></span>

              <div class="info-box-content">
                <span class="info-box-text"> registrados</span>
                <span class="info-box-number"><?php echo number_format($totalProveedores); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-md-4">
            <div class="info-box mb-3 bg-warning">
              <span class="info-box-icon"><i class="fa fa-gas-pump"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pagos realizados</span>
                <span class="info-box-number"><?php echo number_format($totalrespuesta); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>

            <div class="col-md-4">
          <div class="info-box mb-3 bg-success">
              <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Gasto total</span>
                <span class="info-box-number">BsS <?php echo number_format($ventas["totalc"],2); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>


          <!-- /.col -->
          