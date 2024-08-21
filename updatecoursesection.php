<?php

@include 'config1.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form1.php');
    exit;
}


if (isset($_GET['CRN'])) {
    $CRN = mysqli_real_escape_string($conn, $_GET['CRN']);

    
    $query = "SELECT * FROM coursesection WHERE CRN = '$CRN'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        
        $currentCourseID = $row['CourseID']; 
        $currentSectionNum = $row['SectionNum']; 
        $currentFacultyID = $row['FacultyID']; 
    } else {
        showException("Course section not found.");
    }
} else {
    showException("No CRN provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $sectionNum = mysqli_real_escape_string($conn, $_POST['section_num']);
    $facultyID = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $semesterID = mysqli_real_escape_string($conn, $_POST['semester_id']);
    $availableSeats = mysqli_real_escape_string($conn, $_POST['available_seats']);

    
    if (isset($_POST['timeslot_room'])) {
        list($timeslotID, $roomID) = explode('_', $_POST['timeslot_room']);
        $timeslotID = mysqli_real_escape_string($conn, $timeslotID);
        $roomID = mysqli_real_escape_string($conn, $roomID);
    } else {
        showException("Timeslot and room information is required.");
    }

      
      $timeSlotConflictQuery = "SELECT * FROM coursesection 
      WHERE TimeSlotID = '$timeslotID' 
      AND FacultyID = '$facultyID' 
      AND CRN != '$CRN'";
  
  $timeSlotConflictResult = mysqli_query($conn, $timeSlotConflictQuery);
  
  if (mysqli_num_rows($timeSlotConflictResult) > 0) {
      showException("Faculty member already has a class scheduled during the selected time slot.");
  }
    
    mysqli_begin_transaction($conn);

    try {
        
        if ($facultyID != $currentFacultyID) {
            
            if ($currentFacultyID) {
                
                $currentFacultyFTCheck = mysqli_query($conn, "SELECT NumOfClass FROM facultyft WHERE FacultyID = '$currentFacultyID'");
                $currentFacultyPTCheck = mysqli_query($conn, "SELECT NumOfClass FROM facultypt WHERE FacultyID = '$currentFacultyID'");

                if (mysqli_num_rows($currentFacultyFTCheck) > 0) {
                    
                    mysqli_query($conn, "UPDATE facultyft SET NumOfClass = NumOfClass - 1 WHERE FacultyID = '$currentFacultyID'");
                } elseif (mysqli_num_rows($currentFacultyPTCheck) > 0) {
                    
                    mysqli_query($conn, "UPDATE facultypt SET NumOfClass = NumOfClass - 1 WHERE FacultyID = '$currentFacultyID'");
                }
            }

            
            $facultyCheckFT = mysqli_query($conn, "SELECT NumOfClass FROM facultyft WHERE FacultyID = '$facultyID'");
            $facultyCheckPT = mysqli_query($conn, "SELECT NumOfClass FROM facultypt WHERE FacultyID = '$facultyID'");

            if (mysqli_num_rows($facultyCheckFT) > 0) {
                
                $ftRow = mysqli_fetch_assoc($facultyCheckFT);
                if ($ftRow['NumOfClass'] >= 2) {
                    throw new Exception("Full-time faculty member can only teach up to 2 classes.");
                }
                mysqli_query($conn, "UPDATE facultyft SET NumOfClass = NumOfClass + 1 WHERE FacultyID = '$facultyID'");
            } elseif (mysqli_num_rows($facultyCheckPT) > 0) {
                
                $ptRow = mysqli_fetch_assoc($facultyCheckPT);
                if ($ptRow['NumOfClass'] >= 1) {
                    throw new Exception("Part-time faculty member can only teach 1 class.");
                }
                mysqli_query($conn, "UPDATE facultypt SET NumOfClass = NumOfClass + 1 WHERE FacultyID = '$facultyID'");
            } else {
                throw new Exception("Faculty ID not found in full-time or part-time tables.");
            }
        }

        
        if ($sectionNum != $currentSectionNum) {
            $checkQuery = "SELECT * FROM coursesection WHERE SectionNum = '$sectionNum' AND CourseID = '$currentCourseID' AND CRN != '$CRN'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if(mysqli_num_rows($checkResult) > 0) {
                throw new Exception("A course section with this number already exists for the same course.");
            }
        }

        
        $updateQuery = "UPDATE coursesection SET 
                        SectionNum = '$sectionNum', 
                        FacultyID = '$facultyID', 
                        RoomID = '$roomID', 
                        TimeSlotID = '$timeslotID',
                        SemesterID = '$semesterID', 
                        AvailableSeats = '$availableSeats' 
                        WHERE CRN = '$CRN'";
        
        $updateResult = mysqli_query($conn, $updateQuery);

        if (!$updateResult) {
            throw new Exception("Error updating course section: " . mysqli_error($conn));
        }

        
        mysqli_commit($conn);
        showSuccess("Course section with CRN $CRN updated successfully.");

    } catch (Exception $e) {
        
        mysqli_rollback($conn);
        showException($e->getMessage());
    }
}


function showSuccess($message) {
    echo "<div class='message success-message' style='font-size: 24px; color: green;'>$message</div>";
    echo "<script>setTimeout(function() { window.location.href = 'master_schedule1.php'; }, 5000);</script>";
    exit;
}


function showException($message) {
    echo "<div class='message error-message' style='font-size: 24px; color: red;'>$message</div>";
    echo "<script>setTimeout(function() { window.location.href = 'master_schedule1.php'; }, 5000);</script>";
    exit;
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
 

    <style>


.message {
    font-size: 24px;
    padding: 10px;
    margin-bottom: 15px;
    text-align: center;
    color: red;
}

.success-message {
    color: green;
}
</style>
