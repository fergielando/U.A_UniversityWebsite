<?php
// Start the session
// Include your database configuration file
@include 'config1.php';

session_start();

// Check if a UID is passed in the URL
if (!isset($_GET['UID'])) {
    echo "No student selected. Redirecting to previous page.";
    header("refresh:3;url=javascript:history.back()");
    exit;
}

// Use the UID from the URL parameter
$uid = mysqli_real_escape_string($conn, $_GET['UID']);

// Retrieve the student's information from the database
$queryUserInfo = "SELECT FirstName, LastName, UID, DOB FROM user WHERE UID = '$uid'";
$resultUserInfo = mysqli_query($conn, $queryUserInfo);

if ($resultUserInfo && mysqli_num_rows($resultUserInfo) > 0) {
    $user = mysqli_fetch_assoc($resultUserInfo);
} else {
    echo "User not found.";
    exit;
}

// Retrieve the student's majors with MajorID
$queryMajors = "SELECT studentmajor.MajorID, major.MajorName FROM studentmajor INNER JOIN major ON studentmajor.MajorID = major.MajorID WHERE StudentID = '$uid'";
$resultMajors = mysqli_query($conn, $queryMajors);
$majors = [];

while ($rowMajor = mysqli_fetch_assoc($resultMajors)) {
    $majors[] = $rowMajor; 
}

// Retrieve the student's minors with MinorID
$queryMinors = "SELECT studentminor.MinorID, minor.MinorName FROM studentminor INNER JOIN minor ON studentminor.MinorID = minor.MinorID WHERE StudentID = '$uid'";
$resultMinors = mysqli_query($conn, $queryMinors);
$minors = [];

while ($rowMinor = mysqli_fetch_assoc($resultMinors)) {
    $minors[] = $rowMinor;
}

// Fetch course history including grades
$courseHistoryQuery = "SELECT coursesection.CRN, coursesection.CourseID, course.CourseName, semester.SemesterName, studenthistory.Grade, course.DeptID, course.Credits
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Degree Audit</title>
</head>

<style>
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

      .welcome-statement {
         color: #333;
         font-size: 18px;
         padding: 40px;
         text-align: center;
         font-family: 'Poppins', cursive; 
         border: 2px solid #444; 
         box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); 
      }

      table {
         width: 100%; /* Full width */
         max-width: 100%; /* Ensures table is not wider than its container */
         border-collapse: collapse;
         table-layout: auto; /* New line: Ensures the table respects the width */
      }

      th, td {
         border: 1px solid #000;
         padding: 15px;
         text-align: left;
         font-size: 14px;
         word-wrap: break-word; /* New line: Allows words to break and wrap */
      }

      th {
         background-color: #f2f2f2; /* Gives a slight background color to the header */
      }

      /* Style for every other row */
      tr:nth-child(even) {
         background-color: #ccffcc; /* Light green background */
      }

      /* Hover effect for rows */
      tr:hover {
         background-color: #e6ffe6; /* Lighter green on hover */
      }

      td, th {
         padding: 10px;
         border: 1px solid #ccc;
         text-align: center;
      }
      .search-container {
         margin: 20px 0;
         text-align: center;
      }

      .search-container input[type="text"] {
         padding: 10px;
         border: 1px solid #ccc;
         border-radius: 5px;
         font-size: 16px;
      }

      .search-container button {
         padding: 10px 20px;
         background-color: #000;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         font-size: 16px;
      }

      .search-container button:hover {
         background-color: #333;
      }
      .filter-container {
         display: flex;
         justify-content: space-between;
         margin: 10px 0;
         padding: 10px;
         background-color: #f2f2f2;
      }

      .filter-container label {
         font-weight: bold;
      }

      .filter-container select {
         padding: 5px;
      }

      .back-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .back-button:hover {
            background-color: #45a049;
        }
   </style>

<body>
    <!-- Header with back button -->
    <div class="header">
        <a href="javascript:history.back()" class="back-button">Back</a>
        <h1>Unoffficial Transcript</h1>
        <!-- ... existing header content ... -->
    </div>

    <h1>Unofficial Transcript</h1>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['FirstName']) . ' ' . htmlspecialchars($user['LastName']); ?></p>
    <p><strong>UID:</strong> <?php echo htmlspecialchars($user['UID']); ?></p>
	<p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['DOB']); ?></p>
	<p><strong>Campus: Main UA University Campus</strong></p>

    <h2>Declared Majors</h2>
    <?php if (!empty($majors)): ?>
        <ul>
            <?php foreach ($majors as $major): ?>
                <li><?php echo htmlspecialchars($major['MajorName']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No majors declared.</p>
    <?php endif; ?>

    <h2>Declared Minors</h2>
    <?php if (!empty($minors)): ?>
        <ul>
            <?php foreach ($minors as $minor): ?>
                <li><?php echo htmlspecialchars($minor['MinorName']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No minors declared.</p>
    <?php endif; ?>

    <h2>Course History</h2>
    <table border="1">
        <thead>
            <tr>
					<th>Department</th>
                <th>CRN</th>
                <th>Course Name</th>
				   <th>Credits</th>
                <th>Grade</th>
				   <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courseHistory as $course): ?>
                <tr>
						<td><?php echo htmlspecialchars($course['DeptID']); ?></td>
                    <td><?php echo htmlspecialchars($course['CRN']); ?></td>
                    <td><?php echo htmlspecialchars($course['CourseName']); ?></td>
					   <td><?php echo htmlspecialchars($course['Credits']); ?></td>
                    <td><?php echo htmlspecialchars($course['Grade']); ?></td>
					   <td><?php echo htmlspecialchars($course['SemesterName']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

 <script>
   function goBack() {
            window.history.back();
        }
        </script>
</html>

