<?php
@include 'config1.php';

// Retrieve StudentID (you can use session or URL parameter)
if (isset($_SESSION['StudentID'])) {
    $StudentID = $_SESSION['StudentID'];
} elseif (isset($_GET['StudentID'])) {
    $StudentID = $_GET['StudentID'];
} else {
    // Handle missing StudentID
    echo "StudentID is missing.";
    exit;
}

// Function to generate all days of the week between two dates
function getDaysOfWeek($startDate, $endDate, $dayOfWeek) {
    $daysOfWeek = [];
    $currentDate = strtotime($startDate);
    $endTimestamp = strtotime($endDate);

    while ($currentDate <= $endTimestamp) {
        if (date('N', $currentDate) == $dayOfWeek) {
            $daysOfWeek[] = date('Y-m-d', $currentDate);
        }
        $currentDate = strtotime('+1 day', $currentDate);
    }

    return $daysOfWeek;
}

// Generate all days of the week (e.g., Monday to Sunday) starting from "2023-08-30" until today
$startDate = "2023-08-30";
$endDate = date("Y-m-d");

// Define the day of the class (e.g., 1 for Monday, 2 for Tuesday, etc.)
$dayOfClass = 1; // Change this value according to your class day

$daysOfWeek = getDaysOfWeek($startDate, $endDate, $dayOfClass);

// Query to retrieve attendance records for the student and the corresponding days of the week
$attendanceQuery = "SELECT ClassDate, Present
                    FROM attendance
                    WHERE StudentID = '$StudentID'
                    AND ClassDate BETWEEN '$startDate' AND '$endDate'
                    ORDER BY ClassDate";

$attendanceResult = mysqli_query($conn, $attendanceQuery);

// Create an associative array to store attendance data
$attendanceData = [];
while ($row = mysqli_fetch_assoc($attendanceResult)) {
    $attendanceData[$row['ClassDate']] = $row['Present'];
}

// Get the current day of the week
$currentDayOfWeek = date('N');

// Display the attendance system
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }

        h1 {
            color: #333;
        }

        h2 {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        td a {
            text-decoration: none;
            color: #333;
        }

        td a:hover {
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Attendance System</h1>
    <h2>StudentID: <?php echo $StudentID; ?></h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Attendance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daysOfWeek as $dayOfWeek): ?>
                <tr>
                    <td><?php echo $dayOfWeek; ?></td>
                    <td>
                        <?php
                        if (isset($attendanceData[$dayOfWeek])) {
                            $attendanceStatus = $attendanceData[$dayOfWeek];
                            echo ($attendanceStatus == 1) ? 'Present' : 'Absent';
                        } else {
                            echo 'Not Recorded';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($currentDayOfWeek == $dayOfClass) {
                            // Allow editing for the day of the class
                            echo '<a href="editattendance.php?StudentID=' . $StudentID . '&Date=' . $dayOfWeek . '">Edit</a>';
                        } else {
                            echo 'Not Editable';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
