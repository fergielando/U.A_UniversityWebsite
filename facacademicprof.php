<?php
@include 'config1.php';
session_start();


$uid = '';
$user = [];
$userMajors = [];
$userMinors = [];

// Check if a UID is provided
if (isset($_GET['UID'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['UID']);

    // Fetch Student Data
    $query = "SELECT user.*,logintable.* FROM user 
	JOIN logintable ON user.UID = logintable.UID
	WHERE user.UID = '$uid'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

    }
   }

   // Re-fetch user data
$query = "SELECT user.*,logintable.* FROM user 
	JOIN logintable ON user.UID = logintable.UID
	WHERE user.UID = '$uid'";
    $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
}


// Retrieve majors, minors, and enrolled courses
$queryMajors = "SELECT * FROM major";
$resultMajors = mysqli_query($conn, $queryMajors);
$majors = [];

while ($rowMajor = mysqli_fetch_assoc($resultMajors)) {
    $majors[] = $rowMajor;
}

$queryMinors = "SELECT * FROM minor";
$resultMinors = mysqli_query($conn, $queryMinors);
$minors = [];

while ($rowMinor = mysqli_fetch_assoc($resultMinors)) {
    $minors[] = $rowMinor;
}

$queryUserMajors = "SELECT MajorName FROM studentmajor INNER JOIN major ON studentmajor.MajorID = major.MajorID WHERE StudentID = '$uid'";
$resultUserMajors = mysqli_query($conn, $queryUserMajors);
$userMajors = [];

while ($rowUserMajor = mysqli_fetch_assoc($resultUserMajors)) {
    $userMajors[] = $rowUserMajor['MajorName'];
}

$queryUserMinors = "SELECT MinorName FROM studentminor INNER JOIN minor ON studentminor.MinorID = minor.MinorID WHERE StudentID = '$uid'";
$resultUserMinors = mysqli_query($conn, $queryUserMinors);
$userMinors = [];

while ($rowUserMinor = mysqli_fetch_assoc($resultUserMinors)) {
    $userMinors[] = $rowUserMinor['MinorName'];
}


// Function to assign a major to a student
if (isset($_POST['assign_major'])) {
    $selectedMajorID = mysqli_real_escape_string($conn, $_POST['major']);

    // Check how many majors the student has declared
    $countUserMajorsQuery = "SELECT COUNT(*) as totalMajors FROM studentmajor WHERE StudentID = '$uid'";
    $countUserMajorsResult = mysqli_query($conn, $countUserMajorsQuery);
    $countUserMajors = mysqli_fetch_assoc($countUserMajorsResult);

    if ($countUserMajors['totalMajors'] < 2) {
        // If the student has declared less than two majors, proceed to assign the major
        // Check if the assignment already exists
        $checkAssignmentQuery = "SELECT * FROM studentmajor WHERE StudentID = '$uid' AND MajorID = '$selectedMajorID'";
        $checkAssignmentResult = mysqli_query($conn, $checkAssignmentQuery);

        if (mysqli_num_rows($checkAssignmentResult) > 0) {
            // If the assignment exists, update it in the database
            $assignMajorQuery = "UPDATE studentmajor SET MajorID = '$selectedMajorID' WHERE StudentID = '$uid'";
            mysqli_query($conn, $assignMajorQuery);
        } else {
            // If the assignment doesn't exist, insert it into the database
            $assignMajorQuery = "INSERT INTO studentmajor (StudentID, MajorID) VALUES ('$uid', '$selectedMajorID')";
            mysqli_query($conn, $assignMajorQuery);
        }
        
    } else {
        // The student has already declared two majors, show an error message or handle it as needed
        echo "You can only declare up to two majors.";
    }
}

if (isset($_POST['assign_minor'])) {
    // Check how many majors the student has declared
    $countUserMajorsQuery = "SELECT COUNT(*) as totalMajors FROM studentmajor WHERE StudentID = '$uid'";
    $countUserMajorsResult = mysqli_query($conn, $countUserMajorsQuery);
    $countUserMajors = mysqli_fetch_assoc($countUserMajorsResult);

    if ($countUserMajors['totalMajors'] >= 2) {
        // The student has already declared two majors, show an error message or handle it as needed
        echo "You can only declare up to two majors. Minor assignment failed.";
    } else {
        // Continue with the minor assignment process
        $selectedMinorID = mysqli_real_escape_string($conn, $_POST['minor']);
        
        // Check if the assignment already exists
        $checkAssignmentQuery = "SELECT * FROM studentminor WHERE StudentID = '$uid' AND MinorID = '$selectedMinorID'";
        $checkAssignmentResult = mysqli_query($conn, $checkAssignmentQuery);
        
        if (mysqli_num_rows($checkAssignmentResult) > 0) {
            echo "You are already assigned to this minor.";
        } else {
            // If the assignment doesn't exist, insert it into the database
            $assignMinorQuery = "INSERT INTO studentminor (StudentID, MinorID) VALUES ('$uid', '$selectedMinorID')";
            mysqli_query($conn, $assignMinorQuery);
            
          
        }
    }
}



// Function to drop a major for a student
if (isset($_POST['drop_major_submit'])) {
    $selectedMajorToDrop = mysqli_real_escape_string($conn, $_POST['drop_major']);

    // Check if the selected major to drop exists in the user's declared majors
    if (in_array($selectedMajorToDrop, $userMajors)) {
        // Remove the selected major from the user's declared majors
        $key = array_search($selectedMajorToDrop, $userMajors);
        if ($key !== false) {
            unset($userMajors[$key]);
        }

        // Update the user's declared majors in the database
        $queryDeleteMajor = "DELETE FROM studentmajor WHERE StudentID = '$uid' AND MajorID = (SELECT MajorID FROM major WHERE MajorName = '$selectedMajorToDrop')";
        mysqli_query($conn, $queryDeleteMajor);

        // Optionally, you can add a success message or redirect the user
        echo "Major dropped successfully!";
    } else {
        // If the selected major doesn't exist in the user's declared majors, show an error message or handle it as needed
        echo "Selected major is not in your declared majors.";
    }
}

// Function to drop a minor for a student
if (isset($_POST['drop_minor_submit'])) {
    $selectedMinorToDrop = mysqli_real_escape_string($conn, $_POST['drop_minor']);

    // Check if the selected minor to drop exists in the user's declared minors
    if (in_array($selectedMinorToDrop, $userMinors)) {
        // Remove the selected minor from the user's declared minors
        $key = array_search($selectedMinorToDrop, $userMinors);
        if ($key !== false) {
            unset($userMinors[$key]);
        }

        // Update the user's declared minors in the database
        $queryDeleteMinor = "DELETE FROM studentminor WHERE StudentID = '$uid' AND MinorID = (SELECT MinorID FROM minor WHERE MinorName = '$selectedMinorToDrop')";
        mysqli_query($conn, $queryDeleteMinor);

        // Optionally, you can add a success message or redirect the user
        echo "Minor dropped successfully!";
    } else {
        // If the selected minor doesn't exist in the user's declared minors, show an error message or handle it as needed
        echo "Selected minor is not in your declared minors.";
    }
}



// Function to update user information
if (isset($_POST['update_user'])) {
    $newStreet = mysqli_real_escape_string($conn, $_POST['new_street']);
    $newCity = mysqli_real_escape_string($conn, $_POST['new_city']);
    $newState = mysqli_real_escape_string($conn, $_POST['new_state']);
    $newPassword = mysqli_real_escape_string($conn, $_POST['new_password']);

    // Check if the new password is not empty before updating
    $passwordUpdate = !empty($newPassword) ? ", Password = '$newPassword'" : "";

    // Update user information in the database
    $updateQuery = "UPDATE user SET Street = '$newStreet', City = '$newCity', State = '$newState'$passwordUpdate WHERE UID = '$uid'";
    mysqli_query($conn, $updateQuery);

    // Refresh the page to reflect the changes
    header("Location: view_academic_profile1.php?UID=someUserId");;
    exit;
}
$advisorQuery = "SELECT u.FirstName, u.LastName, l.Email 
                 FROM advisor a 
                 JOIN user u ON a.FacultyID = u.UID 
                 JOIN logintable l ON u.UID = l.UID 
                 WHERE a.StudentID = '$uid'";
$advisorResult = mysqli_query($conn, $advisorQuery);

// Fetch course history including grades
$courseHistoryQuery = "SELECT coursesection.CRN, coursesection.CourseID, course.CourseName, semester.SemesterName, studenthistory.Grade
FROM studenthistory
JOIN coursesection ON studenthistory.CRN = coursesection.CRN
JOIN course ON coursesection.CourseID = course.CourseID
JOIN semester ON coursesection.SemesterID = semester.SemesterID
WHERE studenthistory.StudentID = '$uid'";
$courseHistoryResult = mysqli_query($conn, $courseHistoryQuery);
$courseHistory = [];

while ($course = mysqli_fetch_assoc($courseHistoryResult)) {
    $courseHistory[] = $course;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Academic Profile</title>


   <style>

.button-container {
   background-color: #000; /* Black background for the container */
   padding: 10px;
   text-align: center;
}

.button-container .btn {
   background-color: transparent; /* Transparent background for buttons */
   color: #fff; /* White text */
   padding: 10px 20px;
   margin: 5px;
   border: 2px solid #fff; /* White border */
   border-radius: 5px;
   text-decoration: none; /* Remove underline from links */
   font-size: 16px;
   transition: background-color 0.3s, color 0.3s; /* Smooth transition for hover effect */
}

.button-container .btn:hover {
   background-color: #90ee90; /* Light green background on hover */
   color: #000; /* Black text on hover */
}

    /* General style resets */
html, body {
    margin: 0;
    padding: 0;
    box-sizing: border-box; /* Include padding and border in the width */
    font-family: Arial, sans-serif; /* Standard font for better readability */
}

/* Header styles */
.header {
    background: #000;
    color: #fff;
    padding: 20px;
    text-align: center;
    display: flex;
    justify-content: space-between;
}

.header h1 {
    font-size: 36px;
    margin: 0;
}

/* Back button styles */
.back-button {
    background: #000;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
    transition: background-color 0.3s; /* Smooth transition for background color change */
}

.back-button:hover {
    background-color: #4caf50; /* Green color when hovered */
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table, th, td {
    border: 1px solid #000;
    padding: 12px;
    text-align: left;
}

/* Alternating row colors */
table tr:nth-child(even) {
    background-color: #fff; /* White for odd rows */
}

table tr:nth-child(odd) {
    background-color: #e6f7ff; /* Light blue for even rows */
}

/* Create button styles */
.create-button {
    background: #000;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
    display: inline-block;
    cursor: pointer;
}

/* Hold section */
.student-holds-container {
    position: absolute;
    top: 140px;
    right: 70px;
    width: 300px; /* Adjust width as needed */
    background-color: #fff;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add shadow for box effect */
}

/* Assignment section styles */
.assignment-section {
    border: 1px solid #ccc; /* Border color */
    padding: 20px;
    margin-bottom: 20px; /* Space between sections */
    background-color: #f9f9f9; /* Light background for the box */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: shadow for box effect */
}
.advisor-container {
    position: fixed;
    bottom: 10px;
    right: 10px;
    background-color: #fff;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 300px; /* Adjust the width as needed */
}

.advisor-container table {
    width: 100%;
    border-collapse: collapse;
}

.advisor-container th, .advisor-container td {
    border: 1px solid #000;
    padding: 8px;
    text-align: left;
}


    </style>
</head>
<body>

<div class="header">
   <h1>Academic Profile</h1>
   <div class="button-container">
		<a href="facunofficialtranscript.php?UID=<?php echo $uid; ?>" class="btn">Unofficial Transcript</a>
       <a href="Facdegreeaudit.php?UID=<?php echo $uid; ?>" class="btn">Degree Audit</a>
       <a href="fac_newhome1.php" class="btn">Back</a>
       </div>
</div>

<div class="academic-profile-container">
   <h2>User Information</h2>
   <img src="profpic.jpg" alt="User Image" width="200">
      <p><strong>User ID:</strong> <?php echo $user['UID']; ?></p>
      <p><strong>Name:</strong> <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></p>
	  <p><strong>Email:</strong> <?php echo $user['Email']?></p>
	  <p><strong>Gender:</strong> <?php echo $user['Gender']; ?></p>
	  <p><strong>Date of Birth:</strong> <?php echo $user['DOB']; ?></p>
	  <p><strong>Street:</strong> <?php echo $user['Street']; ?></p>
	  <p><strong>City:</strong> <?php echo $user['City']; ?></p>
	  <p><strong>State:</strong> <?php echo $user['State']; ?></p>
	  <p><strong>Zip Code:</strong> <?php echo $user['ZipCode']; ?></p>
</div>


      <h2>Current Schedule</h2>
<table>
   <thead>
      <tr>
         <th>CRN</th>
         <th>Course Name</th>
         <th>Days</th>
         <th>Start Time</th>
         <th>End Time</th>
         <th>Faculty</th>
         <th>Section</th>
         <th>Room</th>
         <th>Building</th>
		 <th>Semester</th>
      </tr>
   </thead>
   <tbody>
      <?php
      $enrolledCoursesQuery = "SELECT 
      coursesection.CRN, 
	  GROUP_CONCAT(DISTINCT day.Weekday ORDER BY day.Weekday SEPARATOR '/') AS Weekdays,
      coursesection.CourseID, 
      coursesection.SectionNum, 
      timeslot.TimeSlotID, 
      day.Weekday, 
      course.CourseName, 
      room.RoomNum, 
      building.BuildingName, 
      periodd.StartTime, 
      periodd.EndTime,
	  semester.SemesterName,
      CONCAT(user.FirstName, ' ', user.LastName) AS FacultyName
  FROM enrollment
  JOIN coursesection ON enrollment.CRN = coursesection.CRN
  JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
  JOIN day ON timeslot.DayID = day.DayID
  JOIN course ON coursesection.CourseID = course.CourseID
  JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
  JOIN room ON coursesection.RoomID = room.RoomID
  JOIN building ON room.BuildingID = building.BuildingID
  JOIN user ON coursesection.FacultyID = user.UID
  JOIN semester ON coursesection.SemesterID = semester.SemesterID
  WHERE enrollment.StudentID = '$uid'
  GROUP BY coursesection.CRN
ORDER BY coursesection.CRN ASC";
  
$enrolledCoursesResult = mysqli_query($conn, $enrolledCoursesQuery);
      
      $enrolledCoursesResult = mysqli_query($conn, $enrolledCoursesQuery);

      while ($enrolledCourse = mysqli_fetch_assoc($enrolledCoursesResult)) {
         echo "<tr>";
         echo "<td>{$enrolledCourse['CRN']}</td>";
         echo "<td>{$enrolledCourse['CourseName']}</td>";
         echo "<td>";
        
        // Adjusted this part to properly display weekdays
        $weekdays = explode('/', $enrolledCourse['Weekdays']);
        echo implode('/', array_unique($weekdays));
        
        echo "</td>";
         // Format StartTime to AM/PM
        echo "<td>" . date("h:i A", strtotime($enrolledCourse['StartTime'])) . "</td>";
        // Format EndTime to AM/PM
        echo "<td>" . date("h:i A", strtotime($enrolledCourse['EndTime'])) . "</td>";
         echo "<td>{$enrolledCourse['FacultyName']}</td>";
         echo "<td>{$enrolledCourse['SectionNum']}</td>";
         echo "<td>{$enrolledCourse['RoomNum']}</td>";
         echo "<td>{$enrolledCourse['BuildingName']}</td>";
         echo "<td>{$enrolledCourse['SemesterName']}</td>";
         echo "</tr>";
      }
      ?>
   </tbody>
</table>

	  <div class="grades-container">
    <h2>Current Grades</h2>
    <table>
        <thead>
            <tr>
                <th>CRN</th>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Grade</th>
				   <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courseHistory as $course) : ?>
                <tr>
                    <td><?php echo $course['CRN']; ?></td>
                    <td><?php echo $course['CourseID']; ?></td>
                    <td><?php echo $course['CourseName']; ?></td>
                    <td><?php echo $course['Grade']; ?></td>
					   <td><?php echo $course['SemesterName']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Student Holds section -->
<div class="student-holds-container">
    <h2>Student Holds</h2>
    <table>
        <thead>
            <tr>
                <th>Hold ID</th>
                <th>Date Of Hold</th>
                <th>Hold Type</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display the student's holds
            $studentHoldsQuery = "SELECT HoldID, DateOfHold, HoldType FROM hold WHERE StudentID = '$uid'";
            $studentHoldsResult = mysqli_query($conn, $studentHoldsQuery);

            while ($hold = mysqli_fetch_assoc($studentHoldsResult)) {
                echo "<tr>";
                echo "<td>{$hold['HoldID']}</td>";
                echo "<td>{$hold['DateOfHold']}</td>";
                echo "<td>{$hold['HoldType']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>


      <h2>Current Major(s)</h2>
      <table>
         <thead>
            <tr>
               <th>Major Name</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($userMajors as $userMajor) : ?>
               <tr>
                  <td><?php echo $userMajor; ?></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>

      <h2>Current Minor(s)</h2>
      <table>
         <thead>
            <tr>
               <th>Minor Name</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($userMinors as $userMinor) : ?>
               <tr>
                  <td><?php echo $userMinor; ?></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>


<!-- Advisor section -->
<div class="advisor-container">
    <h2>Student Advisors</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($advisor = mysqli_fetch_assoc($advisorResult)) {
                echo "<tr>";
                echo "<td>{$advisor['FirstName']} {$advisor['LastName']}</td>";
                echo "<td>{$advisor['Email']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
