<?php

@include 'config1.php';

session_start();

if (!isset($_SESSION['UID'])) {
    echo "<p style='font-size: 24px; color: red;'>Please log in to manage course sections and faculty history.</p>";
    exit;
}

$uid = $_SESSION['UID'];

function getFacultyType($conn, $faculty_id) {
    $query = "SELECT FacultyType FROM faculty WHERE FacultyID = '$faculty_id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['FacultyType'];
    }
    return null; 
}

function countFacultyClasses($conn, $faculty_id, $semester_id) {
    $query = "SELECT COUNT(*) AS classCount FROM facultyhistory WHERE FacultyID = '$faculty_id' AND SemesterID = '$semester_id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['classCount'];
    }
    return 0; 
}

function getCourseDeptId($conn, $course_id) {
    $query = "SELECT DeptID FROM course WHERE CourseID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['DeptID'];
    }
    mysqli_stmt_close($stmt);
    return null;
}

function getFacultyDeptId($conn, $faculty_id) {
    $query = "SELECT DeptID FROM facultydept WHERE FacultyID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $faculty_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['DeptID'];
    }
    mysqli_stmt_close($stmt);
    return null;
}

function hasTimeslotConflict($conn, $facultyID, $semesterID, $timeSlotID) {
    $query = "SELECT COUNT(*) AS conflictCount FROM coursesection WHERE FacultyID = ? AND SemesterID = ? AND TimeSlotID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $facultyID, $semesterID, $timeSlotID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['conflictCount'] > 0;
    }
    mysqli_stmt_close($stmt);
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseID = mysqli_real_escape_string($conn, $_POST['course_id']);
    $CRN = mysqli_real_escape_string($conn, $_POST['CRN']);
    $facultyID = mysqli_real_escape_string($conn, $_POST['faculty_id']);

    $timeslotParts = explode('_', mysqli_real_escape_string($conn, $_POST['timeslot']));
    if (count($timeslotParts) == 2) {
        list($timeSlotID, $roomID) = $timeslotParts;
    } else {
        echo "<p style='font-size: 24px; color: red;'>Invalid timeslot selected.</p>";
        return "<p style='font-size: 24px; color: red;'>Invalid timeslot selected.</p>";
    }

    $courseDeptId = getCourseDeptId($conn, $courseID);
    $facultyDeptId = getFacultyDeptId($conn, $facultyID);
    if ($courseDeptId !== $facultyDeptId) {
        echo "<p style='font-size: 24px; color: red;'>Error: Faculty member is not part of the department offering this course.</p>";
        return "<p style='font-size: 24px; color: red;'>Error: Faculty member is not part of the department offering this course.</p>";
    }

  
  
    $sectionNum = mysqli_real_escape_string($conn, $_POST['section_num']);
    $semesterID = mysqli_real_escape_string($conn, $_POST['semester_id']);
    $availableSeats = mysqli_real_escape_string($conn, $_POST['available_seats']);

    $checkQuery = "SELECT COUNT(*) AS sectionCount FROM coursesection WHERE CourseID = '$courseID' AND SectionNum = '$sectionNum'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if ($checkResult) {
        $row = mysqli_fetch_assoc($checkResult);
        $sectionCount = $row['sectionCount'];
        if ($sectionCount > 0) {
            echo "<p style='font-size: 24px; color: red;'>Error: Course section with the same CourseID and SectionNum already exists.</p>";
            header("refresh:5;url=createcoursesection.php"); 
            exit;
        }
    } else {
        echo "<p style='font-size: 24px; color: red;'>Error checking for an existing course section: " . mysqli_error($conn) . "</p>";
        header("refresh:5;url=createcoursesection.php"); 
        exit;
    }

    if (hasTimeslotConflict($conn, $facultyID, $semesterID, $timeSlotID)) {
        echo "<p style='font-size: 24px; color: red;'>Error: Faculty member has a timeslot conflict.</p>";
        return "<p style='font-size: 24px; color: red;'>Error: Faculty member has a timeslot conflict.</p>";
    }
    $facultyType = getFacultyType($conn, $facultyID);
    $numClasses = countFacultyClasses($conn, $facultyID, $semesterID);
    if (($facultyType == 'Full-time' && $numClasses >= 2) || ($facultyType == 'Part-time' && $numClasses >= 1)) {
        echo "<p style='font-size: 24px; color: red;'>Error: Faculty member cannot be assigned more classes.</p>";
        return "<p style='font-size: 24px; color: red;'>Error: Faculty member cannot be assigned more classes.</p>";
    }

    $facultyTable = ($facultyType == 'Full-time') ? 'facultyft' : 'facultypt';
    $incrementQuery = "UPDATE $facultyTable SET NumOfClass = NumOfClass + 1 WHERE FacultyID = '$facultyID'";
    $incrementResult = mysqli_query($conn, $incrementQuery);

    if (!$incrementResult) {
        echo "<p style='font-size: 24px; color: red;'>Error incrementing NumOfClass: " . mysqli_error($conn) . "</p>";
        return "<p style='font-size: 24px; color: red;'>Error incrementing NumOfClass: " . mysqli_error($conn) . "</p>";
    }

    $sectionQuery = "INSERT INTO coursesection (CRN, CourseID, SectionNum, FacultyID, TimeSlotID, RoomID, SemesterID, AvailableSeats) 
                     VALUES ('$CRN', '$courseID', '$sectionNum', '$facultyID', '$timeSlotID', '$roomID', '$semesterID', '$availableSeats')";
    $sectionResult = mysqli_query($conn, $sectionQuery);

    if ($sectionResult) {
        $facultyHistoryInsertQuery = "INSERT INTO facultyhistory (FacultyID, CourseID, CRN, SemesterID) 
                                      VALUES ('$facultyID', '$courseID', '$CRN', '$semesterID')";
        $facultyHistoryInsertResult = mysqli_query($conn, $facultyHistoryInsertQuery);

        if ($facultyHistoryInsertResult) {
            echo "<p style='font-size: 24px; color: green;'>Coursesection and faculty history created successfully!</p>";
        } else {
            echo "<p style='font-size: 24px; color: red;'>Error inserting data into faculty history table: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='font-size: 24px; color: red;'>Error creating course section: " . mysqli_error($conn) . "</p>";
    }
}
?>
