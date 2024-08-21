<?php
@include 'config1.php'; // Include your database configuration file

session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to delete courses.";
    exit;
}

$uid = $_SESSION['UID'];

// Check if CRN is provided via GET request
if (isset($_GET['CRN'])) {
    $crn = $_GET['CRN'];

    // Perform the course deletion based on the CRN
    $deleteQuery = "DELETE FROM coursesection WHERE CRN = $crn";
    $deleteResult = mysqli_query($conn, $deleteQuery);

    if ($deleteResult) {
        echo "Course with CRN $crn has been deleted successfully.";
    } else {
        echo "Error: Course deletion failed.";
    }
} else {
    echo "CRN not provided.";
}
?>

