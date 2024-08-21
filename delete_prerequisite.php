<?php
// Include your database connection
include 'config1.php';
session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to delete course prerequisites.";
    exit;
}

// Check if the prerequisite_id and course_id are set and are valid numbers
if(isset($_GET['course_id']) && isset($_GET['prerequisite_id'])) {
    $course_id = $_GET['course_id'];
    $prerequisite_id = $_GET['prerequisite_id'];

    // Sanitize input
    $course_id = mysqli_real_escape_string($conn, $course_id);
    $prerequisite_id = mysqli_real_escape_string($conn, $prerequisite_id);

    // Construct the DELETE query for the prerequisite
    $deleteQuery = "DELETE FROM courseprerequisite WHERE CourseID = '$course_id' AND PRcourseID = '$prerequisite_id'";

    // Execute the DELETE query
    if (mysqli_query($conn, $deleteQuery)) {
        echo "Prerequisite deleted successfully.";
    } else {
        echo "Error deleting prerequisite: " . mysqli_error($conn);
    }
} else {
    echo "Missing parameters for deletion.";
}
?>
