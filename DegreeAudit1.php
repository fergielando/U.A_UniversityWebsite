<?php
// Start the session
// Include your database configuration file
@include 'config1.php';

session_start();

// Check if a UID is stored in the session and redirect if not found
if (!isset($_SESSION['UID'])) {
    echo "User not authenticated. Redirecting to login page.";
    // Redirect to login page or another appropriate page
    header("Location: login_form1.php");
    exit;
}

$uid = $_SESSION['UID'];

// Retrieve the student's information from the database
$queryUserInfo = "SELECT FirstName, LastName, UID FROM user WHERE UID = '$uid'";
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
$courseHistoryQuery = "SELECT coursesection.CRN, coursesection.CourseID, course.CourseName, studenthistory.Grade
FROM studenthistory
JOIN coursesection ON studenthistory.CRN = coursesection.CRN
JOIN course ON coursesection.CourseID = course.CourseID
WHERE studenthistory.StudentID = '$uid'";
$courseHistoryResult = mysqli_query($conn, $courseHistoryQuery);
$courseHistory = [];

while ($course = mysqli_fetch_assoc($courseHistoryResult)) {
    $courseHistory[] = $course;
}

function fetchPrerequisitesWithGradeCheck($conn, $prerequisiteQuery, $courseHistory) {
   $result = mysqli_query($conn, $prerequisiteQuery);
   $prerequisites = [];

   while ($row = mysqli_fetch_assoc($result)) {
    $row['AchievedGrade'] = false; // Default value
    foreach ($courseHistory as $course) {
        if (
            isset($row['PRmajorID']) &&
            $course['CourseID'] == $row['PRmajorID'] &&
            (
                $course['Grade'] == 'C' ||
                $course['Grade'] == 'C+' ||
                $course['Grade'] == 'B-' ||
                $course['Grade'] == 'B' ||
                $course['Grade'] == 'B+' ||
                $course['Grade'] == 'A-' ||
                $course['Grade'] == 'A' ||
                $course['Grade'] == 'A+'
            )
        ){
            $row['AchievedGrade'] = $course['Grade'];
            break;
        } 
		elseif(
            isset($row['PRminorID']) &&
            $course['CourseID'] == $row['PRminorID'] &&
            (
                $course['Grade'] == 'C' ||
                $course['Grade'] == 'C+' ||
                $course['Grade'] == 'B-' ||
                $course['Grade'] == 'B' ||
                $course['Grade'] == 'B+' ||
                $course['Grade'] == 'A-' ||
                $course['Grade'] == 'A' ||
                $course['Grade'] == 'A+'
            )
        ){
            $row['AchievedGrade'] = $course['Grade'];
            break;
        }
    }
    $prerequisites[] = $row;
}

   return $prerequisites;
}


// Array to hold all prerequisites for all majors
$allMajorPrerequisites = [];

// Fetch prerequisites for each major
foreach ($majors as $major) {
    $majorId = $major['MajorID']; 
    $prerequisitesQuery = "SELECT majorprerequisite.PRmajorID, majorprerequisite.MinGrade, majorprerequisite.DOLU, course.CourseName
                           FROM majorprerequisite 
                           JOIN course ON majorprerequisite.PRmajorID = course.CourseID
                           WHERE majorprerequisite.MajorID = '$majorId'";
    $prerequisites = fetchPrerequisitesWithGradeCheck($conn, $prerequisitesQuery, $courseHistory);
    $allMajorPrerequisites[$major['MajorName']] = $prerequisites;
 }
 

// Array to hold all prerequisites for all minors
$allMinorPrerequisites = [];

// Fetch prerequisites for each minor
foreach ($minors as $minor) {
   $minorId = $minor['MinorID']; 
   $prerequisitesQuery = "SELECT minorprerequisite.PRminorID, minorprerequisite.MinGrade, minorprerequisite.DOLU, course.CourseName
                          FROM minorprerequisite 
                          JOIN course ON minorprerequisite.PRminorID = course.CourseID
                          WHERE minorprerequisite.MinorID = '$minorId'";
   $prerequisites = fetchPrerequisitesWithGradeCheck($conn, $prerequisitesQuery, $courseHistory);
   $allMinorPrerequisites[$minor['MinorName']] = $prerequisites;
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
        <h1>Degree Audit</h1>
        <!-- ... existing header content ... -->
    </div>

    <h1>Degree Audit</h1>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['FirstName']) . ' ' . htmlspecialchars($user['LastName']); ?></p>
    <p><strong>UID:</strong> <?php echo htmlspecialchars($user['UID']); ?></p>

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
                <th>CRN</th>
                <th>Course Name</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courseHistory as $course): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['CRN']); ?></td>
                    <td><?php echo htmlspecialchars($course['CourseName']); ?></td>
                    <td><?php echo htmlspecialchars($course['Grade']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Major Prerequisites</h2>
    <?php foreach ($allMajorPrerequisites as $majorName => $prerequisites): ?>
        <h3><?php echo htmlspecialchars($majorName); ?></h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Minimum Grade</th>
                    <th>Date of Last Update</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prerequisites as $prerequisite): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prerequisite['PRmajorID']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['CourseName']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['MinGrade']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['DOLU']); ?></td>
                        <td><?php echo !empty($prerequisite['AchievedGrade']) ? $prerequisite['AchievedGrade'] : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <h2>Minor Prerequisites</h2>
    <?php foreach ($allMinorPrerequisites as $minorName => $prerequisites): ?>
        <h3><?php echo htmlspecialchars($minorName); ?></h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Minimum Grade</th>
                    <th>Date of Last Update</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prerequisites as $prerequisite): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prerequisite['PRminorID']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['CourseName']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['MinGrade']); ?></td>
                        <td><?php echo htmlspecialchars($prerequisite['DOLU']); ?></td>
                        <td><?php echo !empty($prerequisite['AchievedGrade']) ? $prerequisite['AchievedGrade'] : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

</body>

 <script>
   function goBack() {
            window.history.back();
        }
        </script>
</html>

