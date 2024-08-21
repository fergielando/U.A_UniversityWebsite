<?php
@include 'config1.php'; // Include your database configuration file

session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to assign courses.";
    exit;
}

$uid = $_SESSION['UID'];

$availableSlotsQuery = "
SELECT 
    t.TimeSlotID, 
    d.Weekday, 
    p.StartTime, 
    p.EndTime, 
    b.BuildingName, 
    r.RoomNum, 
    r.RoomID
FROM timeslot t
JOIN day d ON t.DayID = d.DayID
JOIN periodd p ON t.PeriodID = p.PeriodID
JOIN room r ON 1 = 1
JOIN building b ON r.BuildingID = b.BuildingID
LEFT JOIN coursesection cs ON t.TimeSlotID = cs.TimeSlotID AND r.RoomID = cs.RoomID
WHERE cs.CRN IS NULL
ORDER BY d.Weekday, p.StartTime, r.RoomNum;";




$availableSlotsResult = mysqli_query($conn, $availableSlotsQuery);

$availableSlots = [];
while ($slotRow = mysqli_fetch_assoc($availableSlotsResult)) {
    $availableSlots[] = $slotRow;
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <style>
      body {
  font-family: Arial, sans-serif;
  background-color: #f4f4f4;
  margin: 0;
  padding: 20px;
}

h1 {
  color: #333;
}

form {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  margin: 20px auto;
}

label {
  display: block;
  margin-top: 10px;
  color: #666;
}

input[type="text"],
textarea,
select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box; /* Makes sure padding doesn't affect overall width */
  display: block;
}

textarea {
  height: 100px;
}

input[type="submit"] {
  background-color: #5cb85c;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 20px;
}

input[type="submit"]:hover {
  background-color: #4cae4c;
}

    </style>
</head>
<body>
    <h1>Create Course</h1>
    <form action="process_create_course.php" method="POST">
        <!-- Input field for course ID -->
        <label for="course_id">Course ID:</label>
        <input type="text" id="course_id" name="course_id" required><br>

        <!-- Input field for course name -->
        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" required><br>

        <!-- Dropdown list for department ID -->
        <label for="dept_id">Department ID:</label>
        <select id="dept_id" name="dept_id" required>
            <?php
            // Include your database configuration file
            @include 'config1.php';

            // Fetch existing department IDs from the database
            $deptQuery = "SELECT DeptID FROM dept";
            $deptResult = mysqli_query($conn, $deptQuery);

            if ($deptResult && mysqli_num_rows($deptResult) > 0) {
                while ($deptRow = mysqli_fetch_assoc($deptResult)) {
                    $deptID = $deptRow['DeptID'];
                    echo "<option value='$deptID'>$deptID</option>";
                }
            }
            ?>
        </select><br>


       
<select id="timeslot" name="timeslot" required>
    <?php foreach ($availableSlots as $slot): ?>
        <option value="<?php echo $slot['TimeSlotID'] . '_' . $slot['RoomID']; ?>">
    <?php echo $slot['Weekday'] . ', ' . $slot['StartTime'] . '-' . $slot['EndTime'] . ', ' . $slot['BuildingName'] . ' - Room ' . $slot['RoomNum']; ?>
</option>
    <?php endforeach; ?>
</select>

<!-- Input field for CRN -->
<label for="CRN">CRN:</label>
        <input type="text" id="CRN" name="CRN" required><br>


       <!-- Hidden or Read-only field for SemesterID -->
       <label for="semester_id">Semester ID:</label>
        <input type="text" id="semester_id" name="semester_id" value="20241" readonly><br>

        <!-- Input field for Available Seats -->
        <label for="available_seats">Available Seats:</label>
        <input type="number" id="available_seats" name="available_seats" required><br>

        <!-- Input field for course credits -->
        <label for="credits">Credits:</label>
        <input type="text" id="credits" name="credits" required><br>

        <!-- Input field for course description -->
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br>

        <!-- Input field for course type -->
        <label for="course_type">Course Type:</label>
        <input type="text" id="course_type" name="course_type" required><br>

        <!-- Input field for section number -->
        <label for="section_num">Section Number:</label>
        <input type="text" id="section_num" name="section_num" required><br>

        <!-- Input field for faculty ID -->
        <label for="faculty_id">Faculty ID:</label>
        <input type="text" id="faculty_id" name="faculty_id" required><br>

       <!-- Input fields for course prerequisites -->
<label for="pr_course_id">Prerequisite Course ID:</label>
<input type="text" id="pr_course_id" name="pr_course_id"><br>

<label for="min_grade">Minimum Grade:</label>
<input type="text" id="min_grade" name="min_grade"><br>

<label for="dolu">DOLU:</label>
<input type="text" id="dolu" name="dolu"><br>

        <!-- Submit button -->
        <input type="submit" value="Create Course">
    </form>
</body>
</html>
