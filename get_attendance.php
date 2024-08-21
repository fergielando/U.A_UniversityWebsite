<?php
// Include database configuration and start session
@include 'config1.php';
session_start();

if (!isset($_GET['StudentID']) || !isset($_GET['CRN'])) {
    echo "Invalid parameters.";
    exit;
}

$studentID = $_GET['StudentID'];
$CRN = $_GET['CRN'];

// Fetch student's details
$studentQuery = "SELECT FirstName, LastName FROM user WHERE UID = '$studentID'";
$studentResult = mysqli_query($conn, $studentQuery);
$studentInfo = mysqli_fetch_assoc($studentResult);
$studentName = $studentInfo['FirstName'] . ' ' . $studentInfo['LastName'];

// Fetch attendance records for the selected student
$attendanceQuery = "SELECT ClassDate, Present FROM attendance WHERE StudentID = '$studentID' AND CRN = '$CRN'";
$attendanceResult = mysqli_query($conn, $attendanceQuery);

// Display attendance records in a table
echo "<h3>Attendance History for Student: $studentID - $studentName</h3>";
echo "<table>";
echo "<thead><tr><th>Date</th><th>Attendance</th></tr></thead>";
echo "<tbody>";
while ($row = mysqli_fetch_assoc($attendanceResult)) {
    echo "<tr>";
    echo "<td>" . $row['ClassDate'] . "</td>";
    echo "<td>" . ($row['Present'] ? 'Present' : 'Absent') . "</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
?>
