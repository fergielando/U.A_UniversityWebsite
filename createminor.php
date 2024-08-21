<?php
@include 'config1.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create a Minor</title>
</head>
<body>
    <h2>Create a Minor</h2>
	<a href="minors1.php" class="back-button">Back to Minor</a>
    <form method="post" action="process_minorcreate.php">
        Minor ID: <input type="text" name="minor_id"><br><br>

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

        Minor Name: <input type="text" name="minor_name"><br><br>

        <input type="submit" value="Create Minor">
    </form>
</body>
</html>