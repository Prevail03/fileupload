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
  $selectedAccount = $_POST['selectedAccount'];
  list($accountCode, $accountDescription) = explode('|', $selectedAccount);
  
  
  $schemeCode = $_SESSION["scheme_code"];
  
  $sqlInsert = "INSERT INTO bank_custody_tb ( accountCode, accountName, schemeCode) VALUES (?, ?, ?)";
    $paramsInsert = array($accountCode, $accountDescription, $schemeCode);
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
        var confirmResult = confirm('Insert Successful');
        if (confirmResult) {
            window.location.href = 'importBankDetails.php?success=true';
        }
    </script>";
    
}