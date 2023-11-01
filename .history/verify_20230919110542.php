<?php
require 'commons/config/settings.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

?>


<!DOCTYPE html>
<html lang="en">
<!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
<?php include 'includes/header.php'; ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
  
<?php include 'includes/sidebar.php'; ?>
  <!-- Main Sidebar Container -->
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Device information</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Device Information</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-primary" style=" width: 1000px">
              <div class="card-header">
                <h3 class="card-title"></h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              
                <div class="card-body">
                   
                  <?php
                    if (isset($_GET['id'])) {
                        $machineID = $_GET['id'];
                    } else {
                        header('Location: devices');
                        exit;
                    }

                    $sql = 'SELECT * from assetInventory where machineID = ?';
$params = [$machineID];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    exit(print_r(sqlsrv_errors(), true));
}
$counter = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    ?>
                  <h1>Device Information#<?php echo $row['laptopID']; ?></h1>
                  <table style="border-collapse: collapse; width: 700px;">
                    <tr>
                        <th style="border: 2px solid black; width: 50%;">Specs</th>
                        <th style="border: 2px solid black; width: 50%;">Values</th>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Machine ID</td>
                        <td style="border: 2px solid black;"><?php echo $row['machineID']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Current Owner</td>
                        <td style="border: 2px solid black;"><?php echo $row['currentOwner']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Model</td>
                        <td style="border: 2px solid black;"><?php echo $row['laptopModel']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Serial Number</td>
                        <td style="border: 2px solid black;"><?php echo $row['laptopSN']; ?></td>
                    </tr>
                    
                    <tr>
                        <td style="border: 2px solid black;">Year of Purchase</td>
                        <td style="border: 2px solid black;"><?php echo $row['yearofpurchase']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Value of Purchase</td>
                        <td style="border: 2px solid black;"><?php echo $row['valueOfPurchase']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Usage Status</td>
                        <td style="border: 2px solid black;"><?php echo $row['usageStatus']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Company</td>
                        <td style="border: 2px solid black;"><?php echo $row['whereItIs']; ?></td>
                    </tr>
                    <tr>
                        <td style="border: 2px solid black;">Insurance Status</td>
                        <td style="border: 2px solid black;"><?php echo $row['insuranceStatus']; ?></td>
                    </tr>
                    </table>
                </div> 
                <?php } ?>
            </div>
          </div>
        </div>
        <!-- /.row -->
        <!-- Main row -->
        
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php require 'includes/footer.php'; ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
</body>
</html>
