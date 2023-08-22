<?php
require('../commons/config/settings.php'); // Adjust the path to your settings file
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

if (!$conn) {
    die("Connection failed: " . sqlsrv_errors());
}

$schemeCode = $_SESSION["scheme_code"];
$sqlAccounts = "SELECT coa_account_code, coa_account_name FROM coa_tb WHERE coa_scheme_code LIKE ? ORDER BY coa_account_code";
$paramsAccounts = array($schemeCode);
$stmtAccounts = sqlsrv_query($conn, $sqlAccounts, $paramsAccounts);

if ($stmtAccounts === false) {
    die("Query error: " . sqlsrv_errors());
}

$accounts = array();

while ($rowAccounts = sqlsrv_fetch_array($stmtAccounts, SQLSRV_FETCH_ASSOC)) {
    $accountCode = $rowAccounts['coa_account_code'];
    $accountName = $rowAccounts['coa_account_name'];

    // Create an associative array for each account
    $accountData = array(
        'code' => $accountCode,
        'name' => $accountName
    );

    // Add the account data to the accounts array
    $accounts[] = $accountData;
}

// Close the database connection
sqlsrv_close($conn);

// Return the accounts array as JSON
header('Content-Type: application/json');
echo json_encode($accounts);
?>
