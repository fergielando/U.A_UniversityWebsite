<?php
// Include your database configuration file and session start if needed
@include 'config1.php';
session_start();
$currentDate = date('Y-m-d');

if (!isset($_SESSION['UID'])) {
    echo "Please log in. <a href='login_page1.php'>Login Here</a>";
    exit;
}

$uid = $_SESSION['UID'];

// Fetching additional StudentYear information
$clientInfoQuery = "SELECT ClientName FROM statsoffice WHERE StatsID = '$uid'";
$clientInfoResult = mysqli_query($conn, $clientInfoQuery);
$clientInfo = mysqli_fetch_assoc($clientInfoResult);
$clientname = $clientInfo['ClientName'];

$queryugcount = "SELECT COUNT(StudentID) FROM undergradstudent WHERE StudentID <> 0";
$undergradresult = mysqli_query($conn, $queryugcount);
$undergradInfo = mysqli_fetch_assoc($undergradresult);
$undergrad_count = $undergradInfo['COUNT(StudentID)'];

$querygrcount = "SELECT COUNT(StudentID) FROM gradstudent WHERE StudentID <> 0";
$gradresult = mysqli_query($conn, $querygrcount);
$gradInfo = mysqli_fetch_assoc($gradresult);
$grad_count = $gradInfo['COUNT(StudentID)'];

$undergradpercent = ($undergrad_count) / ($undergrad_count + $grad_count) * 100;

$gradpercent = ($grad_count) / ($undergrad_count + $grad_count) * 100;

$queryftcount = "SELECT SUM(count)
FROM (
    SELECT COUNT(StudentID) AS count
    FROM gradstudentft
    WHERE StudentID <> 0
    UNION ALL
    SELECT COUNT(StudentID) AS count
    FROM undergradstudentft
    WHERE StudentID <> 0
) AS x;";
$fulltimeresult = mysqli_query($conn, $queryftcount);
$fulltimeInfo = mysqli_fetch_assoc($fulltimeresult);
$fulltime_count = $fulltimeInfo['SUM(count)'];

$queryptcount = "SELECT SUM(count)
FROM (
    SELECT COUNT(StudentID) AS count
    FROM gradstudentpt
    WHERE StudentID <> 0
    UNION ALL
    SELECT COUNT(StudentID) AS count
    FROM undergradstudentpt
    WHERE StudentID <> 0
) AS x;";
$parttimeresult = mysqli_query($conn, $queryptcount);
$parttimeInfo = mysqli_fetch_assoc($parttimeresult);
$parttime_count = $parttimeInfo['SUM(count)'];

$fulltimepercent = ($fulltime_count) / ($fulltime_count + $parttime_count) * 100;

$parttimepercent = ($parttime_count) / ($fulltime_count + $parttime_count) * 100;

$querymasterscount = "SELECT COUNT(StudentID) FROM gradstudent WHERE (GradStudentType = 'Masters Full Time' OR GradStudentType = 'Masters Part Time')";
$mastersresult = mysqli_query($conn, $querymasterscount);
$mastersInfo = mysqli_fetch_assoc($mastersresult);
$masters_count = $mastersInfo['COUNT(StudentID)'];

$queryphdcount = "SELECT COUNT(StudentID) FROM gradstudent WHERE (GradStudentType = 'PHD Full Time' OR GradStudentType = 'PHD Part Time')";
$phdresult = mysqli_query($conn, $queryphdcount);
$phdInfo = mysqli_fetch_assoc($phdresult);
$phd_count = $phdInfo['COUNT(StudentID)'];

$masterspercent = ($masters_count) / ($masters_count + $phd_count) * 100;

$phdpercent = ($phd_count) / ($masters_count + $phd_count) * 100;

$querymajorscount = "SELECT COUNT(MajorID) FROM major WHERE MajorID <> 0";
$majorsresult = mysqli_query($conn, $querymajorscount);
$majorsInfo = mysqli_fetch_assoc($majorsresult);
$majors_count = $majorsInfo['COUNT(MajorID)'];

$queryminorscount = "SELECT COUNT(MinorID) FROM minor WHERE MinorID <> 0";
$minorsresult = mysqli_query($conn, $queryminorscount);
$minorsInfo = mysqli_fetch_assoc($minorsresult);
$minors_count = $minorsInfo['COUNT(MinorID)'];

$passedCoursesQuery = "SELECT DISTINCT sh.StudentID
                       FROM studenthistory sh
                       WHERE sh.Grade IN ('A', 'A-', 'B+', 'B', 'B-', 'C+', 'C')";

$resultPassedCourses = mysqli_query($conn, $passedCoursesQuery);

$querytotalcount = "SELECT COUNT(StudentID) FROM student WHERE StudentID <> 0";
$totalresult = mysqli_query($conn, $querytotalcount);
$totalInfo = mysqli_fetch_assoc($totalresult);
$total_count = $totalInfo['COUNT(StudentID)'];

if ($resultPassedCourses) {
    $totalStudents = mysqli_num_rows($resultPassedCourses);

    // Query to count students who have completed major prerequisites
    $majorPrerequisitesQuery = "SELECT DISTINCT sm.StudentID
                                FROM studentmajor sm
                                INNER JOIN majorprerequisite mp ON sm.MajorID = mp.MajorID
                                INNER JOIN studenthistory sh ON sm.StudentID = sh.StudentID
                                WHERE sh.CourseID = mp.PRmajorID";

    $resultMajorPrerequisites = mysqli_query($conn, $majorPrerequisitesQuery);

    // Query to count students who have completed minor prerequisites (if applicable)
    $minorPrerequisitesQuery = "SELECT DISTINCT sm.StudentID
                                FROM studentminor sm
                                INNER JOIN minorprerequisite mp ON sm.MinorID = mp.MinorID
                                INNER JOIN studenthistory sh ON sm.StudentID = sh.StudentID
                                WHERE sh.CourseID = mp.PRminorID";

    $resultMinorPrerequisites = mysqli_query($conn, $minorPrerequisitesQuery);

    if ($resultMajorPrerequisites && $resultMinorPrerequisites) {
        $studentsWithMajorPrerequisites = mysqli_num_rows($resultMajorPrerequisites);
        $studentsWithMinorPrerequisites = mysqli_num_rows($resultMinorPrerequisites);

        // Calculate graduation rate
        $studentsWithAllPrerequisites = min($studentsWithMajorPrerequisites, $studentsWithMinorPrerequisites);
        $graduationRate = ($studentsWithAllPrerequisites / $totalStudents) * 100;

    } else {
        echo "Failed to fetch prerequisite data.";
    }
} else {
    echo "Failed to fetch passed courses data.";
}

$queryMastersDeptCount = "SELECT d.DeptName, COUNT(gs.StudentID) AS MastersCount
                         FROM gradstudent gs
                         INNER JOIN dept d ON gs.DeptID = d.DeptID
                         WHERE (gs.GradStudentType = 'Masters Full Time' OR gs.GradStudentType = 'Masters Part Time')
                         GROUP BY d.DeptName";

$mastersDeptResult = mysqli_query($conn, $queryMastersDeptCount);

$queryPhDDeptCount = "SELECT d.DeptName, COUNT(gs.StudentID) AS PhDCount
                      FROM gradstudent gs
                      INNER JOIN dept d ON gs.DeptID = d.DeptID
                      WHERE (gs.GradStudentType = 'PHD Full Time' OR gs.GradStudentType = 'PHD Part Time')
                      GROUP BY d.DeptName";

$phDDeptResult = mysqli_query($conn, $queryPhDDeptCount);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   
   <!-- Add the styles for the table, search feature, and filters here or in a separate CSS file -->
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
	  
      .button-second .btn {
         display: grid;
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

      .button-second .btn:hover {
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
   </style>
</head>
<body>
   <div class="header">
      <h1>U.A. University Latest Statistics</h1>
       <a href="statsoffice_page1.php" class="back-button">Back to Home Page</a>
   </div>

   <div class="welcome-statement">
    <p>Welcome, <?php echo $_SESSION['statsoffice_name']; ?>. Welcome to UA University! As a Statistics Office representative, You represent <?php echo $clientname; ?>. These are the latest statistics as of <?php echo $currentDate; ?>:</p>
	<p>Number of Undergraduates: <?php echo $undergrad_count; ?></p>
	<p>Percentage of Undergraduates: <?php echo $undergradpercent; ?>%</p>
	<p>Number of Graduates: <?php echo $grad_count; ?></p>
	<p>Percentage of Graduates: <?php echo $gradpercent; ?>%</p>
	<p>Number of Full Time Students: <?php echo $fulltime_count; ?></p>
	<p>Percentage of Full Time Students: <?php echo $fulltimepercent; ?>%</p>
	<p>Number of Part Time Students: <?php echo $parttime_count; ?></p>
	<p>Percentage of Part Time Students: <?php echo $parttimepercent; ?>%</p>
	<p>Number of Masters Graduate Students: <?php echo $masters_count; ?></p>
	<p>Percent of Masters Graduate Students: <?php echo $masterspercent; ?>%</p>
	<p>Number of PHD Graduate Students: <?php echo $phd_count; ?></p>
	<p>Percent of PHD Graduate Students: <?php echo $phdpercent; ?>%</p>
	<p>Total Number of Students: <?php echo $total_count; ?></p>
	<p>Number of Available Majors: <?php echo $majors_count; ?></p>
	<p>Number of Available Minors: <?php echo $minors_count; ?></p>
	<p>Graduation Rate: <?php echo round($graduationRate, 2); ?>%</p>
   </div>
   
   <div>
    <h2>Masters Students per Department</h2>
    <table>
        <tr>
            <th>Department Name</th>
            <th>Masters Count</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($mastersDeptResult)) {
            echo "<tr>";
            echo "<td>" . $row['DeptName'] . "</td>";
            echo "<td>" . $row['MastersCount'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<div>
    <h2>PhD Students per Department</h2>
    <table>
        <tr>
            <th>Department Name</th>
            <th>PhD Count</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($phDDeptResult)) {
            echo "<tr>";
            echo "<td>" . $row['DeptName'] . "</td>";
            echo "<td>" . $row['PhDCount'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>
   
   

   <script>
   </script>
</body>
</html>
