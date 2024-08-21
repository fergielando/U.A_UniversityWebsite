<?php
@include 'config1.php';

// Check if a minor ID is provided in the URL
if (isset($_GET['id'])) {
    $minorID = mysqli_real_escape_string($conn, $_GET['id']);

    // Retrieve the minor information from the database based on the provided minor ID
    $query = "SELECT * FROM minor WHERE MinorID = '$minorID'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $minor = mysqli_fetch_assoc($result);
    } else {
        // Handle the case where the minor with the given ID doesn't exist
        echo "Minor not found.";
        exit;
    }
} else {
    // Handle the case where no minor ID is provided in the URL
    echo "Minor ID not provided.";
    exit;
}

// Update minor information
if (isset($_POST['update'])) {
    $newDeptID = mysqli_real_escape_string($conn, $_POST['newDeptID']);
    $newMinorName = mysqli_real_escape_string($conn, $_POST['newMinorName']);

    $updateQuery = "UPDATE minor SET DeptID = '$newDeptID', MinorName = '$newMinorName' WHERE MinorID = '$minorID'";

    if (mysqli_query($conn, $updateQuery)) {
        // Redirect back to the minors list page after the update is successful
        header('location:minors1.php');
    } else {
        // Handle the case where the update fails
        echo "Update failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Edit Minor</title>

   <link rel="stylesheet" href="css/fatman1.css">

   <style>
      /* Add your custom styles here */
      body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }

        .header {
            background: #3498db;
            color: #fff;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 36px;
            margin: 0;
        }

        .header .back-button {
            background: #2980b9;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }

        .header .back-button:hover {
            background-color: #1c6281;
        }

        .edit-major-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #3498db;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }
		
        .department-list-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
   </style>
</head>
<body>

   <div class ="header">
      <h1>Edit Minor</h1>
      <a href="minors1.php" class="back-button">Back to Minors List</a>
   </div>

   <div class="edit-minor-container">
      <h2>Edit Minor Information</h2>
      <form action="" method="post">
         <input type="text" name="newDeptID" placeholder="Dept ID" value="<?php echo $minor['DeptID']; ?>">
         <input type="text" name="newMinorName" placeholder="MinorName" value="<?php echo $minor['MinorName']; ?>">
         <input type="submit" name="update" value="Update">
      </form>
   </div>
   
   <!-- Displaying Department IDs and Names in a table -->
<div class="department-list-container">
    <h2>Department List</h2>
    <table>
        <thead>
            <tr>
                <th>Department ID</th>
                <th>Department Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Assuming $conn is your database connection
            $deptQuery = "SELECT DeptID, DeptName FROM dept";
            $deptResult = mysqli_query($conn, $deptQuery);

            if (mysqli_num_rows($deptResult) > 0) {
                while ($row = mysqli_fetch_assoc($deptResult)) {
                    echo "<tr>";
                    echo "<td>" . $row['DeptID'] . "</td>";
                    echo "<td>" . $row['DeptID'] . " - " . $row['DeptName'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No departments found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

   <!-- Add any other elements or styling as needed -->

</body>
</html>








