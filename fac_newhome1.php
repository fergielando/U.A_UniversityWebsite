<?php
// Include database configuration file and start session
@include 'config1.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['UID'])) {
    echo "Please log in. <a href='login_page1.php'>Login Here</a>";
    exit;
}

$UID = $_SESSION['UID'];

// Fetch faculty name and position
$facultyQuery = "SELECT user.FirstName, faculty.Position FROM user 
                 JOIN faculty ON user.UID = faculty.FacultyID
                 WHERE user.UID = '$UID'";
$facultyResult = mysqli_query($conn, $facultyQuery);
$facultyName = '';
$facultyPosition = '';

if ($facultyRow = mysqli_fetch_assoc($facultyResult)) {
    $facultyName = $facultyRow['FirstName'];
    $facultyPosition = $facultyRow['Position'];
} else {
    echo "Faculty details not found.";
    exit;
}

// Fetch the courses assigned to the faculty
$scheduleQuery = "SELECT 
cs.CRN, 
cs.CourseID, 
cs.AvailableSeats, 
GROUP_CONCAT(DISTINCT d.Weekday ORDER BY d.Weekday SEPARATOR '/') AS Weekdays, 
cs.SemesterID,  -- Added SemesterID
s.SemesterName,
ts.TimeSlotID, 
d.Weekday, 
c.CourseName, 
r.RoomNum, 
b.BuildingName, 
p.StartTime, 
p.EndTime
FROM 
coursesection cs
JOIN 
timeslot ts ON cs.TimeSlotID = ts.TimeSlotID 
JOIN 
day d ON ts.DayID = d.DayID
JOIN 
course c ON cs.CourseID = c.CourseID 
JOIN 
periodd p ON ts.PeriodID = p.PeriodID
JOIN 
room r ON cs.RoomID = r.RoomID
JOIN 
building b ON r.BuildingID = b.BuildingID
LEFT JOIN
semester s ON cs.SemesterID = s.SemesterID
WHERE 
cs.FacultyID = '$UID'
GROUP BY cs.TimeSlotID, cs.SemesterID, s.SemesterName, cs.CRN, cs.CourseID, cs.AvailableSeats, ts.TimeSlotID, c.CourseName, r.RoomNum, b.BuildingName, p.StartTime, p.EndTime
ORDER BY ts.TimeSlotID";



$scheduleResult = mysqli_query($conn, $scheduleQuery);

$courses = [];
while ($row = mysqli_fetch_assoc($scheduleResult)) {
    $row['Semester'] = $row['SemesterName']; // Update the Semester field with SemesterName
    unset($row['SemesterID']); // Remove the unnecessary SemesterID field
    unset($row['SemesterName']); // Remove the unnecessary SemesterName field
    $row['Weekday'] = $row['Weekdays']; // Update the Weekday field with concatenated weekdays
    unset($row['Weekdays']); // Remove the unnecessary Weekdays field
    $courses[] = $row;
}

// Fetch the students advised by the faculty member
$advisingQuery = "SELECT adv.StudentID, usr.FirstName, usr.LastName, adv.DOA 
                  FROM advisor adv
                  JOIN user usr ON adv.StudentID = usr.UID
                  WHERE adv.FacultyID = '$UID'";
$advisingResult = mysqli_query($conn, $advisingQuery);

$advisedStudents = [];
while ($row = mysqli_fetch_assoc($advisingResult)) {
    $advisedStudents[] = $row;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Welcome to U.A. University</title>

   <style>
       /* Styles for the header */
       body {
           margin: 0;
           padding: 0;
           background-image: url('background.jpg');
           background-size: cover;
           background-position: center;
           font-family: Arial, sans-serif;
           color: #fff;
           height: 100vh;
       }

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

       /* Styles for the welcome message and text below it */
       .welcome-message, .schedule-section {
           background-color: rgba(255, 255, 255, 0.7);
           padding: 10px;
           margin: 10px;
           border-radius: 5px;
       }

       /* Adjust the text color for better contrast */
       .welcome-message h2, .welcome-message h3, .schedule-section h2 {
           text-align: center;
           margin: 20px 0;
           color: #000;
       }

       /* Styles for the course tables */
       .table-container {
           display: flex;
           flex-wrap: wrap;
           justify-content: center;
       }

       /* Styles for the course tables */
       .course-table {
           margin: 10px;
           border: 1px solid #ccc;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
           flex: 1;
           background-color: white;
       }

       .course-table th, .course-table td {
           border: 1px solid #ccc;
           padding: 5px;
           text-align: left;
           color: black;
       }

       .course-table table {
           width: 100%;
           border-collapse: collapse;
       }

       /* Style for Class Roster buttons */
       .course-table a {
           background-image: linear-gradient(to right, #90ee90, #ffffff);
           font-weight: bold;
           padding: 10px 20px;
           color: black;
           text-decoration: none;
           border-radius: 5px;
           display: inline-block;
           margin: 5px 0;
           border: 1px solid #90ee90;
       }

       .course-table a:hover {
           background-image: linear-gradient(to right, #76c476, #ffffff);
       }

       /* Styles for the semester filter */
       .semester-filter {
           text-align: center;
           margin: 20px;
       }

       .semester-filter label {
           font-weight: bold;
           margin-right: 10px;
       }

       .semester-filter select {
           padding: 5px;
       }

       .semester-filter button {
           padding: 5px 10px;
           background-color: #007bff;
           color: white;
           border: none;
           cursor: pointer;
       }
       /* Style for the button container */
/* Styles for the button container */
.button-container {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Styles for the buttons */
.btn {
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    background-color: #000; /* Black background */
    color: #fff; /* White text color */
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.btn:hover {
    background-color: #00cc00; /* Green background on hover */
    color: #000; /* Black text color on hover */
}

/* Styles for the advised students table */
.advised-students-table {
    margin: 10px;
    border: 1px solid #ccc;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: white;
}

.advised-students-table th, .advised-students-table td {
    border: 1px solid #ccc;
    padding: 5px;
    text-align: left;
    color: black;
}

.advised-students-table table {
    width: 100%;
    border-collapse: collapse;
}

   </style>
</head>
<body>
<header class="header">
   <img src="ua.png" alt="U.A. Logo" class="logo">
   <h1>Welcome to U.A. University</h1>
   <div class="button-container">
      <a href="logout1.php" class="btn">Logout</a>
	  <a href="faculty_personalinfo.php" class="btn">Personal Information</a>
      <a href="student_majors1.php" class="btn">Majors</a>
      <a href="student_minor1.php" class="btn">Minors</a>
      <a href="facdepartment.php" class="btn">Departments</a>
      <a href="faculty_page1.php" class="btn">Master Schedule</a>
	  <a href="faculty_course_catalog1.php" class="btn">Course Catalog</a>
	  <a href="academiccal.html" class="btn">Academic Calendar</a>
   </div>
</header>

   <?php
  

   // Retrieve unique SemesterIDs for filtering
   $uniqueSemesters = array_unique(array_column($courses, 'Semester'));
   ?>

   <!-- Semester filter -->
   <div class="semester-filter">
       <label for="semesterSelect">Select Semester:</label>
       <select id="semesterSelect" name="semesterSelect">
           <option value="all">All</option>
           <?php
           // Populate the dropdown with unique semesters
           foreach ($uniqueSemesters as $semester) {
               echo '<option value="' . htmlspecialchars($semester) . '">' . htmlspecialchars($semester) . '</option>';
           }
           ?>
       </select>
       <button id="filterButton">Filter</button>
   </div>

   <!-- Welcome message -->
   <div class="welcome-message">
       <h2>Welcome, <?php echo htmlspecialchars($facultyName); ?> (User ID: <?php echo htmlspecialchars($UID); ?>)</h2>
       <h3>You are a <?php echo htmlspecialchars($facultyPosition); ?>. Here is your schedule/classes:</h3>
   </div>

   <div class="schedule-section">
       <h2>Schedule</h2>

       <!-- Container for course tables -->
       <div class="table-container">
           <?php foreach ($courses as $course): ?>
               <div class="course-table" data-semester-name="<?php echo htmlspecialchars($course['Semester']); ?>">
                   <table>
                       <tr><th>CRN</th><td><?php echo htmlspecialchars($course['CRN']); ?></td></tr>
                       <tr><th>Course ID</th><td><?php echo htmlspecialchars($course['CourseID']); ?></td></tr> <!-- Added line for CourseID -->
                       <tr><th>Course Name</th><td><?php echo htmlspecialchars($course['CourseName']); ?></td></tr>
                       <tr><th>Day</th><td><?php echo htmlspecialchars($course['Weekday']); ?></td></tr>
                       <tr><th>Building</th><td><?php echo htmlspecialchars($course['BuildingName']); ?></td></tr>
                       <tr><th>Room</th><td><?php echo htmlspecialchars($course['RoomNum']); ?></td></tr>
                       <tr><th>Time</th><td>
                            <?php 
                                // Convert military time to AM/PM format
                                $startTime = date("g:i A", strtotime($course['StartTime']));
                                $endTime = date("g:i A", strtotime($course['EndTime']));
                                echo htmlspecialchars($startTime) . " - " . htmlspecialchars($endTime);
                            ?>
                        </td></tr>
                       <tr><th>Semester Name</th><td><?php echo htmlspecialchars($course['Semester']); ?></td></tr>
                       <tr>
                           <td colspan="2"><a href="class_roster.php?CRN=<?php echo htmlspecialchars($course['CRN']); ?>&semesterID=<?php echo htmlspecialchars($course['Semester']); ?>">Class Roster</a></td>
                       </tr>
                   </table>
               </div>
           <?php endforeach; ?>
       </div>
   </div>

   <script>
       document.addEventListener('DOMContentLoaded', function () {
           const semesterSelect = document.getElementById('semesterSelect');
           const filterButton = document.getElementById('filterButton');
           const courseTables = document.querySelectorAll('.course-table');

           filterButton.addEventListener('click', function () {
               const selectedSemester = semesterSelect.value;

               courseTables.forEach(function (table) {
                   const semesterName = table.getAttribute('data-semester-name');
                   if (semesterName === selectedSemester || selectedSemester === 'all') {
                       table.style.display = 'block';
                   } else {
                       table.style.display = 'none';
                   }
               });
           });
       });
   </script>


<!-- Advised Students Section -->
<div class="advised-students-section">
    <h2>Advised Students</h2>
    <div class="table-container">
        <table class="advised-students-table">
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Date of Assignment</th>
            </tr>
            <?php foreach ($advisedStudents as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['StudentID']); ?></td>
                    <!-- Make student name a clickable link -->
                    <td>
                        <a href="facacademicprof.php?UID=<?php echo urlencode($student['StudentID']); ?>">
                            <?php echo htmlspecialchars($student['FirstName']) . ' ' . htmlspecialchars($student['LastName']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($student['DOA']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

</body>
</html>
