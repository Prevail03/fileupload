<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include the Composer autoloader
require '../commons/config/settings.php';
require './menu.php';
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);
session_start();
if (!isset($_SESSION['auth_code'])) {
    header('location: ../logout.php');
    exit('User not authenticated');
}
// Can access this module
if ($_SESSION['accounts'] != 1) {
    $_SESSION['module_error'] = 'You are not allowed to access this module';
    header('location: ../modules.php');
    exit;
}
if ($_SESSION['user_folder'] !== Settings::$folder) {
    header('location: ../logout.php');
    exit('Folder inaccessible');
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

  <!-- Font Awesome -->
  <link rel="stylesheet" href="tables/plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="tables/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="tables/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
</head>
<body>
    <div class="container-fluid sticky-top" style="background-color: #eee;">
      <div class="row">
        <div class="col-sm-4">
          <img src="../commons/images/Octagon_logo.png" width="300" height="100"/>
        </div>
        <div class="col-sm-8">
          <h2><?php echo $_SESSION['scheme_code'].': '.$_SESSION['scheme_name']; ?></h2>
          <h3>Accounts: Home</h3>
        </div>
      </div>
      
      <div class="row">
        <div class="col-sm-12">
          <?php
            print_menu(['menu-pos' => 'z', 'sub-menu-pos' => 'z1']);
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
                              <label for="name">Transaction to be split:</label>
                              <select id="transactionType" name="transactionType">
                                  <option value="deposit">Deposit</option>
                                  <option value="withdrawal">Withdrawal</option>
                              </select>
                          </div>
                      </div>
                      
                      <div id="accountCode">
                          <label for="name">Account Code:</label>
                          <select name="selectedAccount[]" id="selectedAccount" required>
                              <option value="">Select Account</option>
                              <?php
                    $schemeCode = $_SESSION['scheme_code'];
$sqlAccounts = 'SELECT * FROM coa_tb WHERE coa_scheme_code LIKE ? ORDER BY coa_account_code';
$paramsAccounts = [$schemeCode];
$stmtAccounts = sqlsrv_query($conn, $sqlAccounts, $paramsAccounts);
if ($stmtAccounts === false) {
    exit(print_r(sqlsrv_errors(), true));
}
$counting = 1;
// Check if selectedAccount is set in POST
$selectedAccount = isset($_POST['selectedAccount']) ? $_POST['selectedAccount'] : '';

while ($rowAccounts = sqlsrv_fetch_array($stmtAccounts, SQLSRV_FETCH_ASSOC)) {
    $accountCode = $rowAccounts['coa_account_code'];
    $name = $rowAccounts['coa_account_name'];

    // Check if the current option matches the selected value
    $selected = ($selectedAccount == "$accountCode|$name") ? 'selected' : '';

    echo "<option value='$accountCode|$name' $selected>$accountCode - $name</option>";
}
?>
                          </select>
                      </div>

                      <div class="form-group">
                          <label for="name">Transaction Type:</label>
                          <select id="splitType" name="splitType[]">
                              <option>Select</option>
                              <option value="credit">Credit</option>
                              <option value="debit">Debit</option>
                          </select>
                      </div>

                      <div id="amountFields">
                          <div class="form-group">
                              <label for="name">Amount:</label>
                              <input type="number" class="form-control amount-input" name="amount[]"
                                  placeholder="Amount" required>
                          </div>
                      </div>
                            
                      
                      <button type="button" class="btn btn-success" id="addAmount">Add</button>
                      <div class="amountFieldsWrapper"></div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary" id="saveChangesBtn" disabled>Submit</button>
                  </div>
              </form>
          </div>
      </div>
    </div>
<script>
  $(document).ready(function () {
    $('#addAmount').on('click', function () {
        var newField = $(
            '<div class="amountFieldsWrapper">' +
            '<div id="accountCode">' +
            '<label for="name">Account Code:</label>' +
            '<select name="selectedAccount[]" class="account-select" required>' +
            '<option value="">Select Account</option>' +
            '</select>' +
            '</div>' + '<br/>' +
            '<label for="name">Transaction Type:</label>' +
            '<select class="split-type" name="splitType[]">' +
            '<option>Select</option>' +
            '<option value="credit">Credit</option>' +
            '<option value="debit">Debit</option>' +
            '</select>' +
            '</div>' + '<br/>' +
            '<div class="form-group">' +
            '<label for="name">Amount:</label>' +
            '<input type="number" class="form-control amount-input" name="amount[]" placeholder="Amount" required></br>' +
            '<div class="form-group">' +
            '<button type="button" class="btn btn-danger remove-amount" onclick="removeAmount()">Remove</button>' +
            '</div>' +
            '</div>'
        );

        // AJAX to populate the account code select box
        $.ajax({
            url: 'fetch_accounts.php', // URL to your PHP script that fetches accounts
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                var selectAccount = newField.find('.account-select');
                selectAccount.empty();
                selectAccount.append($('<option>', {
                    value: '',
                    text: 'Select Account'
                }));
                $.each(data, function (index, account) {
                    selectAccount.append($('<option>', {
                        value: account.code + '|' + account.name,
                        text: account.code + ' - ' + account.name
                    }));
                });

                // Set the selected option based on selectedAccount value
                var selectedValue = account.code + '|' + account.name;
                console.log(selectedValue);
                selectAccount.val(selectedValue);
            },
            error: function (error) {
                console.error(error);
            }
        });

        $('#amountFields').append(newField);
        updateSaveButtonState();
    });

    function removeAmount() {
  $(this).closest('.amountFieldsWrapper').remove();
  updateSaveButtonState();
}

      

      $('#myModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget);
          var value = button.data('value');
          var anotherValue = button.data('another-value');
          var modal = $(this);

          modal.find('#modalValueInput').val(value);
          modal.find('#modalAnotherValue').text(anotherValue);
          updateSaveButtonState();
      });

      $(document).on('blur', '.amount-input', function () {
          updateSaveButtonState();
      });

      function updateSaveButtonState() {
          var submitButton = $('#saveChangesBtn');
          var totalAmount = 0;
          $('.amount-input').each(function () {
              totalAmount += parseFloat($(this).val() || 0);
          });
          var modalAnotherValue = parseFloat($('#modalAnotherValue').text() || 0);

          if (totalAmount !== modalAnotherValue) {
              submitButton.prop('disabled', true);
              submitButton.css('color', 'gray');
          } else {
              submitButton.prop('disabled', false);
              submitButton.css('color', '#000');
          }
      }
      $('#myModal').on('hidden.bs.modal', function () {
          $('#amountFields .form-group:not(:first)').remove();
          updateSaveButtonState();
      });
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
            <a class="btn btn-primary" href="home.php" role="button">Dashboard</a>
            <ul class="nav nav-tabs" id="myTabs" role="tablist">
              <li class="nav-item">
                  <a class="nav-link active" id="imported-tab" data-toggle="tab" href="#imported" role="tab" aria-controls="imported" aria-selected="true">Imported Transactions</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" id="split-tab" data-toggle="tab" href="#split" role="tab" aria-controls="split" aria-selected="false">Split Bank Transactions</a>
              </li>
            </ul>

            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="imported" role="tabpanel" aria-labelledby="imported-tab">
                <!-- /.card -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Imported Bank Transactions</h3>
                  </div>

                  <!-- /.card-header -->
                  <div class="card-body">
                    <form id="bulkSubmitForm" action="bulkSubmit.php" method="POST">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account Name</th>
                                    <th>Currency</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Withdrawal</th>
                                    <th>Deposit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
  $schemeCode = $_SESSION['scheme_code'];
$sql = "SELECT * FROM BankTransactions WHERE schemeCode= ? and submisionStatus  like '%Not Submitted%' ORDER BY detailsInsertID ASC, createdAT ASC";
$params = [$schemeCode];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    exit(print_r(sqlsrv_errors(), true));
}
$counter = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    ?>
                                    <tr>
                                        <!-- <tr data-details-insert-id="<?php // echo $row['detailsInsertID'];?>"> -->
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo $row['accountNumber']; ?></td>
                                        <td>
                                          <select name="selectedAccount[]">
                                              <?php
              $schemeCode = $_SESSION['scheme_code'];
    $sqlAccounts = 'SELECT * FROM coa_tb WHERE coa_scheme_code LIKE ? ORDER BY coa_account_code';
    $paramsAccounts = [$schemeCode];
    $stmtAccounts = sqlsrv_query($conn, $sqlAccounts, $paramsAccounts);
    if ($stmtAccounts === false) {
        exit(print_r(sqlsrv_errors(), true));
    }
    $counting = 1;

    // Check if selectedAccount is set in POST
    $selectedAccounts = isset($_POST['selectedAccount']) ? $_POST['selectedAccount'] : [];
    while ($rowAccounts = sqlsrv_fetch_array($stmtAccounts, SQLSRV_FETCH_ASSOC)) {
        $accountCode = $rowAccounts['coa_account_code'];
        $name = $rowAccounts['coa_account_name'];

        // Check if the current account is in the selectedAccounts array
        $selected = in_array("$accountCode|$name", $selectedAccounts) ? 'selected' : '';

        echo "<option value='$accountCode|$name' $selected>$accountCode - $name</option>";
    }
    ?>
                                          </select>
                                        </td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['currency']; ?></td>
                                        <td><?php echo date_format($row['date'], 'Y-m-d'); ?></td>
                                        <td class="editable-cell" contenteditable="true" data-column="description"><?php echo $row['description']; ?></td>
                                        <td><?php echo $row['withdrawal']; ?></td>
                                        <td><?php echo $row['deposit']; ?></td>
                                        <input type="hidden" name="selectedRows[]" value="<?php echo $row['detailsInsertID']; ?>">
                                        <td>
                                            <?php
                                            $withdrawal = $row['withdrawal'];
    $deposit = $row['deposit'];
    $value = 0;

    if ($withdrawal > 0) {
        $value = $withdrawal;
    } elseif ($deposit > 0) {
        $value = $deposit;
    }
    ?>
                                            <div class="btn-group">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" data-value="<?php echo $row['detailsInsertID']; ?>" data-another-value="<?php echo $value; ?>">Split Transactions</button>
                                                <a class="btn btn-danger" href="deleteBankDetails.php?id=<?php echo $row['detailsInsertID']; ?>"
                                                    role="button">Delete</a>
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
                                    <th>Account Name</th>
                                    <th>Currency</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Withdrawal</th>
                                    <th>Deposit</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="submit" id="bulkSubmitButton" class="btn btn-primary">Bulk Submit</button>
                    </form>
                  </div>
                  <!-- /.card-body -->
                </div>
                <!-- /.card -->
              </div>
              <div class="tab-pane fade" id="split" role="tabpanel" aria-labelledby="split-tab">
                <!-- /.card -->
                <div class="card-body">
                  <div class="card-header">
                      <h3 class="card-title">Split Bank Transactions</h3>
                    </div>

                    <form id="bulkSubmitSplitForm" action="bulkSubmitSplit.php" method="POST">
                      <table id="example1" class="table table-bordered table-striped"> 
                        <thead>
                        <tr>
                          <th>#</th>
                          <th>Account Number</th>
                          <th>Account Name</th>
                          <th>Address</th>
                          <th>Date</th>
                          <th>Description</th>
                          <th>Amount</th>
                          <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                          $schemeCode = $_SESSION['scheme_code'];
$sql = "SELECT *, s.transactionType As transaction_type FROM BankTransactions b, split_bank_transactions_tb s WHERE b.schemeCode= ? and b.detailsInsertID = s.detailsInsertID  and b.submisionStatus  like '%Submitted%' and s.splitSubmissionStatus  like '%Not Submitted%' ORDER BY s.splitTransactionID ASC, s.createdAT ASC";
$params = [$schemeCode];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    exit(print_r(sqlsrv_errors(), true));
}
$counter = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {?>
                            <tr>
                              <td><?php echo $counter++; ?></td>
                              <td><?php echo $row['accountNumber']; ?></td>
                              <td><?php echo $row['accountName']; ?></td>
                              <td><?php echo $row['address']; ?></td>
                              <td><?php echo date_format($row['date'], 'Y-m-d'); ?></td>
                              <td><?php echo $row['description']; ?></td>
                              <td><?php echo $row['amount']; ?></td>
                              <input type="hidden" name="selectedRowss[]" value="<?php echo $row['splitTransactionID']; ?>">
                              <td>
                              <div class="btn-group">
                                <a class="btn btn-success" href="submitBankDetails.php?id=<?php echo $row['splitTransactionID']; ?>" role="button">Submit</a>
                                <a class="btn btn-danger" href="deleteBankDetails.php?id=<?php echo $row['detailsInsertID']; ?>" role="button">Delete</a>
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
                          <th>Date</th>
                          <th>Description</th>
                          <th>Amount</th>
                          <th>Action</th>
                        </tr>
                        </tfoot>
                      </table>
                      <button type="submit" id="bulkSubmitSplitButton" class="btn btn-primary">Bulk Submit</button>
                    </form>
                </div>
                  <!-- /.card -->
              </div>
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
<script>
  $("#bulkSubmitButton").click(function () {
    var selectedRows = $("input[name='selectedRows[]']:checked").map(function () {
    return this.value;
    }).get();

    var selectedAccounts = $("select[name='selectedAccount']").map(function () {
        return this.value;
    }).get();
    
  });
  $("#bulkSubmitSplitButton").click(function () {
    var selectedRows = $("input[name='selectedRowss[]']:checked").map(function () {
    return this.value;
    }).get();

  });
</script>
<script>
  $(document).ready(function() {
    // Add click event listener to the editable cells
    $('.editable-cell').on('blur', function() {
      // Get the updated value
      var newValue = $(this).text().trim();

      // Get the column name
      var columnName = $(this).data('column');

      // Get the fixture ID of the row
      var detailsInsertID = $(this).closest('tr').data('details-insert-id');

      // Send an AJAX request to update the value in the database
      $.ajax({
        url: 'updateTransactionDescription.php', // Replace with the actual update script
        method: 'POST',
        data: {
          detailsInsertID: detailsInsertID,
          columnName: columnName,
          newValue: newValue
        },
        success: function(response) {
          // Handle the response, such as displaying a success message
          console.log('Value updated successfully');
        },
        error: function(xhr, status, error) {
          // Handle the error, such as displaying an error message
          console.error('Error updating value: ' + error);
        }
      });
    });
  });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
$(document).ready(function() {
  // Initialize DataTable for example1
  var example1 = $("#example1").DataTable({
    "responsive": true,
    "lengthChange": false,
    "autoWidth": false,
    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
  });
  
  example1.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
  example1.page.len(100).draw();
  // Initialize DataTable for example2
  var example2 = $('#example2').DataTable({
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