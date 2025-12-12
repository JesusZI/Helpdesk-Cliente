  

<?php

$item = null;
$valor = null;
$orden = "id";



$usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
$totalUsuarios = count($usuarios);


//$citas = ControladorCitas::ctrMostrarCitas($item, $valor);
$totalClientes = count($citas);



?>

          <!-- /.col -->
             <div class="col-md-3">




<div class="info-box">
              <span class="info-box-icon bg-success elevation-1"><i class="nav-icon fa fa-calendar"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Eventos</span>
                <span class="info-box-number"><?php echo number_format($totalClientes); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>

            <!-- /.info-box -->
          </div>

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-md-3">

<div class="info-box">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Usuarios</span>
                <span class="info-box-number"><?php echo number_format($totalUsuarios); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>

         
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          