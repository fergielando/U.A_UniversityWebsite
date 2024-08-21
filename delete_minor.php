<?php
session_start();
@include 'config1.php'; // Include your database configuration file

if (!isset($_SESSION['admin_name'])) {
   header('location:login_form1.php');
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $minor_id = $_GET['id'];

    // Check if any students are enrolled in the minor
    $enrollment_query = "SELECT * FROM studentminor WHERE MinorID = '{$minor_id}'";
    $enrollment_result = mysqli_query($conn, $enrollment_query);

    // If students are enrolled, don't allow deletion
    if (mysqli_num_rows($enrollment_result) > 0) {
        echo "Cannot delete the minor as students are enrolled.";
    } else {
        // No students enrolled, proceed with deletion
        $delete_query = "DELETE FROM minor WHERE MinorID = '{$minor_id}'";

        if (mysqli_query($conn, $delete_query)) {
            echo "Minor deleted successfully.";
        } else {
            echo "Error deleting minor: " . mysqli_error($conn);
        }
    }
} else {
    echo "Invalid request or missing MinorID.";
}
?>
