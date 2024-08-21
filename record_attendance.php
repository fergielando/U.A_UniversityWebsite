<?php
// Include database configuration and start session
@include 'config1.php';
session_start();

// Check if the form has been submitted for attendance recording
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recordAttendance'])) {
    $studentID = $_POST['StudentID'];
    $CRN = $_POST['CRN'];
    $attendance = ($_POST['attendance'] === "present") ? 1 : 0; // 1 for Present, 0 for Absent

    // Fetch course days
    $courseDaysQuery = "SELECT day.Weekday FROM coursesection
                        JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
                        JOIN day ON timeslot.DayID = day.DayID
                        WHERE coursesection.CRN = '$CRN'";
    $courseDaysResult = mysqli_query($conn, $courseDaysQuery);
    $courseDays = [];
    while ($dayRow = mysqli_fetch_assoc($courseDaysResult)) {
        $courseDays[] = strtolower($dayRow['Weekday']);
    }

    // Add this code after fetching course details using CRN
    $courseIDQuery = "SELECT CourseID FROM coursesection WHERE CRN = '$CRN'";
    $courseIDResult = mysqli_query($conn, $courseIDQuery);

    if ($courseIDRow = mysqli_fetch_assoc($courseIDResult)) {
        $courseID = $courseIDRow['CourseID'];
    } else {
        echo "Course ID not found for CRN: $CRN";
        exit;
    }

    // Check if today is a course day
    $currentWeekday = strtolower(date('l'));
    if (in_array($currentWeekday, $courseDays)) {
        // Get the current date for ClassDate
        $classDate = date('Y-m-d'); // Assuming the format is 'YYYY-MM-DD'

        // Check if an attendance record already exists for the student, CRN, and class date
        $checkAttendanceQuery = "SELECT * FROM attendance WHERE StudentID = '$studentID' AND CRN = '$CRN' AND ClassDate = '$classDate'";
        $checkAttendanceResult = mysqli_query($conn, $checkAttendanceQuery);

        if (mysqli_num_rows($checkAttendanceResult) > 0) {
            // An attendance record already exists, so update it
            $updateAttendanceQuery = "UPDATE attendance SET Present = '$attendance' WHERE StudentID = '$studentID' AND CRN = '$CRN' AND ClassDate = '$classDate'";
            $result = mysqli_query($conn, $updateAttendanceQuery);
        } else {
            // No attendance record exists, so insert a new one
            $insertAttendanceQuery = "INSERT INTO attendance (StudentID, CRN, CourseID, ClassDate, Present) VALUES ('$studentID', '$CRN', '$courseID', '$classDate', '$attendance')";
            $result = mysqli_query($conn, $insertAttendanceQuery);
        }

        if ($result) {
            echo "Attendance recorded successfully.";
        } else {
            echo "Error recording attendance: " . mysqli_error($conn);
        }
    } else {
        echo "Today is not a course day.";
    }
}
?>
