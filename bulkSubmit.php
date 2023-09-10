<?php
require('../commons/config/settings.php'); 
session_start();
if(!isset($_SESSION['auth_code'])){
	header("location: logout.php");
	exit("User not authenticated");
}
if($_SESSION['user_folder'] !== Settings::$folder){
	header("location: logout.php");
	exit();
}
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selectedRows = $_POST['selectedRows'];
  $selectedAccounts = $_POST['selectedAccount'];
  $gl_journal_id = mt_rand(100000, 999999);
  $gl_done_by = $_SESSION['username'];
  // Create an associative array to store selected accounts
  $selectedAccountInfo = array();
  foreach ($selectedAccounts as $account) {
      list($accountCode, $accountDescription) = explode('|', $account);
      $selectedAccountInfo[$accountCode] = $accountDescription;
  }
  // Loop through the selected rows and process each one
  foreach ($selectedRows as $key => $detailsInsertID) {
    list($accountCode, $accountDescription) = explode('|', $selectedAccounts[$key]);
    $sqlUpdate = "UPDATE bankTransactions set submisionStatus = 'Submitted' where detailsInsertID =?";
    $paramsUpdate = array($detailsInsertID);
    $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);
    if ($stmtUpdate === false) {
    echo "Query: $sqlUpdate<br>";  // Print the query for debugging
    die(print_r(sqlsrv_errors(), true));
    }else{
        // Retrieve the detailsInsertID and other details based on the selected detailsInsertID
        $sql = "SELECT * FROM BankTransactions WHERE detailsInsertID = ?";
        $params = array($detailsInsertID);
        $stmt = sqlsrv_query($conn, $sql, $params);  
        if ($stmt === false) {
            echo "Query: $sql<br>";  // Print the query for debugging
            die(print_r(sqlsrv_errors(), true));
        } else {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //insert values
                $gl_scheme_code = $row['schemeCode'];
                $gl_document = "Standard";
                $gl_trans_date = date_format($row['date'], "Y-m-d");
                $gl_account_code = $accountCode;
                $gl_currency = $row['currency'];
                $gl_op = 0.00;
                $gl_debit = $row['withdrawal'];
                $gl_credit = $row['deposit'];
                $gl_comment = $row['description'];
                $gl_credit = $row['deposit'];
                $schemeCode =$_SESSION["scheme_code"];
                $sqlBankCustody = "SELECT * FROM bank_custody_tb WHERE schemeCode LIKE ?";
                $paramsBankCustody = array($schemeCode);
                $stmtBankCustody = sqlsrv_query($conn, $sqlBankCustody, $paramsBankCustody);
                if ($stmtBankCustody === false) {
                die(print_r(sqlsrv_errors(), true));
                }
                $accountCode = "";
                $accountName = "";
                while ($rows = sqlsrv_fetch_array($stmtBankCustody, SQLSRV_FETCH_ASSOC)) {
                $accountCode = $rows['accountCode'];
                $accountName = $rows['accountName'];
                }
                if($gl_debit > 1 ){
                    $gl_creditC = $gl_debit;
                    $gl_debitC - 0.00;
                    $gl_commentC = $accountName;
                    $gl_account_codeC  =$accountCode; 
                    $sqlInsert1 = "INSERT INTO gl_tb (gl_scheme_code, gl_document, gl_trans_date, gl_account_code, gl_currency, gl_op, gl_debit, gl_credit, gl_journal_id, gl_comment, gl_done_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $paramsInsert1 = array($gl_scheme_code, $gl_document, $gl_trans_date, $gl_account_codeC, $gl_currency, $gl_op, $gl_debitC, $gl_creditC, $gl_journal_id, $gl_commentC, $gl_done_by);
                    $stmtInsert1 = sqlsrv_query($conn, $sqlInsert1, $paramsInsert1);
                    if ($stmtInsert1 === false) {
                        // Check for the specific error causing the issue
                        $errors = sqlsrv_errors();
                        foreach ($errors as $error) {
                            if ($error['SQLSTATE'] === '22001') {
                                // Column causing the truncation error
                                $truncatedColumn = str_replace('String or binary data would be truncated - ', '', $error['message']);
                                echo "Truncated Column: $truncatedColumn<br>";
                            }
                        }
                        echo "Query: $sqlInsert1<br>";  // Print the query for debugging
                        die(print_r($errors, true));
                    }
                }else{
                   //"Debit ----Contra Entry ";
                   $gl_debitD = $gl_credit;
                   $gl_creditD - 0.00;
                    $gl_commentD = $accountName;
                    $gl_account_codeD  = $accountCode; 
                    $sqlInsert = "INSERT INTO gl_tb (gl_scheme_code, gl_document, gl_trans_date, gl_account_code, gl_currency, gl_op, gl_debit, gl_credit, gl_journal_id, gl_comment, gl_done_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $paramsInsert = array($gl_scheme_code, $gl_document, $gl_trans_date, $gl_account_codeD, $gl_currency, $gl_op, $gl_debitD, $gl_creditD, $gl_journal_id, $gl_commentD, $gl_done_by);
                    $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
                    if ($stmtInsert === false) {
                        // Check for the specific error causing the issue
                        $errors = sqlsrv_errors();
                        foreach ($errors as $error) {
                            if ($error['SQLSTATE'] === '22001') {
                                // Column causing the truncation error
                                $truncatedColumn = str_replace('String or binary data would be truncated - ', '', $error['message']);
                                echo "Truncated Column: $truncatedColumn<br>";
                            }
                        }
                        echo "Query: $sqlInsert<br>";  // Print the query for debugging
                        die(print_r($errors, true));
                    }
                }
                $sqlInsert = "INSERT INTO gl_tb (gl_scheme_code, gl_document, gl_trans_date, gl_account_code, gl_currency, gl_op, gl_debit, gl_credit, gl_journal_id, gl_comment, gl_done_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $paramsInsert = array($gl_scheme_code, $gl_document, $gl_trans_date, $gl_account_code, $gl_currency, $gl_op, $gl_debit, $gl_credit, $gl_journal_id, $gl_comment, $gl_done_by);
                $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
                if ($stmtInsert === false) {
                    // Check for the specific error causing the issue
                    $errors = sqlsrv_errors();
                    foreach ($errors as $error) {
                        if ($error['SQLSTATE'] === '22001') {
                            // Column causing the truncation error
                            $truncatedColumn = str_replace('String or binary data would be truncated - ', '', $error['message']);
                            echo "Truncated Column: $truncatedColumn<br>";
                        }
                    }
                    echo "Query: $sqlInsert<br>";  // Print the query for debugging
                    die(print_r($errors, true));
                }
                echo "
                <script>
                    var confirmResult = confirm('Bulk Submit Successful');
                    if (confirmResult) {
                        window.location.href = 'importedBankDetails.php?success=true';
                    }
                </script>";
                
            }
        }
    }
}
} else {
  // Handle cases where the form wasn't submitted properly
  echo "Form submission error.";
}
?>
