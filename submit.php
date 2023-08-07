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
        $allowTypes = array('pdf');
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

            // Define a regular expression pattern to match the table format
            $pattern = '/\bname\b\s+\bemail\b\s+\bphone\b\s+\baddress\b\s+.*?\s+(.*?@.*?)\s+.*?\s+(.*?)\s+(.*?)\s*$/ms';

            // Use preg_match_all to find all occurrences of the pattern in the extracted text
            if (preg_match_all($pattern, $pdfText, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    // Extract the details from the captured groups
                    $name = $match[1];
                    $email = $match[2];
                    $phone = $match[3];
                    $address = $match[4];

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
            $statusMsg = '<p>Sorry, only PDF file is allowed to upload.</p>';
        }
    } else {
        $statusMsg = '<p>Please select a PDF file to extract text.</p>';
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
    echo "No table data found in the PDF.";
}
?>
