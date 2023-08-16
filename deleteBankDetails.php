<?php
require('../commons/config/settings.php'); 
if (isset($_GET['id'])) {
  $detailsInsertID = $_GET['id'];
} else {
  header('Location: importedBankDetails.php');
  exit;
}
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
$sql = "DELETE FROM BankTransactions WHERE detailsInsertID = ?";
$params = array($detailsInsertID);

$stmt = sqlsrv_query( $conn, $sql,$params);
if( $stmt === false) {
  die( print_r( sqlsrv_errors(), true) );
  header('location:  importedBankDetails.php?failed');
}else{
  header('location:  importedBankDetails.php?success');
}
?>