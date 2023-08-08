<?php
require_once('vendor/ottosmops/pdftotext/src/Extract.php');
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

function extractCodeFromPDF($pdfFile)
{
    try {
        // Create a new PDFtoText object
        $pdftotext = new PDFtoText();

        // Set the PDF file to be extracted
        $pdftotext->setFilename($pdfFile);

        // Extract the text from the PDF file
        $text = $pdftotext->getText();

        // Find all the lines that contain code
        $codeLines = preg_grep('/\b(php|html|css|js)\b/i', $text);

        // Return the code lines
        return $codeLines;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
function uploadAndExtractCode()
{
    // Get the uploaded file
    $uploadedFile = $_FILES['pdf_file'];

    // Check if the file was uploaded successfully
    if ($uploadedFile['error'] !== 0) {
        echo "Error uploading file: " . $uploadedFile['error'];
        return;
    }

    // Get the file name
    $fileName = $uploadedFile['name'];

    // Move the file to a temporary directory
    $tmpFilePath = tempnam('/tmp', $fileName);
    move_uploaded_file($uploadedFile['tmp_name'], $tmpFilePath);

    // Extract the code from the PDF file
    $codeLines = extractCodeFromPDF($tmpFilePath);

    // Print the code lines
    echo implode("\n", $codeLines);
}
uploadAndExtractCode();

