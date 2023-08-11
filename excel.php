<?php
require 'vendor/autoload.php'; // Include the Composer autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFilePath = 'data.xlsx'; // Path to your Excel file

try {
    // Load the Excel file
    $spreadsheet = IOFactory::load($excelFilePath);
    
    // Select the first worksheet (index 0)
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Initialize the table header
    $table = '<table border="1">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Withdrawal</th>
                    <th>Deposit</th>
                    <th>Balance</th>
                </tr>';
    
    // Iterate through rows (starting from the second row to skip the header)
    foreach ($worksheet->getRowIterator(2) as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue(); // Extract cell values
        }
        
        // Convert Excel numeric date to human-readable date
        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[0]);
        
        // Add row data to the table
        $table .= '<tr>';
        $table .= '<td>' . $date->format('Y-m-d') . '</td>'; // Formatted Date
        $table .= '<td>' . $rowData[1] . '</td>'; // Description
        $table .= '<td>' . $rowData[2] . '</td>'; // Withdrawal
        $table .= '<td>' . $rowData[3] . '</td>'; // Deposit
        $table .= '<td>' . $rowData[4] . '</td>'; // Balance
        $table .= '</tr>';
    }
    
    // Close the table
    $table .= '</table>';
    
    // Output the extracted table
    echo $table;
    
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>
