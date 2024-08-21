<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drop Course</title>
</head>
<body>
    <h1>Drop Course</h1>
    <form action="dropconfirmation.php" method="GET">
        <!-- Input field for CRN -->
        <label for="crn">CRN:</label>
        <input type="text" id="crn" name="crn" required><br>

        <!-- Submit button to confirm the CRN -->
        <input type="submit" value="Confirm CRN">
    </form>
</body>
</html>
