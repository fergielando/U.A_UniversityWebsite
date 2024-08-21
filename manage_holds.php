<?php
@include 'config1.php';

// Check if the UID parameter exists in the URL
if (isset($_GET['studentID'])) {
    $studentID = $_GET['studentID']; // Assign the UID to the studentID variable
} else {
    // Handle the case where the studentID parameter is not provided in the URL
    // You can set a default value or show an error message.
    $studentID = ''; // Default value
}


// Default filter value
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Handle form submission to create a new hold
if (isset($_POST['create_hold'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $date_of_hold = mysqli_real_escape_string($conn, $_POST['date_of_hold']);
    $hold_type = mysqli_real_escape_string($conn, $_POST['hold_type']);

    // Insert the new hold into the 'hold' table
    $insert_hold_query = "INSERT INTO hold (StudentID, DateOfHold, HoldType) VALUES ('$student_id', '$date_of_hold', '$hold_type')";
    mysqli_query($conn, $insert_hold_query);

    // Redirect back to the same page with the studentID as a query parameter
    header('Location: manage_holds.php?studentID=' . $student_id . '&filter=' . $filter);
    exit;
}

// Handle form submission to remove a hold
if (isset($_POST['remove_hold'])) {
    $hold_id = mysqli_real_escape_string($conn, $_POST['hold_id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);

    // Delete the hold from the 'hold' table
    $delete_hold_query = "DELETE FROM hold WHERE HoldID = '$hold_id'";
    mysqli_query($conn, $delete_hold_query);

    // Redirect back to the same page with the studentID as a query parameter
    header('Location: manage_holds.php?studentID=' . $student_id . '&filter=' . $filter);
    exit;
}

// Retrieve a list of students and their holds from the database based on the filter
$filter_condition = '';
if ($filter === 'with_holds') {
    $filter_condition = 'AND hold.HoldID IS NOT NULL';
} elseif ($filter === 'without_holds') {
    $filter_condition = 'AND hold.HoldID IS NULL';
}

$select_students_query = "SELECT user.FirstName, user.LastName, student.StudentID, hold.HoldID, hold.DateOfHold, hold.HoldType 
                         FROM student
                         LEFT JOIN user ON student.StudentID = user.UID
                         LEFT JOIN hold ON student.StudentID = hold.StudentID
                         WHERE 1 $filter_condition";

$students_result = mysqli_query($conn, $select_students_query);
$students = [];

while ($row = mysqli_fetch_assoc($students_result)) {
    $student_id = $row['StudentID'];
    if (!isset($students[$student_id])) {
        $students[$student_id] = [
            'FirstName' => $row['FirstName'],
            'LastName' => $row['LastName'],
            'Holds' => [],
        ];
    }

    if ($row['HoldID']) {
        $students[$student_id]['Holds'][] = [
            'HoldID' => $row['HoldID'],
            'DateOfHold' => $row['DateOfHold'],
            'HoldType' => $row['HoldType'],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Holds</title>
    <style>
     body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <h1>Manage Holds</h1>
    <a href="Update_a_user1.php" class="btn">Back</a>

    <!-- Create Hold Form -->
    <!-- Create Hold Form -->
<form action="" method="post">
    <label for="student_id">Student ID:</label>
    <input type="text" name="student_id" value="<?php echo htmlspecialchars($studentID); ?>" required>
    <label for="date_of_hold">Date of Hold:</label>
    <input type="date" name="date_of_hold" required>
    <label for="hold_type">Hold Type:</label>
    <select name="hold_type" required>
        <option value="Academic">Academic</option>
        <option value="Financial">Financial</option>
        <option value="Disciplinary">Disciplinary</option>
		<option value="Health">Health</option>
    </select>
    <input type="submit" name="create_hold" value="Create Hold">
</form>


    <!-- Filter controls -->
    <div>
        <label for="filter">Filter:</label>
        <select id="filter" name="filter" onchange="applyFilter()">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Students</option>
            <option value="with_holds" <?php echo $filter === 'with_holds' ? 'selected' : ''; ?>>Students with Holds</option>
            <option value="without_holds" <?php echo $filter === 'without_holds' ? 'selected' : ''; ?>>Students without Holds</option>
        </select>
    </div>

    <!-- List of Students and Their Holds -->
    <table>
        <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Holds</th>
        </tr>
        <?php foreach ($students as $student_id => $student_data): ?>
            <tr>
                <td><?php echo htmlspecialchars($student_id); ?></td>
                <td><?php echo htmlspecialchars($student_data['FirstName']); ?></td>
                <td><?php echo htmlspecialchars($student_data['LastName']); ?></td>
                <td>
                    <?php if (!empty($student_data['Holds'])): ?>
                        <ul>
                            <?php foreach ($student_data['Holds'] as $hold): ?>
                                <li>
                                    Hold ID: <?php echo htmlspecialchars($hold['HoldID']); ?>
                                    - Date: <?php echo htmlspecialchars($hold['DateOfHold']); ?>
                                    - Type: <?php echo htmlspecialchars($hold['HoldType']); ?>
                                    <form action="" method="post">
                                        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                                        <input type="hidden" name="hold_id" value="<?php echo $hold['HoldID']; ?>">
                                        <input type="submit" name="remove_hold" value="Remove Hold">
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        No Holds
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
        function applyFilter() {
            const filter = document.getElementById('filter').value;
            window.location.href = `manage_holds.php?studentID=<?php echo $studentID; ?>&filter=${filter}`;
        }
    </script>
</body>
</html>
