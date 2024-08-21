<?php
// Include database configuration and start session
@include 'config1.php';
session_start();

if (!isset($_SESSION['UID'])) {
    echo "Please log in. <a href='login_page1.php'>Login Here</a>";
    exit;
}

if (!isset($_SESSION['admin_name'])) {
   header('location:login_form1.php');
}

// Check if CRN is provided
if (!isset($_GET['CRN'])) {
    echo "No course selected.";
    exit;
}

$CRN = $_GET['CRN'];

// Set default timezone (use before the others)
date_default_timezone_set('America/New_York');


// Fetch course details
$courseInfoQuery = "SELECT coursesection.CourseID, course.CourseName 
                    FROM coursesection
                    JOIN course ON coursesection.CourseID = course.CourseID
                    WHERE CRN = '$CRN'";
$courseInfoResult = mysqli_query($conn, $courseInfoQuery);
$courseID = '';
$courseName = '';


if ($courseRow = mysqli_fetch_assoc($courseInfoResult)) {
    $courseID = $courseRow['CourseID'];
    $courseName = $courseRow['CourseName'];
} else {
    echo "Course details not found.";
    exit;
}

// Fetch course period details and days
$periodQuery = "SELECT periodd.StartTime, periodd.EndTime, GROUP_CONCAT(DISTINCT timeslot.DayID) AS ClassDays
                FROM coursesection
                JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
                JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
                WHERE coursesection.CRN = '$CRN'
                GROUP BY periodd.StartTime, periodd.EndTime";
$periodResult = mysqli_query($conn, $periodQuery);

$currentDay = date('N'); // Get the numeric representation of the current day (1 for Monday, 2 for Tuesday, etc.)
$currentDateTime = time(); // Get current timestamp

$isWithinClassHours = false;

while ($period = mysqli_fetch_assoc($periodResult)) {
    $startTime = strtotime($period['StartTime']);
    $endTime = strtotime($period['EndTime']);
    $classDays = explode(',', $period['ClassDays']);

    // Check if it's a class day and within class hours
    if (in_array($currentDay, $classDays)) {
        if ($currentDateTime >= $startTime && $currentDateTime <= strtotime('23:59:59')) {
            $isWithinClassHours = true;
            break; // Stop checking once we find a valid time slot
        }
    }
}

// Fetch semester start and end dates based on CRN
$semesterDatesQuery = "SELECT semester.StartTime, semester.EndTime
                        FROM coursesection
                        JOIN semester ON coursesection.SemesterID = semester.SemesterID
                        WHERE coursesection.CRN = '$CRN'";
$semesterDatesResult = mysqli_query($conn, $semesterDatesQuery);
$semesterDates = mysqli_fetch_assoc($semesterDatesResult);

$semesterStartDate = strtotime($semesterDates['StartTime']);
$semesterEndDate = strtotime($semesterDates['EndTime']);

// Calculate the date 4 days after the semester end
$fourDaysAfterEnd = strtotime('+4 days', $semesterEndDate);

$currentDate = time();

$isWithinSemesterDates = ($currentDate >= $semesterEndDate);


// Fetch students, majors, and minors
$rosterQuery = "SELECT student.StudentID, user.FirstName, user.LastName, enrollment.Grade,
                       GROUP_CONCAT(DISTINCT major.MajorName SEPARATOR ', ') AS Majors,
                       GROUP_CONCAT(DISTINCT minor.MinorName SEPARATOR ', ') AS Minors, logintable.Email
                FROM enrollment
                INNER JOIN student ON enrollment.StudentID = student.StudentID
                INNER JOIN user ON student.StudentID = user.UID
				INNER JOIN logintable ON student.StudentID = logintable.UID
                LEFT JOIN studentmajor ON student.StudentID = studentmajor.StudentID
                LEFT JOIN major ON studentmajor.MajorID = major.MajorID
                LEFT JOIN studentminor ON student.StudentID = studentminor.StudentID
                LEFT JOIN minor ON studentminor.MinorID = minor.MinorID
                WHERE enrollment.CRN = '$CRN'
                GROUP BY student.StudentID";
$rosterResult = mysqli_query($conn, $rosterQuery);
$students = mysqli_fetch_all($rosterResult, MYSQLI_ASSOC);

// Function to handle grade assignment
function assignGrade($conn, $studentID, $CRN, $grade) {
    mysqli_begin_transaction($conn);

    try {
        // Update grade in enrollment table
        $updateEnrollment = "UPDATE enrollment SET Grade = '$grade' WHERE StudentID = '$studentID' AND CRN = '$CRN'";
        mysqli_query($conn, $updateEnrollment);

        // Update or insert grade in studenthistory table
        // Check if a record already exists
        $checkHistory = "SELECT * FROM studenthistory WHERE StudentID = '$studentID' AND CRN = '$CRN'";
        $historyResult = mysqli_query($conn, $checkHistory);

        if (mysqli_num_rows($historyResult) > 0) {
            // Update existing record
            $updateHistory = "UPDATE studenthistory SET Grade = '$grade' WHERE StudentID = '$studentID' AND CRN = '$CRN'";
            mysqli_query($conn, $updateHistory);
        } else {
            // Insert new record
            $insertHistory = "INSERT INTO studenthistory (StudentID, CRN, Grade) VALUES ('$studentID', '$CRN', '$grade')";
            mysqli_query($conn, $insertHistory);
        }

        // Commit transaction
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn); // Rollback transaction on error
        echo "Error updating grade: " . $e->getMessage();
    }
}

// Check if the form has been submitted for grade assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assignGrade'])) {
    assignGrade($conn, $_POST['StudentID'], $_POST['CRN'], $_POST['Grade']);
}

// Fetch SemesterID for the CRN
$semesterIDQuery = "SELECT SemesterID FROM coursesection WHERE CRN = '$CRN'";
$semesterIDResult = mysqli_query($conn, $semesterIDQuery);
$semesterIDRow = mysqli_fetch_assoc($semesterIDResult);

$semesterID = $semesterIDRow['SemesterID']; // Extract the SemesterID value


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Class Roster for Course <?php echo htmlspecialchars($CRN); ?> - Course ID: <?php echo htmlspecialchars($courseID); ?></title>


   <style>
       /* Styles for the header */
       .header {
         background: #000; 
         color: #fff; 
         padding: 20px;
         background-color: #000;
         text-align: left;
         margin-top: 20px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .header h1 {
         font-size: 36px; 
         margin: 0;
      }

      .header .logo {
         width: 50px;
         height: 50px;
      }
      .department-container {
         padding: 20px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
      }

      table, th, td {
         border: 1px solid #000;
      }

      th, td {
         padding: 8px;
         text-align: left;
      }
      th {
      background-color: #f2f2f2; /* Gives a slight background color to the header */
   }

   /* Style for every other row */
   tr:nth-child(even) {
      background: linear-gradient(90deg, #87CEEB, #FFFFFF); /* Light blue to white gradient */
   }

    .button-container {
         display: flex;
         align-items: center;
      }

      .button-container .btn {
         display: inline-block;
         padding: 10px 30px;
         font-size: 20px;
         background: #000; 
         color: #fff; 
         margin: 0 20px; 
         text-transform: capitalize;
         text-decoration: none; 
         border-radius: 5px;
         background-color: #000;
      }

      .button-container .btn:hover {
         background: #333;
      }

   </style>
</head>
<body>
<header class="header">
      <img src="ua.png" alt="U.A. Logo" class="logo">
      <h1>Welcome to U.A. University</h1>
      <div class="button-container">
         <a href="admin_page1.php" class="btn">Back</a>
      </div>
   </header>

<div style="text-align: center; margin-top: 20px;">
    <?php
    // Get current date and time in desired formats
    $currentDate = date('Y-m-d');
    $currentTime = date('h:i A');
    echo "<p>Current Date: $currentDate</p>";
    echo "<p>Current Time: $currentTime</p>";

    // Fetch and display class period details
    $periodQuery = "SELECT periodd.StartTime, periodd.EndTime
                    FROM coursesection
                    JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
                    JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
                    WHERE coursesection.CRN = '$CRN'";
    $periodResult = mysqli_query($conn, $periodQuery);

    if ($period = mysqli_fetch_assoc($periodResult)) {
        $startTime = date('h:i A', strtotime($period['StartTime']));
        $endTime = date('h:i A', strtotime($period['EndTime']));
        echo "<p>This class meets from $startTime to $endTime</p>";
    } else {
        echo "<p>Class period details not found.</p>";
    }
    ?>
</div>

	<div style="text-align: center; margin-top: 20px;">
    <?php
        // Get current date and time in desired formats
        $currentDate = date('Y-m-d');
        $currentTime = date('h:i A');
		
		// Map DayIDs to day names
        $daysMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        // Display class days
        $classDaysString = implode(', ', array_map(function ($dayID) use ($daysMap) {
            return $daysMap[$dayID];
        }, $classDays));

        echo "<p>This class meets on $classDaysString</p>";
    ?>
</div>

   <h1>Class Roster for Course: <?php echo htmlspecialchars($courseName); ?> (CRN: <?php echo htmlspecialchars($CRN); ?>)</h1>
   
   <h1><?php
// Fetch FacultyID and Faculty Name
$facultyInfoQuery = "SELECT user.UID AS FacultyID, user.FirstName AS FacultyFirstName, user.LastName AS FacultyLastName
                    FROM coursesection
                    JOIN user ON coursesection.FacultyID = user.UID
                    WHERE CRN = '$CRN'";
$facultyInfoResult = mysqli_query($conn, $facultyInfoQuery);
$facultyInfo = mysqli_fetch_assoc($facultyInfoResult);

// Display Faculty information
if ($facultyInfo) {
    echo "<p>This class is taught by " . htmlspecialchars($facultyInfo['FacultyFirstName']) . " " . htmlspecialchars($facultyInfo['FacultyLastName']) . " (FacultyID: " . htmlspecialchars($facultyInfo['FacultyID']) . ")</p>";
    echo "<p>Faculty Profile: <a href='adminfaculty_personalinfo.php?UID=" . htmlspecialchars($facultyInfo['FacultyID']) . "' target='_blank'>View Profile</a></p>";
} else {
    echo "<p>Faculty information not available.</p>";
}
?></h1>

   <table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
			  <th>Email</th>
            <th>Major(s)</th>
            <th>Minor</th>
            <th>Grade</th>
            <th>Assign Grade</th>
            <th>Attendance</th> <!-- New column for Attendance -->
			  <th>Attendance History</th> <!-- New column header for Attendance History -->
			  <th>View Academic Profile </th>
        </tr>
    </thead>
    <tbody>
        <?php
    // Iterate through the first 20 students
    $counter = 0;
    foreach ($students as $student):
        if ($counter >= 20) {
            break; // Stop looping after 20 students
        }
        ?>
            <tr>
                <td><?php echo htmlspecialchars($student['StudentID']); ?></td>
                <td><?php echo htmlspecialchars($student['FirstName']); ?></td>
                <td><?php echo htmlspecialchars($student['LastName']); ?></td>
				   <td><?php echo htmlspecialchars($student['Email']); ?></td>
                <td><?php echo isset($student['Majors']) ? htmlspecialchars($student['Majors']) : ''; ?></td>
                <td><?php echo isset($student['Minors']) ? htmlspecialchars($student['Minors']) : ''; ?></td>
                <td><?php echo htmlspecialchars($student['Grade']); ?></td>
                <td>
		              <?php if ($isWithinSemesterDates): ?>
                    <form action="class_roster.php?CRN=<?php echo htmlspecialchars($CRN); ?>" method="post">
                        <input type="hidden" name="StudentID" value="<?php echo htmlspecialchars($student['StudentID']); ?>">
                        <input type="hidden" name="CRN" value="<?php echo htmlspecialchars($CRN); ?>">
                        <input type="text" name="Grade" placeholder="Enter grade">
                        <button type="submit" name="assignGrade">Assign</button>
                    </form>
					<?php else: ?>
					<span>Grade assignment is not available until <?php echo date('Y-m-d', $semesterEndDate);?>.</span>
					<?php endif; ?>
                </td>
                <td>
                    <?php if ($isWithinClassHours && $semesterID == '20232'): ?>
                        <form action="record_attendance.php" method="post">
                            <input type="hidden" name="StudentID" value="<?php echo htmlspecialchars($student['StudentID']); ?>">
                            <input type="hidden" name="CRN" value="<?php echo htmlspecialchars($CRN); ?>">
                            <!-- Create a dropdown calendar for attendance -->
                            <select name="attendance_date">
                                <?php
                                // Fetch attendance dates for the current date only
                                $currentDate = date('Y-m-d'); // Get the current date
                                $attendanceDatesQuery = "SELECT DISTINCT ClassDate, Present FROM attendance WHERE StudentID = '" . $student['StudentID'] . "' AND CRN = '$CRN' AND ClassDate = '$currentDate'";
                                $attendanceDatesResult = mysqli_query($conn, $attendanceDatesQuery);

                                // Check if there are any attendance records for the current date
                                if (mysqli_num_rows($attendanceDatesResult) > 0) {
                                    // There are attendance records for the current date
                                    $attendanceDateRow = mysqli_fetch_assoc($attendanceDatesResult);
                                    $currentDate = $attendanceDateRow['ClassDate'];
                                    $currentAttendanceStatus = $attendanceDateRow['Present'];
                                } else {
                                    // No attendance records for the current date
                                    $currentDate = date('Y-m-d'); // Get the current date
                                    $currentAttendanceStatus = ''; // No attendance status
                                }

                                echo "<option value='$currentDate'>$currentDate</option>";
                                ?>
                            </select>
                            <select name="attendance">
                                <option value="present" <?php if ($currentAttendanceStatus === 'present') echo 'selected'; ?>>Present</option>
                                <option value="absent" <?php if ($currentAttendanceStatus === 'absent') echo 'selected'; ?>>Absent</option>
                            </select>
                            <button type="submit" name="recordAttendance">Record</button>
                        </form>
                    <?php else: ?>
                        <span>Not a course day</span>
                    <?php endif; ?>
                </td>
					<td>
                    <button onclick="showAttendance('<?php echo htmlspecialchars($student['StudentID']); ?>')">Show Attendance History</button>
                </td>
				   <td>
                    <a href="view_academic_profile1.php?UID=<?php echo htmlspecialchars($student['StudentID']); ?>" target="_blank">View Profile</a>
                </td>
            </tr>
        <?php
        $counter++;
    endforeach;
    ?>
    </tbody>
</table>

<div id="attendanceHistory">
    <!-- Attendance history will be displayed here -->
</div>

<script>
    function showAttendance(studentID) {
        // AJAX request to fetch attendance records for the selected student
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("attendanceHistory").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "get_attendance.php?StudentID=" + studentID + "&CRN=<?php echo $CRN; ?>", true);
        xhttp.send();
    }
</script>

</body>
</html>