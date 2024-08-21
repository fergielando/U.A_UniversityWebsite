<?php
// Include your database configuration file
@include 'config1.php';

if (isset($_POST['dept_id'])) {
    $selectedDept = $_POST['dept_id'];

    // Fetch courses for the selected department, including CourseType and Prerequisite Course
   // Fetch courses for the selected department from the course table
   $query = "
   SELECT DISTINCT c.CourseID, c.CourseName, c.Credits, c.CourseType, pc.PRcourseID AS PrerequisiteCourse
   FROM course c
   LEFT JOIN courseprerequisite pc ON c.CourseID = pc.CourseID
   WHERE c.DeptID = '$selectedDept'";


    $result = mysqli_query($conn, $query);

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
} else {
    echo json_encode(array()); // Return an empty array if no department ID provided
}
?>
