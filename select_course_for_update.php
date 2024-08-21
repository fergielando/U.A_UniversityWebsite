<?php
if (isset($_GET['CourseID'])) {
    $courseID = $_GET['CourseID'];
    // Optional: Perform any validation or additional checks

    // Redirect to the update page
    header("Location: update_course.php?CourseID=" . $courseID);
    exit;
} else {
    echo "No Course ID specified.";
}
?>
