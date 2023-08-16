<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include the Composer autoloader
require('../commons/config/settings.php'); 
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadedFilePath = $_FILES['file']['tmp_name'];
    $excelFilePath = $uploadedFilePath; // Path to your Excel file
    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($excelFilePath);
        
        // Select the first worksheet (index 0)
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Prepare the SQL INSERT query
        $insertQuery = "INSERT INTO BankTransactions (bankDetailsID, accountNumber, accountName, address, currency, date, description, withdrawal, deposit, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare the statement
        $stmt = $conn->prepare($insertQuery);
        
        // Iterate through rows (starting from the second row to skip the header)
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
              $rowData[] = $cell->getValue(); // Extract cell values
            }
            // Convert Excel numeric date to human-readable date
            $bankDetailsID=bin2hex(random_bytes(6));
            $accountNumber = $rowData[0];
            $accountName = $rowData[1];
            $address = $rowData[2];
            $currency = $rowData[3];
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[4]);
            $dateFormatted = $date->format('Y-m-d');
            $description = $rowData[5];
            $withdrawal = $rowData[6];
            $deposit = $rowData[7];
            $balance = $rowData[8];

            $stmt->bind_param("sssssssdds", $bankDetailsID, $accountNumber, $accountName, $address, $currency, $dateFormatted, $description, $withdrawal, $deposit, $balance);
            $stmt->execute();
        }
        echo "Data inserted successfully!";
    } catch (\Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
  }else {
    echo "An error occurred while uploading the file";
  }
} 
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  </head>
  <body>
    <h1>Form Upload</h1>
   
    <form action="importBankDetails.php" method="post" enctype="multipart/form-data">
      <div class="input-group mb-3">
        <label class="input-group-text" for="inputGroupFile01">Upload</label>
        <input type="file" name="file" class="form-control" id="inputGroupFile01" required>
      </div>
      <input class="btn btn-primary" type="submit" value="Submit">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
  </body>
</html>
