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
      $formattedText = '';
      $currentCell = '';
      $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
      // Define a regular expression for the date format "11 Apr 2023"
      $datePattern = '/\d{2} (' . implode('|', $months) . ') \d{4}/';
      $formattedText = '';
      // Iterate through the lines
      foreach ($lines as $line) {
          // Check if the line contains a date in the format "11 Apr 2023"
          if (preg_match($datePattern, $line)) {
              // Add the current cell to the formatted text
              $formattedText .= trim($currentCell) . "\n";
              $currentCell = $line;
          } else {
              // Append the line to the current cell
              $currentCell .= ' ' . $line;
          }
      }

      // Add the last cell to the formatted text
      $formattedText .= trim($currentCell);

      // Display the formatted extracted text
      echo $formattedText;
  } else {
      echo "Error uploading the file.";
  }
  echo $formattedText;
}

