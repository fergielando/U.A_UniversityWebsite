<?php
// Include your database configuration file and session start if needed
@include 'config1.php';
session_start();

if (!isset($_SESSION['UID'])) {
    echo "Please log in. <a href='login_page1.php'>Login Here</a>";
    exit;
}

$uid = $_SESSION['UID'];

// Set default timezone (use before the others)
date_default_timezone_set('America/New_York');


if (isset($_POST['submit'])) {
    $selectedSemester = $_POST['semester']; // Get selected SemesterID
    // Modify the query with the selected SemesterID
$enrolledCoursesQuery = "SELECT
    coursesection.CRN,
    coursesection.CourseID,
    coursesection.AvailableSeats,
    timeslot.TimeSlotID,
    day.Weekday,
    course.CourseName,
    room.RoomNum,
    building.BuildingName,
    periodd.StartTime,
    periodd.EndTime,
    user.FirstName AS FacultyFirstName,
    user.LastName AS FacultyLastName,
    coursesection.SectionNum,
	coursesection.SemesterID
FROM enrollment
JOIN coursesection ON enrollment.CRN = coursesection.CRN
JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
JOIN day ON timeslot.DayID = day.DayID
JOIN course ON coursesection.CourseID = course.CourseID
JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
JOIN room ON coursesection.RoomID = room.RoomID
JOIN building ON room.BuildingID = building.BuildingID
JOIN user ON coursesection.FacultyID = user.UID
WHERE enrollment.StudentID = '$uid' AND coursesection.SemesterID = '$selectedSemester'";
}
else {
$enrolledCoursesQuery = "SELECT
    coursesection.CRN,
    coursesection.CourseID,
    coursesection.AvailableSeats,
    timeslot.TimeSlotID,
    day.Weekday,
    course.CourseName,
    room.RoomNum,
    building.BuildingName,
    periodd.StartTime,
    periodd.EndTime,
    user.FirstName AS FacultyFirstName,
    user.LastName AS FacultyLastName,
    coursesection.SectionNum,
	coursesection.SemesterID
FROM enrollment
JOIN coursesection ON enrollment.CRN = coursesection.CRN
JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
JOIN day ON timeslot.DayID = day.DayID
JOIN course ON coursesection.CourseID = course.CourseID
JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
JOIN room ON coursesection.RoomID = room.RoomID
JOIN building ON room.BuildingID = building.BuildingID
JOIN user ON coursesection.FacultyID = user.UID
WHERE enrollment.StudentID = '$uid' AND coursesection.SemesterID = '20232'";
}


$enrolledCoursesResult = mysqli_query($conn, $enrolledCoursesQuery);

// Check if the student has any enrolled courses
if (mysqli_num_rows($enrolledCoursesResult) > 0) {
    $enrolledCourses = [];
    while ($row = mysqli_fetch_assoc($enrolledCoursesResult)) {
        $enrolledCourses[] = $row;
    }
} else {
    $enrolledCourses = false; // No enrolled courses
}

// Fetching additional StudentYear information
$studentInfoQuery = "SELECT StudentYear FROM student WHERE StudentID = '$uid'";
$studentInfoResult = mysqli_query($conn, $studentInfoQuery);
$studentInfo = mysqli_fetch_assoc($studentInfoResult);
$studentYear = $studentInfo['StudentYear'];

// Fetch the time slots and days of the week from your database
$timeSlotsQuery = "SELECT DISTINCT StartTime, EndTime FROM periodd WHERE PeriodID <> 0 ORDER BY StartTime";
$timeSlotsResult = mysqli_query($conn, $timeSlotsQuery);

$daysOfWeekQuery = "SELECT DISTINCT Weekday FROM day WHERE DayID <> 0 ORDER BY FIELD(Weekday, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$daysOfWeekResult = mysqli_query($conn, $daysOfWeekQuery);

$timeSlots = [];
while ($row = mysqli_fetch_assoc($timeSlotsResult)) {
    $timeSlots[] = $row;
}

$daysOfWeek = [];
while ($row = mysqli_fetch_assoc($daysOfWeekResult)) {
    $daysOfWeek[] = $row['Weekday'];
}

// Query to fetch distinct SemesterIDs
$distinctSemestersQuery = "SELECT DISTINCT SemesterID, SemesterName FROM semester WHERE SemesterID <> 0";
$distinctSemestersResult = mysqli_query($conn, $distinctSemestersQuery);

$semesterOptions = [];
while ($row = mysqli_fetch_assoc($distinctSemestersResult)) {
    $semesterOptions[$row['SemesterID']] = $row['SemesterName'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add your head content here -->
    <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Page - Master Schedule</title>
   <link rel="stylesheet" href="css/fatman1.css">
   <style>
       body {
            margin: 0;
            padding: 0;
            /*background-image: url('background.jpg'); /* Replace 'background_image.jpg' with your image file */
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif; /* Add a generic font family */
            color: #fff; /* Set text color to white */
            height: 100vh; /* Set the body height to full viewport height */
        }

        .overlay {
            background: rgba(0, 0, 0, 0.5); /* Add a semi-transparent black overlay */
            height: 100%; /* Set the overlay height to full viewport height */
            overflow: auto; /* Allow scrolling if the content overflows */
        }

      .header {
         background: #000;
         color: #fff;
         padding: 20px;
         text-align: center;
         display: flex;
         justify-content: space-between;
      }

      .welcome-message {
    background: linear-gradient(to right, #e6f7ff, #ffffff);
    color: black; /* Black text */
    padding: 20px;
    text-align: center;
    border-radius: 10px; /* Optional: rounds the corners */
    margin: 20px auto; /* Centers the message and adds spacing */
    font-size: 24px; /* Increases the font size */
}


      .header h1 {
         font-size: 36px; 
      }

      .container {
         margin-top: 20px;
      }

      .buttons {
         display: flex;
         align-items: center;
      }

      .buttons a {
         margin-left: 20px;
         background: #000;
         color: #fff;
         padding: 10px 30px;
         text-decoration: none;
         border-radius: 5px;
      }

      .button-container .btn:hover {
         background: #333;
      }
      .header .logo {
         width: 50px; 
         height: 50px; 
      }

      .table-container {
    width: 90%; /* Adjust as needed */
    margin: auto; /* Centers the container */
    border-radius: 15px; /* Rounds the corners */
    overflow: hidden; /* Ensures the border radius clips the content */
}
      table {
    width: 90%; /* Adjust the width to fit your screen better */
    margin: 20px auto; /* Center the table with margin and add some space around it */
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 15px; /* Round corners for the outer border */
    overflow: hidden; /* Ensures the rounded corners are visible */
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
    color: black;
    text-align: center;
}

th {
    background-color: #e6f7ff; /* Use the blue color here for consistency */
    font-weight: bold;
}

/* Alternating row colors with gradient */
tr:nth-child(even) {
    background: linear-gradient(to right, #e6f7ff, #ffffff);
}

tr:nth-child(odd) {
    background-color: #ffffff;
}

/* Rounded corners for the first and last table header */
th:first-child {
    border-top-left-radius: 15px;
}

th:last-child {
    border-top-right-radius: 15px;
}

/* Button styles */
.buttons {
    display: flex;
    justify-content: center; /* Centers the buttons */
    padding: 10px 0; /* Adds padding above and below the buttons */
    margin-top: 20px; /* Adds space between the table and buttons */
}

.buttons a {
    background: linear-gradient(to right, #e6f7ff, #ffffff); /* Gradient to match the table rows */
    color: #000;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px; /* Rounded corners for buttons */
    margin-right: 10px; /* Adds space between buttons */
    font-weight: bold; /* Optional: makes the text a bit bolder */
    border: none; /* Removes border */
}

.buttons a:hover {
    background-color: #cceeff; /* A lighter blue for hover state */
    color: white;
}

@media screen and (max-width: 600px) {
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .buttons {
        justify-content: center; /* Center buttons on small screens */
    }
}


   </style>
<link rel="stylesheet" href="userPage.css">
</head>
<body>
   <div class = "overlay">
   <div class="header">
      <img src="ua.png" alt="U.A. Logo" class="logo">
      <h1>Welcome to U.A. University</h1>
      <div class="button-container">
         <a href="logout1.php" class="btn">Logout</a>
      </div>
   </div>
   
<?php
$semesterName = 'Fall 2023'; // Variable to hold the semester name

// Check if a semester filter is applied
if (isset($_POST['submit'])) {
    $selectedSemester = $_POST['semester'];

    // Fetch the semester name based on the selected SemesterID
    $semesterNameQuery = "SELECT SemesterName FROM semester WHERE SemesterID = '$selectedSemester'";
    $semesterNameResult = mysqli_query($conn, $semesterNameQuery);
    $semesterData = mysqli_fetch_assoc($semesterNameResult);

    // Assign the fetched semester name to the variable
    $semesterName = $semesterData['SemesterName'];
}
?>   

   <div class="welcome-message">
    <p>Welcome, <?php echo $_SESSION['user_name']; ?>. Welcome to UA University! You are currently in your <?php echo $studentYear; ?> year. Your Student ID is: <?php echo $_SESSION['UID']; ?>. Here is your current schedule for <?php echo $semesterName; ?>:</p>
	<p>
	    <?php
        // Get current date and time in desired formats
        $currentDate = date('Y-m-d');
        $currentTime = date('h:i A');
        echo "<p>Current Date: $currentDate</p>";
        echo "<p>Current Time: $currentTime</p>";
		?>
	</p>
</div>

<div class="semester-filter">
    <form method="POST" action="">
        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester">
            <?php foreach ($semesterOptions as $semesterID => $semesterName): ?>
                <option value="<?php echo $semesterID; ?>"><?php echo $semesterName; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="submit" value="Filter">
    </form>
</div>

<?php if ($enrolledCourses !== false): ?>
    <table>
        <thead>
            <tr>
                <th>Time / Day</th>
                <?php foreach ($daysOfWeek as $day): ?>
                    <th><?php echo $day; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timeSlots as $timeSlot): ?>
                <tr>
                    <td><?php
            // Convert StartTime and EndTime to AM/PM format
            $startTime = date("h:i A", strtotime($timeSlot['StartTime']));
            $endTime = date("h:i A", strtotime($timeSlot['EndTime']));
            echo $startTime . " - " . $endTime;
            ?></td>
                    <?php foreach ($daysOfWeek as $day): ?>
                        <td>
                            <?php
                            foreach ($enrolledCourses as $course) {
                                if ($course['Weekday'] == $day && $course['StartTime'] <= $timeSlot['StartTime'] && $course['EndTime'] >= $timeSlot['EndTime']) {
                                    echo $course['CourseName'] . "<br>";
                                    echo "Room: " . $course['RoomNum'] . "<br>";
                                    echo "Building: " . $course['BuildingName'] . "<br>";
                                    echo "Professor: " . $course['FacultyFirstName'] . " " . $course['FacultyLastName'] . "<br>"; // Display Faculty Name
                                    echo "Section: " . $course['SectionNum'] . "<br>"; // Display Section Number
                                }
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No courses registered for this semester.</p>
<?php endif; ?>
    <div class="buttons">
    <a href="student_academic_profile1.php" class="btn">Academic Profile</a>
    <a href="student_course_catalog1.php" class="btn">Course Catalog</a>
    <a href="student_majors1.php" class="btn">Majors</a>
    <a href="student_minor1.php" class="btn">Minors</a>
    <a href="student_departments1.php" class="btn">Departments</a>
    <a href="studentmasterschedule.php" class="btn">Master Schedule</a>
	<a href="academiccal.html" class="btn">Academic Calendar</a>
</div>

</body>
</html>





