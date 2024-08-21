<?php
@include 'config1.php';

session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_name'])) {
   header('location:login_form1.php');
}

// Get the ScheduleID from the URL parameter
$schedule_id = isset($_GET['ScheduleID']) ? $_GET['ScheduleID'] : '';

// Fetch the schedule data from the database for the given ScheduleID
if ($schedule_id != '') {
   $query = "SELECT * FROM masterschedule WHERE ScheduleID = ?";
   $stmt = $conn->prepare($query);
   $stmt->bind_param("i", $schedule_id);
   $stmt->execute();
   $result = $stmt->get_result();
   $schedule = $result->fetch_assoc();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Get the form data
   $course_name = $_POST['course_name'];
   $day = $_POST['day'];
   $time = $_POST['time'];
   $building = $_POST['building'];
   $room = $_POST['room'];

   // Update the schedule in the database
   $update_query = "UPDATE masterschedule SET CourseName = ?, Day = ?, Time = ?, Bldg = ?, Room = ? WHERE ScheduleID = ?";
   $update_stmt = $conn->prepare($update_query);
   $update_stmt->bind_param("sssssi", $course_name, $day, $time, $building, $room, $schedule_id);
   $update_stmt->execute();

   // Redirect to the master schedule page or display a success message
   header('location:admin_page1.php');
   // Alternatively, you can echo a success message
   // echo "Schedule updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <!-- ... [Your existing head elements] ... -->
</head>
<body>
   <div class="container">
      <h2>Edit Schedule</h2>
      <form action="edit_schedule1.php?ScheduleID=<?php echo $schedule_id; ?>" method="post">
         <label for="course_name">Course Name:</label>
         <input type="text" name="course_name" value="<?php echo $schedule['CourseName']; ?>" required>

         <label for="day">Day:</label>
         <input type="text" name="day" value="<?php echo $schedule['Day']; ?>" required>

         <label for="time">Time:</label>
         <input type="text" name="time" value="<?php echo $schedule['Time']; ?>" required>

         <label for="building">Building:</label>
         <input type="text" name="building" value="<?php echo $schedule['Bldg']; ?>" required>

         <label for="room">Room:</label>
         <input type="text" name="room" value="<?php echo $schedule['Room']; ?>" required>

         <input type="submit" value="Update Schedule">
      </form>
   </div>
</body>
</html>
