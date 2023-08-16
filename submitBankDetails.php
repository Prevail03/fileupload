<?php
require('../commons/config/settings.php'); 

if (isset($_GET['id'])) {
  $detailsInsertID = $_GET['id'];
} else {
  header('Location: importedBankDetails.php');
  exit;
}
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
$sql = "SELECT * FROM BankTransactions WHERE detailsInsertID = ?";
$params = array($detailsInsertID);
$stmt = sqlsrv_query($conn, $sql, $params);
$accountCode = $_GET['accountCode']; 
echo 'Account Code: '.$accountCode."</br>";
echo "-------------------------================================</br>";
if ($stmt === false) {
  echo "Query: $sql<br>";  // Print the query for debugging
  die(print_r(sqlsrv_errors(), true));
} else {
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo $row['accountNumber'] . "<br/>";
    echo $row['accountName'] . "<br/>";
    echo $row['address'] . "<br/>";
    echo $row['currency'] . "<br/>";
    echo date_format($row['date'], "Y-m-d") . "<br/>";
    echo $row['description'] . "<br/>";
    echo $row['withdrawal'] . "<br/>";
    echo $row['deposit'] . "<br/>";
    echo $row['balance'] . "<br/>";
  }
}