<?php
session_start();
@include 'config1.php'; // Include your database configuration file

if (!isset($_SESSION['admin_name'])) {
   header('location:login_form1.php');
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $major_id = $_GET['id'];

    // Check if any students are enrolled in the major
    $enrollment_query = "SELECT * FROM studentmajor WHERE MajorID = '{$major_id}'";
    $enrollment_result = mysqli_query($conn, $enrollment_query);

    // If students are enrolled, don't allow deletion
    if (mysqli_num_rows($enrollment_result) > 0) {
        echo "Cannot delete the major as students are enrolled.";
    } else {
        // No students enrolled, proceed with deletion
        $delete_query = "DELETE FROM major WHERE MajorID = '{$major_id}'";

        if (mysqli_query($conn, $delete_query)) {
            echo "Major deleted successfully.";
        } else {
            echo "Error deleting major: " . mysqli_error($conn);
        }
    }
} else {
    echo "Invalid request or missing MajorID.";
}
?>
