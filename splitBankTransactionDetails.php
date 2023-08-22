<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include the Composer autoloader
require('../commons/config/settings.php'); 
require('./menu.php');
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
session_start();
if(!isset($_SESSION['auth_code'])){
	header("location: ../logout.php");
	exit("User not authenticated");
}
//Can access this module
if($_SESSION['accounts'] != 1){
	$_SESSION['module_error'] = "You are not allowed to access this module";
	header("location: ../modules.php");
	exit();
}
if($_SESSION['user_folder'] !== Settings::$folder){
	header("location: ../logout.php");
	exit("Folder inaccessible");
}
?>

  <meta charset="utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../commons/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../commons/bootstrap/css/custom.css">
  <script src="../commons/js/jquery.js"></script>
  <script src="../commons/js/popper.min.js"></script>
  <script src="../commons/bootstrap/js/bootstrap.min.js"></script>
  <link rel="icon" type="image/ico" href="../commons/images/favicon.ico">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="tables/plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="tables/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">



    
    <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    </br></br>
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <!-- /.card -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Split Bank Transactions</h3>
              </div>

              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped"> 
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th>Address</th>
                    <th>Currency</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Withdrawal</th>
                    <th>Deposit</th>
                    <th>Balance</th>
                    <th>Amount</th>
                    <th>Transaction Type</th>
                    <th>Account Code</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                    $schemeCode = $_SESSION['scheme_code'];
                    $sql = "SELECT *, s.transactionType As transaction_type FROM BankTransactions b, split_bank_transactions_tb s WHERE b.schemeCode= ? and b.detailsInsertID = s.detailsInsertID  and b.submisionStatus  like '%Submitted%' ORDER BY b.detailsInsertID ASC, s.createdAT ASC";
                    $params = array($schemeCode);
                    $stmt = sqlsrv_query( $conn, $sql,$params);
                    if( $stmt === false) {
                        die( print_r( sqlsrv_errors(), true) );
                    }
                    $counter =1;
                    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {?>
                      <tr>
                        <td><?php echo $counter++?></td>
                        <td><?php echo $row['accountNumber']?></td>
                        <td><?php echo $row['accountName']?></td>
                        <td><?php echo $row['address']?></td>
                        <td><?php echo $row['currency']?></td>
                        <td><?php echo date_format($row['date'], "Y-m-d") ?></td>
                        <td><?php echo $row['description']?></td>
                        <td><?php echo $row['withdrawal']?></td>
                        <td><?php echo $row['deposit']?></td>
                        <td><?php echo $row['balance']?></td>
                        <td><?php echo $row['amount']?></td>
                        <td><?php echo $row['transaction_type']?></td>

                        <td>
                          <form action = "submitBankDetails.php" method = "GET">
                            <select name="accountCode" id="accountCode"> 
                              <?php
                                $sqlAccounts = "SELECT * FROM coa_tb WHERE coa_scheme_code LIKE ? ORDER BY coa_account_code";
                                $paramsAccounts = array($schemeCode);
                                $stmtAccounts = sqlsrv_query( $conn, $sqlAccounts,$paramsAccounts);
                                if( $stmtAccounts === false) {
                                    die( print_r( sqlsrv_errors(), true) );
                                }
                                $counting = 1;
                                while( $rowAccounts = sqlsrv_fetch_array( $stmtAccounts, SQLSRV_FETCH_ASSOC) ) {
                                  $accountCode = $rowAccounts['coa_account_code'];
                                  $name = $rowAccounts['coa_account_name'];
                                  echo "<option >Select Account</option>";
                                  echo "<option value='$accountCode'>$name</option>";
                                }
                              ?>
                            </select>
                          </form>                            
                        </td>
                        <td>
                        <div class="btn-group">
                        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-value="<?php echo $row["detailsInsertID"]; ?>">Split Transactions</button> -->
                          <a class="btn btn-success" href="submitBankDetails.php?id=<?php echo $row["detailsInsertID"]; ?>" role="button">Submit</a>
                          <a class="btn btn-danger" href="deleteBankDetails.php?id=<?php echo  $row["detailsInsertID"];?>" role="button">Delete</a>
                        </div>
                        </td>
                      </tr>
                    <?php
                    }
                  ?>
                  </tbody>
                  <tfoot>
                  <tr>
                    <th>#</th>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th>Address</th>
                    <th>Currency</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Withdrawal</th>
                    <th>Deposit</th>
                    <th>Balance</th>
                    <th>Amount</th>
                    <th>Transaction Type</th>
                    <th>Account Code</th>
                    <th>Action</th>
                  </tr>
                  </tfoot>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script src="tables/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="tables/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="tables/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="tables/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="tables/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="tables/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="tables/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="tables/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="tables/plugins/jszip/jszip.min.js"></script>
<script src="tables/plugins/pdfmake/pdfmake.min.js"></script>
<script src="tables/plugins/pdfmake/vfs_fonts.js"></script>
<script src="tables/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="tables/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="tables/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->
<script src="tables/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="tables/dist/js/demo.js"></script>
<!-- Page specific script -->
<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
  