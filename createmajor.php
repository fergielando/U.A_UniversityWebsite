<?php
@include 'config1.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create a Major</title>
</head>
<body>
    <h2>Create a Major</h2>
	<a href="majors1.php" class="back-button">Back to Majors</a>
    <form method="post" action="process_majorcreate.php">
        Major ID: <input type="text" name="major_id"><br><br>

        Department:
        <select name="dept_id">
            <?php
            // Fetch departments from dept table
            $dept_query = "SELECT DeptID, DeptName FROM dept";
            $result = $conn->query($dept_query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["DeptID"] . "'>" . $row["DeptName"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        Major Name: <input type="text" name="major_name"><br><br>

        <input type="submit" value="Create Major">
    </form>
</body>
</html>