<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include the Composer autoloader
require 'commons/config/settings.php';
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFilePath = $_FILES['file']['tmp_name'];
        $excelFilePath = $uploadedFilePath; // Path to your Excel file

        $spreadsheet = IOFactory::load($excelFilePath);
        $worksheet = $spreadsheet->getActiveSheet();
        // Prepare the SQL INSERT query
        $sql = 'INSERT INTO BankTransactions (bankDetailsID ,currency, date, description, withdrawal, deposit, balance, schemeCode, createdAt, selectedPeriod, selectedPeriodSpecification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        date_default_timezone_set('Africa/Nairobi');
        // Iterate through rows (starting from the second row to skip the header)
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue(); // Extract cell values
            }
            // Convert Excel numeric date to human-readable date
            $bankDetailsID = bin2hex(random_bytes(6));
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[0]);
            $dateFormatted = $date->format('Y-m-d');
            $description = $rowData[1];
            $withdrawal = $rowData[2];
            $deposit = $rowData[3];
            $balance = $rowData[4];
            $createdAt = date('Y-m-d H:i:s');
            $selectedPeriods = $selectedPeriod;
            $selectedSpecifications = $selected_specification;
            $SchemeCode = $schemeCode;

            $params = [$bankDetailsID, $currency, $dateFormatted, $description, $withdrawal, $deposit, $balance, $schemeCode, $createdAt, $selectedPeriods, $selectedSpecifications];
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                exit(print_r(sqlsrv_errors(), true));
            }
        }

        echo "
        <script>
            var confirmResult = confirm('Insert Successful');
            if (confirmResult) {
                window.location.href = 'importBankDetails.php?success=true';
            }S
        </script>";
    } else {
        echo 'An error occurred while uploading the file';
        header('location:importBankDetails.php?uploadfailure');
    }
}
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
            <h1 class="m-0"> Upload Device information</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"> Upload Device Information</li>
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
                <h3 class="card-title">Upload</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <h1>File Upload</h1>
              <form action="importBankDetails.php" method="post" enctype="multipart/form-data">
                <div class="input-group mb-3">
                    <label class="input-group-text" for="inputGroupFile01">Upload</label>
                    <input type="file" name="file" class="form-control" id="inputGroupFile01" style="width: 50%" required>
                </div>
                <input class="btn btn-primary" type="submit" value="Submit">
              </form> 
             
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


       