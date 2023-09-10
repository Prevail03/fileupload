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
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFilePath = $_FILES['file']['tmp_name'];
        $excelFilePath = $uploadedFilePath; // Path to your Excel file
        $selectedPeriod = $_POST['selected_period'];
        $selected_specification = $_POST['selected_specification'];
        try {
            $spreadsheet = IOFactory::load($excelFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            // Prepare the SQL INSERT query
            $sql = 'INSERT INTO BankTransactions (bankDetailsID, accountNumber, accountName, address, currency, date, description, withdrawal, deposit, balance, schemeCode, createdAt, selectedPeriod, selectedPeriodSpecification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)';
            date_default_timezone_set('Africa/Nairobi');
            // Iterate through rows (starting from the second row to skip the header)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue(); // Extract cell values
                }
                // Convert Excel numeric date to human-readable date
                $bankDetailsID = bin2hex(random_bytes(6));
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
                $schemeCode = $_SESSION['scheme_code'];
                $createdAt = date('Y-m-d H:i:s');
                $selectedPeriods = $selectedPeriod;
                $selectedSpecifications = $selected_specification;

                $params = [$bankDetailsID, $accountNumber, $accountName, $address, $currency, $dateFormatted, $description, $withdrawal, $deposit, $balance, $schemeCode, $createdAt, $selectedPeriods, $selectedSpecifications];
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    exit(print_r(sqlsrv_errors(), true));
                }
            }

            header('location:importBankDetails.php?success');
        } catch (\Exception $e) {
            echo 'An error occurred: '.$e->getMessage();
            header('location:importBankDetails.php?insertfailure');
        }
    } else {
        echo 'An error occurred while uploading the file';
        header('location:importBankDetails.php?uploadfailure');
    }
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
            print_menu(['menu-pos' => 'z',
                            'sub-menu-pos' => 'z1']);
?>
        </div>
      </div>
      
    </div>
</br>
<?php
    $schemeCode = $_SESSION['scheme_code'];
$sqlBankCustody = 'SELECT * FROM bank_custody_tb WHERE schemeCode LIKE ?';
$paramsBankCustody = [$schemeCode];
$stmtBankCustody = sqlsrv_query($conn, $sqlBankCustody, $paramsBankCustody);
if ($stmtBankCustody === false) {
    exit(print_r(sqlsrv_errors(), true));
}
$custodyID = '';
while ($row = sqlsrv_fetch_array($stmtBankCustody, SQLSRV_FETCH_ASSOC)) {
    $custodyID = $row['custodyID']."\n";
}
if (!empty($custodyID)) {?>
       <h1>File Upload</h1>
    <form action="importBankDetails.php" method="post" enctype="multipart/form-data">
        <label for="period">Select Period:</label>
        <select id="period" name="selected_period">
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
        </select>
        <div id="options-container">
            <!-- The dynamic options dropdowns will be inserted here -->
        </div>
    
        <div class="input-group mb-3">
            <label class="input-group-text" for="inputGroupFile01">Upload</label>
            <input type="file" name="file" class="form-control" id="inputGroupFile01" style="width: 50%" required>
        </div>
        <input class="btn btn-primary" type="submit" value="Submit">
    </form><?php
} else {?>
      <h1>Bank Custody Selection</h1>
      <form action="bankCustodySelection.php" method="post" enctype="multipart/form-data">
        <select name="selectedAccount">
          <?php
      $schemeCode = $_SESSION['scheme_code'];
    $sqlAccounts = "SELECT * FROM coa_tb WHERE coa_scheme_code LIKE ? and coa_account_name like '%Custody%' ORDER BY coa_account_code";
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
        </br></br>
        <input class="btn btn-primary" type="submit" value="Submit">   
      </form>
      <?php
}
?>
    
<script>
    document.getElementById('period').addEventListener('change', function () {
        var period = this.value;
        var optionsContainer = document.getElementById('options-container');

        // Clear any previous options
        optionsContainer.innerHTML = '';

        // Create a dropdown for period specification
        var specificationDropdown = document.createElement('select');
        specificationDropdown.name = 'selected_specification';

        // Populate options based on selected period
        if (period === 'monthly') {
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            for (var i = 0; i < months.length; i++) {
                var option = document.createElement('option');
                option.text = months[i];
                specificationDropdown.appendChild(option);
            }
        } else if (period === 'quarterly') {
            var quarters = ["Q1", "Q2", "Q3", "Q4"];
            for (var i = 0; i < quarters.length; i++) {
                var option = document.createElement('option');
                option.text = quarters[i];
                specificationDropdown.appendChild(option);
            }
        } else if (period === 'yearly') {
            var currentYear = new Date().getFullYear();
            for (var year = 2007; year <= currentYear; year++) {
                var option = document.createElement('option');
                option.text = year.toString();
                specificationDropdown.appendChild(option);
            }
        }

        // Append the populated dropdown to the options container
        optionsContainer.appendChild(specificationDropdown);
    });
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
  </body>
</html>