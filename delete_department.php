<?php
@include 'config1.php'; // Include your database configuration file

session_start();

if (!isset($_SESSION['admin_name'])) {
   header('location:login_form1.php');
}

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to manage Departments.";
    exit;
}

$uid = $_SESSION['UID'];

// Process delete request
if (isset($_GET['id'])) {
    $deptid = $_GET['id']; // Fetching 'id' parameter from the URL

    // Check if the department is used in any other tables
    $checkCourseQuery = "SELECT COUNT(*) AS CourseCount FROM course WHERE DeptID = '$deptid'";
    $checkFacultyDeptQuery = "SELECT COUNT(*) AS FacultyDeptCount FROM facultydept WHERE DeptID = '$deptid'";
    $checkMajorQuery = "SELECT COUNT(*) AS MajorCount FROM major WHERE DeptID = '$deptid'";
    $checkMinorQuery = "SELECT COUNT(*) AS MinorCount FROM minor WHERE DeptID = '$deptid'";

    $courseResult = mysqli_query($conn, $checkCourseQuery);
    $facultyDeptResult = mysqli_query($conn, $checkFacultyDeptQuery);
    $majorResult = mysqli_query($conn, $checkMajorQuery);
    $minorResult = mysqli_query($conn, $checkMinorQuery);

    $courseCount = mysqli_fetch_assoc($courseResult)['CourseCount'];
    $facultyDeptCount = mysqli_fetch_assoc($facultyDeptResult)['FacultyDeptCount'];
    $majorCount = mysqli_fetch_assoc($majorResult)['MajorCount'];
    $minorCount = mysqli_fetch_assoc($minorResult)['MinorCount'];

    if ($courseCount > 0 || $facultyDeptCount > 0 || $majorCount > 0 || $minorCount > 0) {
        echo "Cannot delete department as it is associated with Courses/Faculty/Majors/Minors";
    } else {
        // Perform the department deletion if it's not associated with any other tables
        $deleteQuery = "DELETE FROM dept WHERE DeptID = '$deptid'";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        if ($deleteResult) {
            echo "Department with DeptID $deptid has been deleted successfully.";
        } else {
            echo "Error: Department deletion failed.";
        }
    }
} else {
    echo "DeptID not provided.";
}
?>
