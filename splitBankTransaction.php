<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../commons/config/settings.php'); 
date_default_timezone_set('Africa/Nairobi');
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $detailsInsertID =  $_POST['insertID'];
  $amounts = $_POST['amount'];
  $transactionType = $_POST['transactionType'];

  $totalAmount = 0; 
    
  foreach ($amounts as $amount) {
    $totalAmount += $amount; 
  }

  if ($transactionType ==="withdrawal") {
    $sql = "SELECT withdrawal FROM BankTransactions WHERE detailsInsertID = ?";
    $params = array($detailsInsertID);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
      echo "Query: $sql<br>";  // Print the query for debugging
      die(print_r(sqlsrv_errors(), true));
    } else {
      while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $withdrawnAmount = $row['withdrawal'];
        if($totalAmount > $withdrawnAmount){
          echo "
          <script>
              var confirmResult = confirm('Enter values whose sum is less than $withdrawnAmount');
              if (confirmResult) {
                  window.location.href = 'importedBankDetails.php?invalidvaluesdeposit';
              }
          </script>";
        }else{
          $sqlUpdate = "UPDATE bankTransactions set submisionStatus = 'Submitted' where detailsInsertID =?";
          $paramsUpdate = array($detailsInsertID);
          $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);
          if ($stmtUpdate === false) {
            echo "Query: $sql<br>";  // Print the query for debugging
            die(print_r(sqlsrv_errors(), true));
          }else{
            $difference = $withdrawnAmount - $totalAmount;
            if ($difference == 0) {
              $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, splitType, amount, detailsInsertID, accountCode, splitID, createdAt, accountDescription, credit, debit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
              $splitTypes = $_POST['splitType']; // Assuming this is the array of split types
              $amounts = $_POST['amount'];
              $createdAt = date("Y-m-d H:i:s"); 
              $splitID=bin2hex(random_bytes(6));
              $credit = 0;
              $debit = 0;

              for ($i = 0; $i < count($amounts); $i++) {
                $splitType = $splitTypes[$i];
                $amount = $amounts[$i];
                $selectedAccount = $_POST['selectedAccount'][$i]; // Access the selectedAccount corresponding to this split
            
                // Split the selected value into accountCode and accountDescription
                list($accountCode, $accountDescription) = explode('|', $selectedAccount);
            
                if ($splitType === 'credit') {
                    $credit = $amount; // Assign the amount to credit
                    $debit = 0.00; // Ensure debit is 0
                } else if ($splitType === 'debit') {
                    $debit = $amount; // Assign the amount to debit
                    $credit = 0.00; // Ensure credit is 0
                }
            
                $paramsInsert = array($transactionType, $splitType, $amount, $detailsInsertID, $accountCode, $splitID, $createdAt, $accountDescription, $credit, $debit);
            
                $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
                if ($stmtInsert === false) {
                    echo "Query: $sqlInsert<br>";  // Print the query for debugging
                    die(print_r(sqlsrv_errors(), true));
                }
              }
              echo "
              <script>
                  var confirmResult = confirm('Transaction Split Successful');
                  if (confirmResult) {
                      window.location.href = 'importedBankDetails.php?success=true';
                  }
              </script>";
            } 
          }
        }
      }
    }
  }else if ($transactionType ==="deposit") {
    $sql = "SELECT deposit FROM BankTransactions WHERE detailsInsertID = ?";
    $params = array($detailsInsertID);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
      echo "Query: $sql<br>";  // Print the query for debugging
      die(print_r(sqlsrv_errors(), true));
    } else {
      while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $depositedAmount = $row['deposit'];
        if($totalAmount > $depositedAmount){
          echo "
          <script>
              var confirmResult = confirm('Enter values whose sum is less than $depositedAmount');
              if (confirmResult) {
                  window.location.href = 'importedBankDetails.php?invalidvaluesdeposit';
              }
          </script>";
        }else{
          $sqlUpdate = "UPDATE bankTransactions set submisionStatus = 'Submitted' where detailsInsertID =?";
          $paramsUpdate = array($detailsInsertID);
          $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);
          if ($stmtUpdate === false) {
            echo "Query: $sql<br>";  // Print the query for debugging
            die(print_r(sqlsrv_errors(), true));
          }else{
            $difference = $depositedAmount - $totalAmount;
            if ($difference == 0) {
              
              $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, splitType, amount, detailsInsertID, accountCode, splitID, createdAt, accountDescription, credit, debit ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? ,?)";
              $splitTypes = $_POST['splitType'];
              $amounts = $_POST['amount'];
              $createdAt = date("Y-m-d H:i:s");
              $splitID = bin2hex(random_bytes(6)); // to record amounts that were split at the same time, same as detailsInsertID

              for ($i = 0; $i < count($amounts); $i++) {
                $splitType = $splitTypes[$i];
                $amount = $amounts[$i];
                $selectedAccount = $_POST['selectedAccount'][$i]; // Access the selectedAccount corresponding to this split
            
                // Split the selected value into accountCode and accountDescription
                list($accountCode, $accountDescription) = explode('|', $selectedAccount);
            
                if ($splitType === 'credit') {
                    $credit = $amount; // Assign the amount to credit
                    $debit = 0.00; // Ensure debit is 0
                } else if ($splitType === 'debit') {
                    $debit = $amount; // Assign the amount to debit
                    $credit = 0.00; // Ensure credit is 0
                }
            
                $paramsInsert = array($transactionType, $splitType, $amount, $detailsInsertID, $accountCode, $splitID, $createdAt, $accountDescription, $credit, $debit);
            
                $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
                if ($stmtInsert === false) {
                    echo "Query: $sqlInsert<br>";  // Print the query for debugging
                    die(print_r(sqlsrv_errors(), true));
                }
              }
              echo "
              <script>
                  var confirmResult = confirm('Transaction Split Successful');
                  if (confirmResult) {
                      window.location.href = 'importedBankDetails.php?success=true';
                  }
              </script>";

            } 
          }
        }
      }
    }
  }else{
    header('location:importedBankDetails.php?invalidtransactiontype');
  }
}
?>