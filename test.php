<!DOCTYPE html>
<html>
<head>
    <title>Word Document Upload</title>
</head>
<body>
    <form action="process_upload.php" method="post" enctype="multipart/form-data">
        <label for="wordDocument">Choose a Word document:</label>
        <input type="file" name="wordDocument" id="wordDocument">
        <input type="submit" value="Upload and Extract">
    </form>
</body>
</html>
