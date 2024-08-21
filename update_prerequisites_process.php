<?php
@include 'config1.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $prerequisite_ids = $_POST['prerequisite_ids'];
    $min_grades = $_POST['min_grades'];
    $prerequisite_ids_original = $_POST['prerequisite_ids_original'];

    
    for ($i = 0; $i < count($prerequisite_ids_original); $i++) {
        $prerequisiteID = mysqli_real_escape_string($conn, $prerequisite_ids[$i]);
        $minGrade = mysqli_real_escape_string($conn, $min_grades[$i]);
        $originalID = mysqli_real_escape_string($conn, $prerequisite_ids_original[$i]);

        
        if (!empty($prerequisiteID) && !empty($minGrade)) {
            
            $updatePrerequisiteQuery = "UPDATE courseprerequisite SET PRcourseID='$prerequisiteID', MinGrade='$minGrade', DOLU=NOW() WHERE CourseID='$course_id' AND PRcourseID='$originalID'";
            mysqli_query($conn, $updatePrerequisiteQuery);
        } elseif (!empty($originalID)) {
            
            
            $updatePrerequisiteQuery = "UPDATE courseprerequisite SET PRcourseID=NULL, MinGrade=NULL, DOLU=NOW() WHERE CourseID='$course_id' AND PRcourseID='$originalID'";
            mysqli_query($conn, $updatePrerequisiteQuery);
        }
    }

    
    header("Location: course_catalog1.php?id=$course_id");
    exit();
}
?>
