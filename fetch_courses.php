<?php
// Include your database configuration file
@include 'config1.php';

if (isset($_POST['dept_id'])) {
    $selectedDept = $_POST['dept_id'];

    // Prepare the SQL query to prevent SQL injection
    $query = $conn->prepare("SELECT c.CourseID, c.CourseName, c.Credits, c.CourseType, pc.PRcourseID AS PrerequisiteCourse
                             FROM course c
                             LEFT JOIN courseprerequisite pc ON c.CourseID = pc.CourseID
                             WHERE c.DeptID = ?");

    // Bind the department ID to the query
    $query->bind_param("s", $selectedDept);

    // Execute the query
    $query->execute();

    // Store the result
    $result = $query->get_result();

    if ($result && mysqli_num_rows($result) > 0) {
        $courses = array();
        while ($row = mysqli_fetch_assoc($result)) {
            // Check if PrerequisiteCourse is null, and set it to 'N/A' if null
            $row['PrerequisiteCourse'] = ($row['PrerequisiteCourse'] !== null) ? $row['PrerequisiteCourse'] : 'N/A';
            $courses[] = $row;
        }
        echo json_encode($courses);
    } else {
        echo json_encode(array()); // Return an empty array if no courses found
    }

    // Close the statement
    $query->close();
} else {
    echo json_encode(array()); // Return an empty array if no department ID provided
}
?>
