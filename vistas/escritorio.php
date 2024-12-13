<?php
//activamos almacenamiento en el buffer
ob_start();
session_start();
if (!isset($_SESSION['nombre'])) {
  header("Location: login.html");
} else {


  require 'header.php';

  if ($_SESSION['escritorio'] == 1) {

    require_once "../modelos/Consultas.php";
    $consulta = new Consultas();
    $rsptac = $consulta->totalcomprahoy();
    $regc = $rsptac->fetch_object();
    $totalc = $regc->total_compra;

    $rsptav = $consulta->totalventahoy();
    $regv = $rsptav->fetch_object();
    $totalv = $regv->total_venta;

    $client = $consulta->totalclientes();
    $proveedor = $consulta->totalproveedores();
    $top10 = $consulta->Articulos_mas_vendidos();
    $stockmin = $consulta->articulostockmin();

    //obtener valores para cargar al grafico de barras
    $compras10 = $consulta->comprasultimos_10dias();
    $fechasc = '';
    $totalesc = '';
    while ($regfechac = $compras10->fetch_object()) {
      $fechasc = $fechasc . '"' . $regfechac->fecha . '",';
      $totalesc = $totalesc . $regfechac->total . ',';
    }


    //quitamos la ultima coma
    $fechasc = substr($fechasc, 0, -1);
    $totalesc = substr($totalesc, 0, -1);
    //obtener valores para cargar al grafico de barras
    $ventas12 = $consulta->ventasultimos_12meses();
    $fechasv = '';
    $totalesv = '';
    while ($regfechav = $ventas12->fetch_object()) {
      $fechasv = $fechasv . '"' . $regfechav->fecha . '",';
      $totalesv = $totalesv . $regfechav->total . ',';
    }


    //quitamos la ultima coma
    $fechasv = substr($fechasv, 0, -1);
    $totalesv = substr($totalesv, 0, -1);
?>
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Administración</h1>
                <div class="box-tools pull-right">

                </div>
              </div>


              <div class="panel-body">
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                  <div class="small-box bg-yellow">
                    <div class="inner">
                      <h4 style="font-size: 17px;">
                        <strong>S/. <?php echo $totalc; ?> </strong>
                      </h4>
                      <p>Compras</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bag"></i>
                    </div>
                    <a href="ingreso.php" class="small-box-footer">Compras <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                  <div class="small-box bg-blue">
                    <div class="inner">
                      <h4 style="font-size: 17px;">
                        <strong>S/. <?php echo $totalv; ?> </strong>
                      </h4>
                      <p>Ventas</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bag"></i>
                    </div>
                    <a href="venta.php" class="small-box-footer">Ventas <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                  <div class="small-box bg-red">
                    <div class="inner">
                      <h4 style="font-size: 17px;">
                        <strong> #<?php echo $client->num_rows ?> </strong>
                      </h4>
                      <p>Clientes</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bag"></i>
                    </div>
                    <a href="cliente.php" class="small-box-footer">Clientes <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                  <div class="small-box bg-green">
                    <div class="inner">
                      <h4 style="font-size: 17px;">
                        <strong> #<?php echo $proveedor->num_rows ?> </strong>
                      </h4>
                      <p>Proveedores</p>
                    </div>
                    <div class="icon">
                      <i class="ion ion-bag"></i>
                    </div>
                    <a href="proveedor.php" class="small-box-footer">Proveedores <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                  <div class="box box-success">
                    <div class="box-header with-border">
                      Top 10 Artículos mas vendidos
                    </div>
                    <div class="box-body">
                      <table id="dd" class="table table-striped table-bordered table-condensed table-hover">
                        <thead>
                          <tr>
                            <th>Artículo</th>
                            <th>Descripción</th>
                            <th>Stock</th>
                            <th>Cantidad</th>
                            <th>Ventas</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($reg = $top10->fetch_object()) : ?>
                            <tr>
                              <td><img src='../files/articulos/<?php echo $reg->imagen ?>' height='50px' width='50px'><?php echo $reg->nombre ?></td>
                              <td><?php echo $reg->descripcion ?></td>
                              <td><?php echo $reg->stock ?></td>
                              <td><?php echo $reg->cantidad ?> </td>
                              <td><?php echo ($reg->venta_precio) ?></td>
                            </tr>
                          <?php endwhile ?> 
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      Artículos por agotar
                    </div>
                    <div class="box-body">
                      <table id="stockmin" class="table table-striped table-bordered table-condensed table-hover">
                        <thead>
                          <tr>
                            <th>Artículo</th>
                            <th>Codigo</th>
                            <th>Descripción</th>
                            <th>Stock</th>
                            <th>Condición</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($reg = $stockmin->fetch_object()) : ?>
                            <tr>
                              <td><img src='../files/articulos/<?php echo $reg->imagen ?>' height='50px' width='50px'><?php echo $reg->nombre ?></td>
                              <td><?php echo $reg->codigo ?> </td>
                              <td><?php echo $reg->descripcion ?></td>
                              <td><?php echo $reg->stock ?></td>
                              <td>
                                <?php if ($reg->stock > 0) : ?>
                                  <span class="bg-green label rounded-4"> Disponible </span>
                                <?php else : ?>
                                  <span class="bg-red label">Agotado</span>
                                <?php endif ?>
                              </td>
                            </tr>
                          <?php endwhile ?>

                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      Compras de los ultimos 10 dias
                    </div>
                    <div class="box-body">
                      <canvas id="compras" width="400" height="300"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      Ventas de los ultimos 12 meses
                    </div>
                    <div class="box-body">
                      <canvas id="ventas" width="400" height="300"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script src="../public/js/Chart.bundle.min.js"></script>
  <script src="../public/js/Chart.min.js"></script>
  <script>
    var ctx = document.getElementById("compras").getContext('2d');
    var compras = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [<?php echo $fechasc ?>],
        datasets: [{
          label: '# Compras en S/. de los últimos 10 dias',
          data: [<?php echo $totalesc ?>],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)'
          ],
          borderColor: [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
    var ctx = document.getElementById("ventas").getContext('2d');
    var ventas = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [<?php echo $fechasv ?>],
        datasets: [{
          label: '# Ventas en S/. de los últimos 12 meses',
          data: [<?php echo $totalesv ?>],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
  </script>
<?php
}

ob_end_flush();
?>