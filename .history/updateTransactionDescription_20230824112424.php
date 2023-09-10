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
    // Retrieve the POST parameters
    $detailsInsertID = $_POST['detailsInsertID'];
    $columnName = $_POST['columnName'];
    $newValue = $_POST['newValue'];

    $sqlUpdate = "UPDATE bankTransactions set description = ? where detailsInsertID =?";
    $paramsUpdate = array($newValue, $detailsInsertID);
    $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);
    if ($stmtUpdate === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to update value in the database']);
    echo "Query: $sqlUpdate<br>";  // Print the query for debugging
    die(print_r(sqlsrv_errors(), true));
    }else{
      // Return a success response
      echo json_encode(['success' => true]);
    }
} else {
    // Return an error response for unsupported request methods
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
