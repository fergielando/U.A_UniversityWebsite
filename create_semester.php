<!DOCTYPE html>
<html>
<head>
    <title>Create Semester</title>
</head>
<body>
    <h2>Create Semester</h2>
    <form method="post" action="createsemesterprocess.php">
        Semester ID: <input type="text" name="SemesterID" required><br><br>
        Semester Name: <input type="text" name="SemesterName" required><br><br>
        Semester Year:
        <select name="SemesterYear" required>
            <?php
            // Generate dropdown options for years from 2000 to 2035
            for ($year = 2000; $year <= 2035; $year++) {
                echo "<option value='$year'>$year</option>";
            }
            ?>
        </select><br><br>
        Start Time: <input type="date" name="StartTime" required><br><br>
        End Time: <input type="date" name="EndTime" required><br><br>
        <input type="submit" value="Create Semester">
    </form>
</body>
</html>