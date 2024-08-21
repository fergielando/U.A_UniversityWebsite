<?php

@include 'config1.php';

session_start();


if (!isset($_SESSION['UID'])) {
    echo "Please log in to create a course.";
    exit;
}

$uid = $_SESSION['UID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $courseID = mysqli_real_escape_string($conn, $_POST['course_id']);
    $courseName = mysqli_real_escape_string($conn, $_POST['course_name']);
    $deptID = mysqli_real_escape_string($conn, $_POST['dept_id']);
    $credits = mysqli_real_escape_string($conn, $_POST['credits']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $courseType = mysqli_real_escape_string($conn, $_POST['course_type']);

    
    $checkQuery = "SELECT COUNT(*) as count FROM course WHERE CourseName = '$courseName'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (!$checkResult) {
        echo "Error checking course name: " . mysqli_error($conn);
        exit;
    }

    $courseExists = mysqli_fetch_assoc($checkResult)['count'];

    if ($courseExists > 0) {
        echo "Course with the same name already exists. Please choose a different course name.";
    } else {
        
        $courseQuery = "INSERT INTO course (CourseID, CourseName, DeptID, Credits, Description, CourseType) 
                        VALUES ('$courseID', '$courseName', '$deptID', '$credits', '$description', '$courseType')";
        $courseResult = mysqli_query($conn, $courseQuery);
    }
    if ($courseResult) {
        
         if (isset($_POST['prerequisites']) && is_array($_POST['prerequisites']) && !empty(array_filter($_POST['prerequisites']))) {
            $prerequisiteIDs = $_POST['prerequisites'];
            $minGrades = $_POST['min_grade'];

            
            for ($i = 0; $i < count($prerequisiteIDs); $i++) {
                $prerequisiteID = mysqli_real_escape_string($conn, $prerequisiteIDs[$i]);
                $minGrade = mysqli_real_escape_string($conn, $minGrades[$i]);

                
                $prerequisiteQuery = "INSERT INTO courseprerequisite (CourseID, PRcourseID, MinGrade) 
                                      VALUES ('$courseID', '$prerequisiteID', '$minGrade')";
                $prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);

                if (!$prerequisiteResult) {
                    echo "Error creating course prerequisite for $prerequisiteID: " . mysqli_error($conn);
                    
                }
            }
        }

        
        echo "Course created successfully!";
    
    header("Location: course_catalog1.php");
    exit(); 
} else {
    $error_message = "Error creating course: " . mysqli_error($conn);
    
    header("Location: error_page.php?message=" . urlencode($error_message));
    exit();
}
}
?>
