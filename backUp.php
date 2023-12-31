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
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Accounts Module</title>
  <meta charset="utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../commons/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../commons/bootstrap/css/custom.css">
  <script src="../commons/js/jquery.js"></script>
  <script src="../commons/js/popper.min.js"></script>
  <script src="../commons/bootstrap/js/bootstrap.min.js"></script>
  <link rel="icon" type="image/ico" href="../commons/images/favicon.ico">
  <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="tables/plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="tables/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
</head>
<body>
    <div class="container-fluid sticky-top" style="background-color: #eee;">
      <div class="row">
        <div class="col-sm-4">
          <img src="../commons/images/Octagon_logo.png" width="300" height="100"/>
        </div>
        <div class="col-sm-8">
          <h2><?php echo($_SESSION['scheme_code'].": ".$_SESSION['scheme_name']); ?></h2>
          <h3>Accounts: Home</h3>
        </div>
      </div>
      
      <div class="row">
        <div class="col-sm-12">
          <?php 
            print_menu(array("menu-pos"=>"z", "sub-menu-pos"=>"z1"));
          ?>
        </div>
      </div>
      
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Split Transactions</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                 <p>Amount to be Split: <span id="modalAnotherValue"></span></p>

              </div>
            <form method="post" action="splitBankTransactions.php">
            <input type="hidden" id="modalValueInput" name="insertID">
                <div class="modal-body">
                    <div id="transactionType">
                      <div class="form-group">
                        <label for="name">Transaction Type:</label>
                        <select id="transactionType" name="transactionType" required>
                          <option value="deposit">Deposit</option>
                          <option value="withdrawal">Withdrawal</option>
                        </select>
                      </div>
                    </div>
                    <div id="amountFields">
                      <div class="form-group">
                        <label for="name">Amount:</label>
                        <input type="number" class="form-control" name="amount[]" placeholder="Amount" required>
                      </div>
                    </div>
                    <button type="button" class="btn btn-success" id="addAmount">Add</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
      </div>
    </div>
    <script>
        var amountFields = [];
        $(document).ready(function () {
          $('#addAmount').on('click', function () {
            var newField = $('<div id="amountFields" class="form-group">' +
                                '<label for="name">Amount:</label>' +
                                '<input type="number" class="form-control" name="amount[]" placeholder="Amount" required></br>' +
                                '<button type="button" class="btn btn-danger remove-amount">Remove</button>' +
                            '</div>');
            amountFields.push(newField);
            $(this).before(newField);
          });

          amountFields.on('click', '.remove-amount', function () {
            $(this).parent().remove();
          });
        });
        $('#myModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget); // Button that triggered the modal
          var value = button.data('value'); // Extract value from data-* attributes
          var anotherValue = button.data('another-value');
          var modal = $(this);

          // Update modal content with the passed value
          modal.find('#modalValue').text(value);
          modal.find('#modalAnotherValue').text(anotherValue);
          
          // Set the value in the hidden input field
          modal.find('#modalValueInput').val(value);
        });

        function initMyModal() {
          // Get the submit button and the amount fields
          var submitButton = $('#myModal .btn-primary');
          var amountFields = $('#myModal #amountFields input[type="number"][name="amount[]"]');

          // Add an event listener to the amount fields
          amountFields.each(function (index) {
            amountFields[index].on('blur', function () {
              // Get the total amount from the amount fields
              var totalAmount = 0;
              for (var i = 0; i < amountFields.length; i++) {
                totalAmount += +amountFields[i].val();
              }

              // Check if the total amount is equal to the modalAnotherValue
              if (totalAmount !== +$('#modalAnotherValue').text()) {
                // The total amount is not equal to the modalAnotherValue, so disable the submit button
                submitButton.prop('disabled', true);
                submitButton.css('color', 'gray');
              } else {
                // The total amount is equal to the modalAnotherValue, so enable the submit button
                submitButton.prop('disabled', false);
                submitButton.css('color', '#000');
              }
            });
          });
        }

        $(document).ready(function () {
          // Initialize the myModal modal
          initMyModal();
        });
    </script>
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
                <h3 class="card-title">Imported Bank Statements</h3>
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
                    <th>Account Code</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                    $schemeCode = $_SESSION['scheme_code'];
                    $sql = "SELECT * FROM BankTransactions WHERE schemeCode= ? and submisionStatus  like '%Not Submitted%' ORDER BY detailsInsertID ASC, createdAT ASC";
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
                        <?php
                          $withdrawal = $row['withdrawal'];
                          $deposit = $row['deposit'];
                          $value = 0;
                          
                          if ($withdrawal > 0) {
                              $value = $withdrawal;
                          } else if ($deposit > 0) { // Adjusted condition here
                              $value = $deposit;
                          }
                        ?>
                        <div class="btn-group">
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-value="<?php echo $row["detailsInsertID"]; ?>" data-another-value="<?php echo $value; ?>">Split Transactions</button>
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
  </body>
</html>