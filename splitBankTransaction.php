<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../commons/config/settings.php'); 
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
                $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, amount, detailsInsertID) VALUES (?, ?, ?)";
                foreach ($_POST['amount'] as $amount) {
                    $paramsInsert = array($transactionType, $amount, $detailsInsertID);
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
            } else {
                // Add the difference to the amounts array
                $_POST['amount'][] = $difference;

                $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, amount, detailsInsertID) VALUES (?, ?, ?)";
                foreach ($_POST['amount'] as $amount) {
                    $paramsInsert = array($transactionType, $amount, $detailsInsertID);
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
                $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, amount, detailsInsertID) VALUES (?, ?, ?)";
                foreach ($_POST['amount'] as $amount) {
                    $paramsInsert = array($transactionType, $amount, $detailsInsertID);
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
            } else {
                // Add the difference to the amounts array
                $_POST['amount'][] = $difference;

                $sqlInsert = "INSERT INTO split_bank_transactions_tb (transactionType, amount, detailsInsertID) VALUES (?, ?, ?)";
                foreach ($_POST['amount'] as $amount) {
                    $paramsInsert = array($transactionType, $amount, $detailsInsertID);
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