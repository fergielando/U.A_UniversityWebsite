<?php
// Include database configuration file and start session
@include 'config1.php';
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['FacultyID'])) {
    echo "Please log in.";
    exit;
}

$facultyID = $_SESSION['FacultyID'];

// Fetch faculty details for the welcome message
$facultyQuery = "SELECT Name FROM faculty WHERE FacultyID = '$facultyID'";
$facultyResult = mysqli_query($conn, $facultyQuery);
$facultyName = '';
if ($facultyRow = mysqli_fetch_assoc($facultyResult)) {
    $facultyName = $facultyRow['Name'];
}

// Fetch the courses assigned to the faculty
$scheduleQuery = "SELECT coursesection.CRN, coursesection.CourseID, coursesection.AvailableSeats, timeslot.TimeSlotID, day.Weekday, course.CourseName, room.RoomNum, building.BuildingName, periodd.StartTime, periodd.EndTime
                  FROM facultyhistory
                  JOIN coursesection ON facultyhistory.CourseID = coursesection.CourseID
                  JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID 
                  JOIN day ON timeslot.DayID = day.DayID
                  JOIN course ON coursesection.CourseID = course.CourseID 
                  JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
                  JOIN room ON coursesection.RoomID = room.RoomID
                  JOIN building ON room.BuildingID = building.BuildingID
                  WHERE facultyhistory.FacultyID = '$facultyID'";
$scheduleResult = mysqli_query($conn, $scheduleQuery);

$courses = [];
while ($row = mysqli_fetch_assoc($scheduleResult)) {
    $courses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Add your CSS link here -->
   <link rel="stylesheet" href="path_to_your_stylesheet.css">
</head>
<body>
   <!-- Header section (copied from the previous page) -->
   <header class="header">
      <!-- Header content here (same as in the previous page) -->
   </header>

   <!-- Welcome message -->
   <div class="welcome-message">
      <h2>Welcome, <?php echo $facultyName; ?> (Faculty ID: <?php echo $facultyID; ?>)</h2>
   </div>

   <!-- Schedule label -->
   <h2>Schedule</h2>

   <!-- Schedule table -->
   <table>
      <thead>
         <tr>
            <th>CRN</th>
            <th>Course Name</th>
            <th>Day</th>
            <th>Building</th>
            <th>Room</th>
            <th>Time</th>
            <th>Action</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($courses as $course): ?>
            <tr>
               <td><?php echo $course['CRN']; ?></td>
               <td><?php echo $course['CourseName']; ?></td>
               <td><?php echo $course['Weekday']; ?></td>
               <td><?php echo $course['BuildingName']; ?></td>
               <td><?php echo $course['RoomNum']; ?></td>
               <td><?php echo $course['StartTime'] . " - " . $course['EndTime']; ?></td>
               <td><a href="class_roster.php?CRN=<?php echo $course['CRN']; ?>">Class Roster</a></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>

</body>
</html>
