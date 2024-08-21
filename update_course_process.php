<?php
@include 'config1.php'; // Database configuration

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseId = $_POST['CourseID'];

    $fieldsToUpdate = [];
    $params = [];
    $types = '';

    // Check and add CourseName to the update query if provided
    if (isset($_POST['CourseName']) && $_POST['CourseName'] !== '') {
        $fieldsToUpdate[] = "CourseName = ?";
        $params[] = $_POST['CourseName'];
        $types .= 's'; // 's' for string type
    }

    // Repeat for other fields
    if (isset($_POST['DeptID']) && $_POST['DeptID'] !== '') {
        $fieldsToUpdate[] = "DeptID = ?";
        $params[] = $_POST['DeptID'];
        $types .= 'i'; // 'i' for integer type
    }

    if (isset($_POST['Credits']) && $_POST['Credits'] !== '') {
        $fieldsToUpdate[] = "Credits = ?";
        $params[] = $_POST['Credits'];
        $types .= 'i'; // 'i' for integer type
    }

    if (isset($_POST['Description']) && $_POST['Description'] !== '') {
        $fieldsToUpdate[] = "Description = ?";
        $params[] = $_POST['Description'];
        $types .= 's'; // 's' for string type
    }

    if (isset($_POST['CourseType']) && $_POST['CourseType'] !== '') {
        $fieldsToUpdate[] = "CourseType = ?";
        $params[] = $_POST['CourseType'];
        $types .= 's'; // 's' for string type
    }

    // Ensure there are fields to update
    if (count($fieldsToUpdate) > 0) {
        $query = "UPDATE course SET " . implode(', ', $fieldsToUpdate) . " WHERE CourseID = ?";
        $params[] = $courseId;
        $types .= 'i'; // Adding 'i' for integer (CourseID)

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
    }

    // Redirect back to course list or show a success message
    header("Location: course_catalog1.php");
    exit;
}
?>
