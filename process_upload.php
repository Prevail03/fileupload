<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_FILES['wordDocument']['error'] === UPLOAD_ERR_OK) {
        $uploadedFilePath = $_FILES['wordDocument']['tmp_name'];

        // Convert Word document to plain text using pandoc
        $outputFilePath = 'docx/'.mt_rand(100000,999999).date('Ymdhis').'.txt';
        exec("pandoc -f docx -t plain $uploadedFilePath -o $outputFilePath");

        // Read and process the plain text file
        $extractedText = file_get_contents($outputFilePath);

       // Split the text into lines
        $lines = explode("\n", $extractedText);
        // Initialize variables
        $tableRows = array();
        $currentRow = array();
        $foundDate = false;
        // Define an array of month abbreviations
        $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        // Iterate through the lines
        foreach ($lines as $line) {
            // Check if the line contains a date in the format "06 Apr 2023"
            if (preg_match('/\d{2} (' . implode('|', $months) . ') \d{4}/', $line)) {
                // Add the current row if a date was found
                if ($foundDate && !empty($currentRow)) {
                    $tableRows[] = $currentRow;
                }

                // Start a new row with the current line
                $currentRow = array($line);
                $foundDate = true;
            } elseif ($foundDate) {
                // Add the line to the current row if a date was found
                $currentRow[] = $line;
            }
        }

        // Display the parsed table rows
        foreach ($tableRows as $row) {
            echo implode("\t", $row) . "\n"; // Adjust delimiter as needed
        }
    } else {
        echo "Error uploading the file.";
    }
}
