<?php
@include 'config1.php'; // Include your database configuration file

session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to create a course.";
    exit;
}

$uid = $_SESSION['UID'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data from the form
    $courseID = mysqli_real_escape_string($conn, $_POST['course_id']);
    $courseName = mysqli_real_escape_string($conn, $_POST['course_name']);
    $deptID = mysqli_real_escape_string($conn, $_POST['dept_id']);
    $credits = mysqli_real_escape_string($conn, $_POST['credits']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $courseType = mysqli_real_escape_string($conn, $_POST['course_type']);

    // Insert data into the course table
    $courseQuery = "INSERT INTO course (CourseID, CourseName, DeptID, Credits, Description, CourseType) 
                    VALUES ('$courseID', '$courseName', '$deptID', '$credits', '$description', '$courseType')";
    $courseResult = mysqli_query($conn, $courseQuery);

    if ($courseResult) {
        // Parse timeslot and room from the selected option
        $timeslotParts = explode('_', mysqli_real_escape_string($conn, $_POST['timeslot']));
        if (count($timeslotParts) == 2) {
            list($timeSlotID, $roomID) = $timeslotParts;
        } else {
            echo "Invalid timeslot selected.";
            exit;
        }

        // Additional fields for coursesection
        $CRN = mysqli_real_escape_string($conn, $_POST['CRN']);
        $sectionNum = mysqli_real_escape_string($conn, $_POST['section_num']);
        $facultyID = mysqli_real_escape_string($conn, $_POST['faculty_id']);
        $semesterID = mysqli_real_escape_string($conn, $_POST['semester_id']);
        $availableSeats = mysqli_real_escape_string($conn, $_POST['available_seats']);

        // Insert data into coursesection table
        $sectionQuery = "INSERT INTO coursesection (CRN, CourseID, SectionNum, FacultyID, TimeSlotID, RoomID, SemesterID, AvailableSeats) 
                         VALUES ('$CRN', '$courseID', '$sectionNum', '$facultyID', '$timeSlotID', '$roomID', '$semesterID', '$availableSeats')";
        $sectionResult = mysqli_query($conn, $sectionQuery);

        if ($sectionResult) {
            // Input fields for course prerequisites
            $prCourseID = mysqli_real_escape_string($conn, $_POST['pr_course_id']);
            $minGrade = mysqli_real_escape_string($conn, $_POST['min_grade']);
            $dolu = mysqli_real_escape_string($conn, $_POST['dolu']);

            // Insert data into courseprerequisite table if needed
            $prerequisitesQuery = "INSERT INTO courseprerequisite (CourseID, PRcourseID, MinGrade, DOLU) 
                                  VALUES ('$courseID', '$prCourseID', '$minGrade', '$dolu')";
            $prerequisitesResult = mysqli_query($conn, $prerequisitesQuery);

               // Insert data into facultyhistory table
               $facultyHistoryInsertQuery = "INSERT INTO facultyhistory (FacultyID, CourseID, CRN, SemesterID) 
               VALUES ('$facultyID',  '$courseID', '$CRN', '$semesterID')";
$facultyHistoryInsertResult = mysqli_query($conn, $facultyHistoryInsertQuery);

if ($facultyHistoryInsertResult) {
echo "Course, coursesection, and faculty history created successfully!";
// Redirect or perform other actions as needed
} else {
echo "Error inserting data into faculty history table: " . mysqli_error($conn);
}

            if ($prerequisitesResult) {
                echo "Course created successfully!";
                // Redirect or perform other actions as needed
            } else {
                echo "Error creating course prerequisites: " . mysqli_error($conn);
            }
        } else {
            echo "Error creating course section: " . mysqli_error($conn);
        }
    } else {
        echo "Error creating course: " . mysqli_error($conn);
    }
}
?>
