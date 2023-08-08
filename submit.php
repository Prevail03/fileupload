<?php
$pdfText = '';
$tableData = array();
$statusMsg = '';

if (isset($_POST['submit'])) {
    // If file is selected
    if (!empty($_FILES["pdf_file"]["name"])) {
        // File upload path
        $fileName = basename($_FILES["pdf_file"]["name"]);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        // Allow certain file formats
        $allowTypes = array('pdf','docx','doc');
        if (in_array($fileType, $allowTypes)) {
            // Include autoloader file
            include 'vendor/autoload.php';
            // Initialize and load PDF Parser library
            $parser = new \Smalot\PdfParser\Parser();
            // Source PDF file to extract text
            $file = $_FILES["pdf_file"]["tmp_name"];

            // Parse pdf file using Parser library
            $pdf = $parser->parseFile($file);

            // Extract text from PDF
            $text = $pdf->getText();

            // Add line break
            $pdfText = nl2br($text);
        } else {
            $statusMsg = '<p>Sorry, only PDF file is allowed to upload.</p>';
        }
    } else {
        $statusMsg = '<p>Please select a PDF file to extract text.</p>';
    }
}

// Process the table data and organize it back into columns
if (!empty($pdfText)) {
    // Explode the text by lines
    $lines = explode('<br />', $pdfText);

    // Check if the first line contains headers
    $headerLine = $lines[0];
    if (strpos($headerLine, 'Name') !== false && strpos($headerLine, 'Email') !== false && strpos($headerLine, 'Pho') !== false && strpos($headerLine, 'Address') !== false) {
        // Skip the first line (header) and process the rest
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            // Split the line by spaces
            $data = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
            if (count($data) === 4) {
                $name = $data[0];
                $email = $data[1];
                $phone = $data[2];
                $address = $data[3];

                // Add the extracted data to the tableData array
                $tableData[] = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address
                );
            }
        }
    } else {
        $statusMsg = '<p>No table data found in the PDF.</p>';
    }
}

// Display the extracted table data in an HTML table
if (!empty($tableData)) {
    echo '<table border="1">';
    echo '<tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th></tr>';
    foreach ($tableData as $row) {
        echo '<tr><td>' . $row['name'] . '</td><td>' . $row['email'] . '</td><td>' . $row['phone'] . '</td><td>' . $row['address'] . '</td></tr>';
    }
    echo '</table>';
} else {
    echo $statusMsg;
}
?>
