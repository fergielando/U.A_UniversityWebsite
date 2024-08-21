<?php
session_start();
@include 'config1.php'; // Include your database configuration file

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to assign courses.";
    exit;
}

$uid = $_SESSION['UID'];

// Check if the student is a graduate or undergraduate
$studentTypeQuery = "SELECT StudentID FROM gradstudent WHERE StudentID = '$uid'";
$studentresult = mysqli_query($conn, $studentTypeQuery);

if (mysqli_num_rows($studentresult) > 0) {
    // Student is found in gradstudent table, therefore considered Graduate
    $courseType = 'Graduate';
} else {
    // Student not found in gradstudent table, therefore considered Undergraduate
    $courseType = 'Undergraduate';
}

// Fetch available courses with additional details, including prerequisites, ordered by CRN
$query = "SELECT
    coursesection.CRN,
	GROUP_CONCAT(DISTINCT day.Weekday ORDER BY day.Weekday SEPARATOR '/') AS Weekdays,
    MAX(coursesection.CourseID) AS CourseID,
    MAX(coursesection.AvailableSeats) AS AvailableSeats,
	MAX(coursesection.SectionNum) AS SectionNum,
    MAX(timeslot.TimeSlotID) AS TimeSlotID,
    MAX(day.Weekday) AS Weekday,
    MAX(course.CourseName) AS CourseName,
	MAX(course.DeptID) AS CourseDept,
    MAX(coursesection.RoomID) AS RoomNum,
    MAX(building.BuildingName) AS BuildingName,
    MAX(periodd.StartTime) AS StartTime,
    MAX(periodd.EndTime) AS EndTime,
    MAX(coursesection.SemesterID) AS SemesterID,
	MAX(semester.SemesterName) AS SemesterName,
    MAX(courseprerequisite.PRcourseID) AS PRcourseID,
    MAX(courseprerequisite.MinGrade) AS MinGrade,
    MAX(course.CourseType) AS CourseType,
    MAX(course.Credits) AS Credits,
	MAX(user.FirstName) AS FacultyFirstName,
	MAX(user.LastName) AS FacultyLastName,
    MAX(dept.DeptName) AS DeptName
FROM coursesection
JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
JOIN day ON timeslot.DayID = day.DayID
JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
JOIN room ON coursesection.RoomID = room.RoomID
JOIN building ON room.BuildingID = building.BuildingID
JOIN facultyhistory ON coursesection.CRN = facultyhistory.CRN
JOIN faculty ON facultyhistory.FacultyID = faculty.FacultyID
LEFT JOIN courseprerequisite ON coursesection.CourseID = courseprerequisite.CourseID
JOIN course ON coursesection.CourseID = course.CourseID
JOIN dept ON course.DeptID = dept.DeptID
JOIN user ON faculty.FacultyID = user.UID  -- Join using the foreign key constraint
JOIN semester ON coursesection.SemesterID = semester.SemesterID  -- Join using the foreign key constraint
WHERE course.CourseType = '$courseType' AND coursesection.SemesterID <> 0
GROUP BY coursesection.CRN
ORDER BY coursesection.CRN ASC";



$result = mysqli_query($conn, $query);

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

//Get Course Prerequisites for each Course
foreach ($courses as &$course) {
    $courseID = $course['CourseID'];
    $prerequisitesQuery = "SELECT PRCourseID FROM courseprerequisite WHERE CourseID = '$courseID'";
    $prerequisitesResult = mysqli_query($conn, $prerequisitesQuery);

    $prerequisites = [];
    while ($row = mysqli_fetch_assoc($prerequisitesResult)) {
        $prerequisites[] = $row['PRCourseID'];
    }

    $course['Prerequisites'] = $prerequisites;
}
unset($course); // Unset the reference after the loop

// Fetch distinct semester Names for the filter
$query = "SELECT DISTINCT SemesterName FROM semester WHERE semesterID <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$semesterNames = [];
while ($row = mysqli_fetch_assoc($result)) {
    $semesterNames[] = $row['SemesterName'];
}

// Fetch distinct semester IDs for the filter
$query = "SELECT DISTINCT semesterID FROM coursesection WHERE semesterID <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$semesterIDs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $semesterIDs[] = $row['semesterID'];
}

// Fetch distinct days for the filter
$query = "SELECT DISTINCT day.Weekday FROM day WHERE DayID <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$days = [];
while ($row = mysqli_fetch_assoc($result)) {
    $days[] = $row['Weekday'];
}

// Fetch distinct building names for the filter
$query = "SELECT DISTINCT building.BuildingName FROM building WHERE BuildingID <> 0 AND BuildingID <>4"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$buildingNames = [];
while ($row = mysqli_fetch_assoc($result)) {
    $buildingNames[] = $row['BuildingName'];
}


// Fetch distinct department IDs for the filter
$query = "SELECT DISTINCT dept.DeptName FROM dept WHERE DeptID <> 'NULL'";
$result = mysqli_query($conn, $query);

$departmentIDs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $departmentIDs[] = $row['DeptName'];
}

// Fetch distinct room IDs for the filter
$query = "SELECT DISTINCT room.RoomID FROM room WHERE RoomID <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$roomIDs = [];
while ($row = mysqli_fetch_assoc($result)) {
   $roomIDs[] = $row['RoomID'];
}

$query = "SELECT DISTINCT CONCAT(periodd.StartTime, ' to ', periodd.EndTime) AS Time FROM periodd WHERE PeriodID <> 0";
$result = mysqli_query($conn, $query);

$times = [];
while ($row = mysqli_fetch_assoc($result)) {
    $timeRange = explode(' to ', $row['Time']);
    $formattedStart = date("g:i A", strtotime($timeRange[0]));
    $formattedEnd = date("g:i A", strtotime($timeRange[1]));
    $formattedTime = $formattedStart . ' to ' . $formattedEnd;
    $times[] = $formattedTime;
}

// Fetch distinct semester names for the filter
$query = "SELECT DISTINCT semester.SemesterName FROM semester WHERE SemesterID <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$semesterNames = [];
while ($row = mysqli_fetch_assoc($result)) {
    $semesterNames[] = $row['SemesterName'];
}

// Fetch distinct Section Numbers for the filter
$query = "SELECT DISTINCT coursesection.SectionNum FROM coursesection WHERE SectionNum <> 0"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$sectionnums = [];
while ($row = mysqli_fetch_assoc($result)) {
   $sectionnums[] = $row['SectionNum'];
}

// Fetch distinct Available Seats for the filter
$query = "SELECT DISTINCT coursesection.AvailableSeats FROM coursesection"; // Adjust the table and column names as needed
$result = mysqli_query($conn, $query);

$availbleseats = [];
while ($row = mysqli_fetch_assoc($result)) {
   $availbleseats[] = $row['AvailableSeats'];
}

// Fetch TimeSlotIDs of courses the student is currently enrolled in
$currentEnrollmentsQuery = "SELECT TimeSlotID FROM coursesection JOIN enrollment ON coursesection.CRN = enrollment.CRN WHERE enrollment.StudentID = '$uid'";
$currentEnrollmentsResult = mysqli_query($conn, $currentEnrollmentsQuery);
$currentEnrollmentTimeSlots = [];
while ($enrollmentRow = mysqli_fetch_assoc($currentEnrollmentsResult)) {
    $currentEnrollmentTimeSlots[] = $enrollmentRow['TimeSlotID'];
}

// Handle course assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['courses']) && is_array($_POST['courses'])) {
    $selectedTimeSlots = [];
    $timeSlotConflict = false;

    foreach ($_POST['courses'] as $selectedCRN) {
        $selectedCRN = mysqli_real_escape_string($conn, $selectedCRN);
    
        // Retrieve course details for the selected course
        $courseDetailsQuery = "SELECT TimeSlotID FROM coursesection WHERE CRN = '$selectedCRN'";
        $courseDetailsResult = mysqli_query($conn, $courseDetailsQuery);
        
        if ($courseDetailsRow = mysqli_fetch_assoc($courseDetailsResult)) {
            // Check if the course has already been dropped in the same semester
            $checkDropQuery = "SELECT * FROM studenthistory WHERE StudentID = '$uid' AND CRN = '$selectedCRN' AND SemesterID = (SELECT SemesterID FROM coursesection WHERE CRN = '$selectedCRN') AND Grade = 'Dro'";
            $checkDropResult = mysqli_query($conn, $checkDropQuery);
    
            if (mysqli_num_rows($checkDropResult) > 0) {
                return "Course with CRN $selectedCRN has already been dropped in this semester.";
            }
			
		// Check if the student has passed the course they are trying to register for
		$passedCourseQuery = "SELECT * FROM studenthistory WHERE StudentID = '$uid' AND CourseID = (SELECT CourseID FROM coursesection WHERE CRN = '$selectedCRN') AND Grade IN ('C','C+','B-','B','B+','A-','A','A+')";
		$passedCourseResult = mysqli_query($conn, $passedCourseQuery);
		
		if (mysqli_num_rows($passedCourseResult) > 0) {
        // The student has already passed the course, prevent enrollment
        echo "<div style='font-size: 18px; color: red;'>Enrollment failed: You have already passed the course with CRN $selectedCRN. Please review your course selection.</div>";
        return "<div style='font-size: 18px; color: red;'>Enrollment failed: You have already passed the course with CRN $selectedCRN. Please review your course selection.</div>";
    }

           // Check for student hold
$checkHoldQuery = "SELECT * FROM hold WHERE StudentID = '$uid'";
$checkHoldResult = mysqli_query($conn, $checkHoldQuery);

if ($checkHoldResult && mysqli_num_rows($checkHoldResult) > 0) {
    // The student has a hold
    $holdInfo = mysqli_fetch_assoc($checkHoldResult);
    
    // Display a larger, red error message with a 8-second delay
    return "<div style='font-size: 24px; color: red;'>You have a hold on your account of type: " . $holdInfo['HoldType'] . " since " . $holdInfo['DateOfHold'] . "</div>";
}

if($courseType == 'Undergraduate'){
//Credits Check
$query = "SELECT U.StudentID, U.UnderGradStudentType, SUM(C.Credits) AS TotalCredits
FROM undergradstudent U
JOIN enrollment E ON U.StudentID = E.StudentID
JOIN coursesection CS ON E.CRN = CS.CRN
JOIN course C ON CS.CourseID = C.CourseID
WHERE CS.SemesterID = '20241' AND U.StudentID = '$uid'
GROUP BY U.StudentID, U.UnderGradStudentType";




$result = mysqli_query($conn, $query);


while ($row = mysqli_fetch_assoc($result)) {
    $studentID = $row['StudentID'];
    $studentType = $row['UnderGradStudentType'];
    $totalCredits = $row['TotalCredits'];

    $creditLimit = ($studentType === 'Undergrad Part Time') ? 6 : 12;

    if ($totalCredits > $creditLimit) {
		echo "Enrollment for Student ID $studentID failed: Exceeds credit limit. Maximum allowed credits: $creditLimit.";
        return "Enrollment for Student ID $studentID failed: Exceeds credit limit. Maximum allowed credits: $creditLimit.";

    }
}
}
elseif($courseType == 'Graduate'){
$query = "SELECT G.StudentID, G.GradStudentType, SUM(C.Credits) AS TotalCredits
FROM gradstudent G
JOIN enrollment E ON G.StudentID = E.StudentID
JOIN coursesection CS ON E.CRN = CS.CRN
JOIN course C ON CS.CourseID = C.CourseID
WHERE CS.SemesterID = '20241' AND G.StudentID = '$uid'
GROUP BY G.StudentID, G.GradStudentType";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $studentID = $row['StudentID'];
    $studentType = $row['GradStudentType'];
    $totalCredits = $row['TotalCredits'];

    $creditLimits = [
        'PHD Full Time' => 12,
		'PHD Part Time' => 6,
        'Masters Full Time' => 12,
        'Masters Part Time' => 6,
    ];

    if (isset($creditLimits[$studentType]) && $totalCredits > $creditLimits[$studentType]) {
		echo "Enrollment for Student ID $studentID failed: Exceeds credit limit. Maximum allowed credits for $studentType: " . $creditLimits[$studentType] . ".";
        return "Enrollment for Student ID $studentID failed: Exceeds credit limit. Maximum allowed credits for $studentType: " . $creditLimits[$studentType] . ".";
    }
}
}

// If the student doesn't have a hold or credits restriction, proceed with class assignment logic
// ...

        if ($courseDetailsRow = mysqli_fetch_assoc($courseDetailsResult)) {
            // Check if the course has already been dropped in the same semester
            $checkDropQuery = "SELECT * FROM studenthistory WHERE StudentID = '$uid' AND CRN = '$selectedCRN' AND SemesterID = '20232' AND Grade = 'Dro'";
            $checkDropResult = mysqli_query($conn, $checkDropQuery);
    
            if (mysqli_num_rows($checkDropResult) > 0) {
				echo "Course with CRN $selectedCRN has already been dropped in this semester.";
                return "Course with CRN $selectedCRN has already been dropped in this semester.";
            }
    
            // Retrieve prerequisite information for the selected course
            $prerequisiteQuery = "SELECT PRcourseID, MinGrade FROM courseprerequisite WHERE CourseID = (SELECT CourseID FROM coursesection WHERE CRN = '$selectedCRN')";
            $prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
    
            if ($prerequisiteRow = mysqli_fetch_assoc($prerequisiteResult)) {
                $prerequisiteCourseID = $prerequisiteRow['PRcourseID'];
                $minGrade = $prerequisiteRow['MinGrade'];
    
                // Check if the student has met the prerequisite requirement
                $hasPrerequisite = false;
    
                // Query to check if the student has the required grade in the prerequisite course
                $prerequisiteCheckQuery = "SELECT * FROM studenthistory WHERE StudentID = '$uid' AND CourseID = '$prerequisiteCourseID' AND Grade IN ('A+','A','A-', 'B+','B','B-', 'C+','C')";
                $prerequisiteCheckResult = mysqli_query($conn, $prerequisiteCheckQuery);
                
                if (mysqli_num_row($prerequisiteCheckResult) > 0) {
                    
                $hasPrerequisite = true;    
                    
                }
    
                if ($hasPrerequisite = false) {
                    // Retrieve the course name and CourseID for the prerequisite course
                    $prerequisiteCourseInfoQuery = "SELECT course.CourseName, coursesection.CourseID FROM coursesection
                    JOIN course ON coursesection.CourseID = course.CourseID
                    WHERE course.CourseID = '$prerequisiteCourseID'";
                    $prerequisiteCourseInfoResult = mysqli_query($conn, $prerequisiteCourseInfoQuery);
                    $prerequisiteCourseName = "";
                    $prerequisiteCRN = "";
                
                    if ($prerequisiteCourseInfoRow = mysqli_fetch_assoc($prerequisiteCourseInfoResult)) {
                        $prerequisiteCourseName = $prerequisiteCourseInfoRow['CourseName'];
                        $prerequisiteCRN = $prerequisiteCourseInfoRow['CourseID'];
                    }
					echo "<div id='prerequisiteError' style='font-size: 18px; color: red;'>Enrollment failed: You must obtain a $minGrade or better in the prerequisite course ($prerequisiteCourseName - CourseID: $prerequisiteCRN) to register for this class. Please review your course selection.</div>";
                    return "<div id='prerequisiteError' style='font-size: 18px; color: red;'>Enrollment failed: You must obtain a $minGrade or better in the prerequisite course ($prerequisiteCourseName - CourseID: $prerequisiteCRN) to register for this class. Please review your course selection.</div>";
                }
                
                
            }
    
            if (in_array($courseDetailsRow['TimeSlotID'], $selectedTimeSlots)) {
                $timeSlotConflict = true;
				echo "Enrollment failed: Time slot conflict detected.";
                return "Enrollment failed: Time slot conflict detected.";
            }
            $selectedTimeSlots[] = $courseDetailsRow['TimeSlotID'];
        }
    }
    
    
    // Check for conflicts with current enrollments
    foreach ($selectedTimeSlots as $selectedTimeSlot) {
        if (in_array($selectedTimeSlot, $currentEnrollmentTimeSlots)) {
			echo "Enrollment failed: Conflict with previous or currently enrolled course.";
            return "Enrollment failed: Conflict with previous or currently enrolled course.";
        }
    }

    if (!$timeSlotConflict) {
        mysqli_begin_transaction($conn);

        try {
            foreach ($_POST['courses'] as $selectedCRN) {
                $selectedCRN = mysqli_real_escape_string($conn, $selectedCRN);
                $courseDetailsQuery = "SELECT CourseID, AvailableSeats, SemesterID FROM coursesection WHERE CRN = '$selectedCRN'";
                $courseDetailsResult = mysqli_query($conn, $courseDetailsQuery);
				
				$checkHistoryQuery = "SELECT * FROM studenthistory WHERE StudentID = ? AND CRN = ?";
				$stmt = mysqli_prepare($conn, $checkHistoryQuery);
				mysqli_stmt_bind_param($stmt, "ss", $uid, $selectedCRN);
				mysqli_stmt_execute($stmt);
				$resultHistory = mysqli_stmt_get_result($stmt);

                if ($courseDetailsRow = mysqli_fetch_assoc($courseDetailsResult)) {
                    if ($courseDetailsRow['AvailableSeats'] > 0) {
                        if(mysqli_num_rows($resultHistory) == 0) {
						// If the entry doesn't exist, proceed with insertion
						$insertHistoryQuery = "INSERT INTO studenthistory (StudentID, CRN, CourseID, SemesterID, Grade) VALUES (?, ?, ?, ?, ?)";
						$grade = 'IP'; // Set the initial grade
						$stmt = mysqli_prepare($conn, $insertHistoryQuery);
						mysqli_stmt_bind_param($stmt, "sssss", $uid, $selectedCRN, $courseDetailsRow['CourseID'], $courseDetailsRow['SemesterID'], $grade);
						mysqli_stmt_execute($stmt);
							} else { 
							$insertHistoryQuery = "UPDATE studenthistory SET Grade = 'IP' WHERE StudentID = '$uid' AND CRN = '$selectedCRN'";
							$courseDetailsResult = mysqli_query($conn, $insertHistoryQuery);
							}

                        // Enrollment logic for enrollment table
                        $currentDateTime = date("Y-m-d H:i:s"); // Get the current date and time
                        $insertEnrollmentQuery = "INSERT INTO enrollment (StudentID, CRN, Grade, DOE) VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $insertEnrollmentQuery);
                        mysqli_stmt_bind_param($stmt, "ssss", $uid, $selectedCRN, $grade, $currentDateTime);
                        mysqli_stmt_execute($stmt);

                        // Update the available seats
                        $updateSeatsQuery = "UPDATE coursesection SET AvailableSeats = AvailableSeats - 1 WHERE CRN = ?";
                        $stmt = mysqli_prepare($conn, $updateSeatsQuery);
                        mysqli_stmt_bind_param($stmt, "s", $selectedCRN);
                        mysqli_stmt_execute($stmt);
                    } else {
                        mysqli_rollback($conn);
						echo "Enrollment failed: No available seats in course CRN $selectedCRN.";
                        return "Enrollment failed: No available seats in course CRN $selectedCRN.";
                    }
                }
            }
            mysqli_commit($conn);
            echo "You have been successfully enrolled in the selected courses.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "An error occurred: " . $e->getMessage();
        }
    }
}
}
if (isset($_GET['drop_course'])) {
    $dropCRN = mysqli_real_escape_string($conn, $_GET['drop_course']);

    // Check if the user is enrolled in the course
    // Check if the user is enrolled in the course and get the SemesterID from the enrollment table
$checkEnrollmentQuery = "SELECT enrollment.StudentID, studenthistory.SemesterID
FROM enrollment
JOIN studenthistory ON enrollment.StudentID = studenthistory.StudentID AND enrollment.CRN = studenthistory.CRN
WHERE enrollment.StudentID = '$uid' AND enrollment.CRN = '$dropCRN'";

    $checkEnrollmentResult = mysqli_query($conn, $checkEnrollmentQuery);

    if (mysqli_num_rows($checkEnrollmentResult) > 0) {
        $enrollmentRow = mysqli_fetch_assoc($checkEnrollmentResult);
        $semesterID = $enrollmentRow['SemesterID'];

        // Check if the course has already been dropped in the same semester
        $checkDropQuery = "SELECT * FROM studenthistory WHERE StudentID = '$uid' AND CRN = '$dropCRN' AND SemesterID = '$semesterID' AND Grade = 'Dro'";
        $checkDropResult = mysqli_query($conn, $checkDropQuery);

        if (mysqli_num_rows($checkDropResult) > 0) {
			echo "Course with CRN $dropCRN has already been dropped in this semester.";
            return "Course with CRN $dropCRN has already been dropped in this semester.";
        } else {
            // Update the grade to "Dropped" in studenthistory
            $updateHistoryQuery = "UPDATE studenthistory SET Grade = 'Dro' WHERE StudentID = '$uid' AND CRN = '$dropCRN' AND SemesterID = '$semesterID'";
            mysqli_query($conn, $updateHistoryQuery);

            // Increase the available seats in coursesection
            $updateSeatsQuery = "UPDATE coursesection SET AvailableSeats = AvailableSeats + 1 WHERE CRN = '$dropCRN'";
            mysqli_query($conn, $updateSeatsQuery);

            // Remove the course from enrollment
            $deleteEnrollmentQuery = "DELETE FROM enrollment WHERE StudentID = '$uid' AND CRN = '$dropCRN'";
            mysqli_query($conn, $deleteEnrollmentQuery);

            echo "Course with CRN $dropCRN has been dropped successfully.";
        }
    } else {
        return "Course with CRN $dropCRN is not currently enrolled.";
    }
}

$currentSemester = "20232"; 
?>




<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <div class="header">
      <h1>Academic Profile</h1>
      <button class="back-button" onclick="goBack()">Back</button>
   </div>


   <link rel="stylesheet" href="css/fatman1.css">

<style>
   .header {
      background: #000;
      color: #fff;
      padding: 20px;
      text-align: center;
      display: flex;
      justify-content: space-between;
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

   .header h1 {
      font-size: 36px;
   }

   .header a {
      text-decoration: none;
      color: #fff;
      margin-right: 20px; /* Add some spacing between buttons */
      font-size: 18px;
      transition: background-color 0.3s ease; /* Add a smooth background color transition */
   }

   .header a:hover {
      background-color: #333; /* Change the background color on hover */
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
   .selected-courses-box {
    border: 2px solid #333;
    background-color: #f2f2f2;
    padding: 10px;
    font-size: 18px;
    margin-top: 20px;
}


.top-right-container {
    margin-top: 20px; /* Add margin-top instead of top */
    width: 50%;
    box-sizing: border-box;
    padding: 20px;
}


      /* Style the table within the top-right container */
      .enrolled-courses-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 20px;
      }

      .enrolled-courses-table, th, td {
         border: 1px solid #000;
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
   </style>

   <script>

       function updateSelectedCourses() {
         var selectedCourses = document.querySelectorAll('input[name="courses[]"]:checked');
         var displayBox = document.getElementById("selectedCoursesBox");
         var selectedInfo = Array.from(selectedCourses).map(function(checkbox) {
            return checkbox.value + ' - ' + checkbox.getAttribute('data-course-name');
         });

         if (selectedInfo.length > 0) {
            displayBox.textContent = "Selected Courses:";
            var ul = document.createElement('ul');
            selectedInfo.forEach(function(course) {
               var li = document.createElement('li');
               li.textContent = course;
               ul.appendChild(li);
            });
            displayBox.appendChild(ul);
         } else {
            displayBox.textContent = "Selected Courses: None";
         }
      }

      function goBack() {
            window.history.back();
        }


      // Function to handle course selection
      function handleCourseSelection(checkbox, semesterID) {
         if (semesterID === "20232" OR semesterID === '0') { // Check if the semester is Fall 2023
            var popup = document.getElementById("popup");
            popup.style.display = "block";
            checkbox.checked = false; // Uncheck the checkbox
            setTimeout(function() {
               popup.style.display = "none";
            }, 3000); // Hide the popup after 3 seconds
         } else {
            // Course selection logic (e.g., updateSelectedCourses)
            updateSelectedCourses();
         }
      }

   </script>
</head>

<body>
<div id="selectedCoursesBox" class="selected-courses-box">Selected Courses: None</div>
    <div class="course-assignment-container">
        <h1>Assign/Drop Courses</h1>
        <!-- ... Existing search input and course assignment form ... -->
        <!-- ... Existing search input and course assignment form ... -->
        <div class="search-container">
       General Search: 
      <input type="text" id="searchInput" placeholder="General Search..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
   </div>

        <div class="filter-container">
     CRN:
      <input type="text" id="crnSearch" placeholder="Search by CRN..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
	  
       Course ID:
      <input type="text" id="courseidSearch" placeholder="Search by Course ID..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
      
      Course Name:
      <input type="text" id="coursenameSearch" placeholder="Search by Course Name..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
    </select>

    <label for="sectionFilter">Section Number:</label>
   <select id="sectionFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($sectionnums as $SectionNum): ?>
         <option value="<?php echo $SectionNum; ?>"><?php echo $SectionNum; ?></option>
      <?php endforeach; ?>
   </select>



    <label for="deptFilter">Department:</label>
   <select id="deptFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($departmentIDs as $deptID): ?>
         <option value="<?php echo $deptID; ?>"><?php echo $deptID; ?></option>
      <?php endforeach; ?>
   </select>
</div>



<div class="course-assignment-container">
    <label for="dayFilter">Day:</label>
   <select id="dayFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($days as $day): ?>
         <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
      <?php endforeach; ?>
   </select>


    <label for="buildingFilter">Building:</label>
   <select id="buildingFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($buildingNames as $buildingName): ?>
         <option value="<?php echo $buildingName; ?>"><?php echo $buildingName; ?></option>
      <?php endforeach; ?>
   </select>

       <label for="roomFilter">Room:</label>
   <select id="roomFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($roomIDs as $roomID): ?>
         <option value="<?php echo $roomID; ?>"><?php echo $roomID; ?></option>
      <?php endforeach; ?>
   </select>
   
   <label for="timeFilter">Time:</label>
   <select id="timeFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($times as $time): ?>
         <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
      <?php endforeach; ?>
   </select>
</div>

<div class="course-assignment-container">
Professor Name:
      <input type="text" id="professorSearch" placeholder="Search by Professor..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
	  
	  <label for="semesterFilter">Semester:</label>
   <select id="semesterFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($semesterNames as $semester): ?>
         <option value="<?php echo $semester; ?>"><?php echo $semester; ?></option>
      <?php endforeach; ?>
	  </select>
	  <label for="seatsFilter">Available Seats:</label>
   <select id="seatsFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($availbleseats as $seats): ?>
         <option value="<?php echo $seats; ?>"><?php echo $seats; ?></option>
      <?php endforeach; ?>
   </select>
</div>

        <div class="top-right-container">
      <h2>Schedule</h2>
      <table class="enrolled-courses-table">
            <thead>
               <tr>
                  <th>CRN</th>
                  <th>Course Name</th>
                  <th>Day</th>
                  <th>Building</th>
                  <th>Room</th>
                  <th>Section</th>
                  <th>Time</th>
				     <th>Semester</th>
					 <th>Drop</th>
               </tr>
            </thead>
            <tbody>
               <?php
             // Fetch and display currently enrolled courses
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
			 coursesection.SemesterID
             FROM enrollment
             JOIN coursesection ON enrollment.CRN = coursesection.CRN
             JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID
             JOIN day ON timeslot.DayID = day.DayID
             JOIN course ON coursesection.CourseID = course.CourseID
             JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
             JOIN room ON coursesection.RoomID = room.RoomID
             JOIN building ON room.BuildingID = building.BuildingID
			 JOIN semester ON coursesection.SemesterID = semester.SemesterID
             WHERE enrollment.StudentID = '$uid' AND coursesection.SemesterID <> 0
			 GROUP BY coursesection.CRN
			ORDER BY coursesection.CRN ASC";
$enrolledCoursesResult = mysqli_query($conn, $enrolledCoursesQuery);


               while ($enrolledCourse = mysqli_fetch_assoc($enrolledCoursesResult)) {
                  echo "<tr>";
                  echo "<td>{$enrolledCourse['CRN']}</td>";
                  echo "<td>{$enrolledCourse['CourseName']}</td>";
                  echo "<td>";

					$weekdays = explode('/', $enrolledCourse['Weekdays']);
				echo implode('/', array_unique($weekdays)); // Displaying concatenated weekdays

				echo "</td>";
                  echo "<td>{$enrolledCourse['BuildingName']}</td>";
                  echo "<td>{$enrolledCourse['RoomNum']}</td>";
                  echo "<td>{$enrolledCourse['SectionNum']}</td>";
                  echo "<td>";
					$startTime = date("g:i A", strtotime($enrolledCourse['StartTime']));
					$endTime = date("g:i A", strtotime($enrolledCourse['EndTime']));
					echo $startTime . " to " . $endTime;
					echo "</td>";
				  echo "<td>{$enrolledCourse['SemesterName']}</td>";
				   if ($enrolledCourse['SemesterID'] === "20241") {
                  echo "<td><a href='?UID=$uid&drop_course={$enrolledCourse['CRN']}'>Drop</a></td>";
				  } else{
				   echo "<td>Cannot Drop</td>"; 
				   }
                  echo "</tr>";
               }
               ?>
            </tbody>
         </table>
      </div>
        
        <form action="" method="post">
            <table id="courseTable">
            <thead>
    <tr>
        <th>CRN</th>
		 <th>Course ID</th>
        <th>Course Name</th>
        <th>Section Num</th>
		 <th>Prerequisites</th>
        <th>Department</th>
        <th>Day</th>
        <th>Building</th>
        <th>Room</th>
        <th>Time</th>
        <th>Professor Name</th>  <!-- Add faculty name header -->
		<th>Semester</th>
		<th>Available Seats</th>
		<th>Credits</th>
		<th>Course Type</th>
        <th>Select</th>
    </tr>
</thead>

<tbody>
<?php foreach ($courses as $course): ?>
    <tr>
        <td><?php echo $course['CRN']; ?></td>
		<td><?php echo $course['CourseID']; ?></td>
        <td><?php echo $course['CourseName']; ?></td>
        <td><?php echo $course['SectionNum']; ?></td>
			 <td>
                <?php if (!empty($course['Prerequisites'])): ?>
                    <ul>
                        <?php foreach ($course['Prerequisites'] as $prerequisite): ?>
                            <li><?php echo $prerequisite; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    None
                <?php endif; ?>
            </td>
			<td><?php echo $course['DeptName']; ?></td>
        <td>
					<?php 
						$weekdays = explode('/', $course['Weekdays']);
						echo implode('/', array_unique($weekdays)); // Displaying concatenated weekdays
					?>
				</td>
        <td><?php echo $course['BuildingName']; ?></td>
        <td><?php echo $course['RoomNum']; ?></td>
            <td>
					<?php
					$startTime = date("g:i A", strtotime($course['StartTime']));
					$endTime = date("g:i A", strtotime($course['EndTime']));
					echo $startTime . " to " . $endTime;
					?>
				</td>
		  <td><?php echo $course['FacultyFirstName'] . " " . $course['FacultyLastName']; ?></td> 
        <td><?php echo $course['SemesterName']; ?></td>
        <td><?php echo $course['AvailableSeats']; ?></td>
        <td><?php echo $course['Credits']; ?></td>
        <td><?php echo $course['CourseType']; ?></td>
        <td>
        <?php
                     // Check if the course is in the Fall 2023 semester (semesterID 20232)
                     if ($course['SemesterID'] === "20232" OR $course['SemesterID'] === "0") {
                        echo '<input type="checkbox" name="courses[]" value="' . $course['CRN'] . '" data-course-name="' . $course['CourseName'] . '" disabled>';
                     } else {
                        echo '<input type="checkbox" name="courses[]" value="' . $course['CRN'] . '" data-course-name="' . $course['CourseName'] . '" onchange="handleCourseSelection(this, \'' . $course['SemesterID'] . '\')">';
                     }
                     ?>
                  </td>
               </tr>
            <?php endforeach; ?>
</tbody>
		 </table>
            <input type="submit" value="Assign Course">
        </form>

<script>
      function searchAndFilterTable() {
        var searchText = document.getElementById('searchInput').value.toUpperCase();
        var crnText = document.getElementById('crnSearch').value.toUpperCase();
		var courseidText = document.getElementById('courseidSearch').value.toUpperCase();
        var coursenameText = document.getElementById('coursenameSearch').value.toUpperCase();
        var professorText = document.getElementById('professorSearch').value.toUpperCase();
        var filters = {
            'Section Num': document.getElementById('sectionFilter').value.toUpperCase(),
            'Department': document.getElementById('deptFilter').value.toUpperCase(),
            'Building': document.getElementById('buildingFilter').value.toUpperCase(),
            'Day': document.getElementById('dayFilter').value.toUpperCase(),
            'Room': document.getElementById('roomFilter').value.toUpperCase(),
            'Time': document.getElementById('timeFilter').value.toUpperCase(),
            'Semester': document.getElementById('semesterFilter').value.toUpperCase(),
			'Available Seats': document.getElementById('seatsFilter').value.toUpperCase()
            // Add other filters here
        };

        var table = document.getElementById('courseTable'); // Get the courseTable by ID
        var rows = table.getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var showRow = true;

    for (var filter in filters) {
            var columnIndex = getColumnIndex(filter);
            if (columnIndex > -1) {
                var cell = row.getElementsByTagName('td')[columnIndex];
                if (cell) {
                    var cellValue = cell.textContent || cell.innerText;

                    if (filters[filter] !== 'ALL') {
                        var filterValue = filters[filter];

                        // Handle '0' as a special case for Available Seats
                        if (filter === 'Available Seats' && filterValue === '0') {
                            if (cellValue !== '0') {
                                showRow = false;
                                break;
                            }
                        } else {
                            if (cellValue.toUpperCase().indexOf(filterValue) === -1) {
                                showRow = false;
                                break;
                            }
                        }
                    }
                }
            }
        }

            var crnCell = row.getElementsByTagName('td')[0]; // Assuming CRN is in the 1st column
            if (crnCell) {
                var crnValue = crnCell.textContent || crnCell.innerText;
                if (crnValue.toUpperCase().indexOf(crnText) === -1 && crnText !== '') {
                    showRow = false;
                }
            }
			
            var courseidCell = row.getElementsByTagName('td')[1]; // Assuming Course ID is in the 1st column
            if (courseidCell) {
                var courseidValue = courseidCell.textContent || courseidCell.innerText;
                if (courseidValue.toUpperCase().indexOf(courseidText) === -1 && courseidText !== '') {
                    showRow = false;
                }
            }
            
            var coursenameCell = row.getElementsByTagName('td')[2]; // Assuming Course Name is in the 1st column
            if (coursenameCell) {
                var coursenameValue = coursenameCell.textContent || coursenameCell.innerText;
                if (coursenameValue.toUpperCase().indexOf(coursenameText) === -1 && coursenameText !== '') {
                    showRow = false;
                }
            }

            var professorCell = row.getElementsByTagName('td')[10]; // Assuming Professor Name is in the 9th column
            if (professorCell) {
                var professorValue = professorCell.textContent || professorCell.innerText;
                if (professorValue.toUpperCase().indexOf(professorText) === -1 && professorText !== '') {
                    showRow = false;
                }
            }
            
            var rowData = row.textContent || row.innerText;
            if (rowData.toUpperCase().indexOf(searchText) > -1 && showRow) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function getColumnIndex(columnName) {
         var table = document.getElementById('courseTable'); // Get the courseTable by ID
         var header = table.querySelector("thead");
         var thArray = Array.from(header.querySelectorAll("th"));
         for (var i = 0; i < thArray.length; i++) {
            if (thArray[i].textContent.trim() === columnName) {
               return i;
            }
         }
         return -1;
      }

    function resetTable() {
        var table = document.getElementById('courseTable'); // Get the courseTable by ID
        var rows = table.getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            rows[i].style.display = '';
        }

        document.getElementById('searchInput').value = '';
		document.getElementById('crnSearch').value = '';
		document.getElementById('coursenameSearch').value = '';
		document.getElementById('sectionFilter').value = '';
        // Reset all filter dropdowns to default
        document.getElementById('deptFilter').value = '';
        document.getElementById('buildingFilter').value = '';
        document.getElementById('dayFilter').value = '';
        document.getElementById('roomFilter').value = '';
        document.getElementById('timeFilter').value = '';
		document.getElementById('professorSearch').value = '';
        document.getElementById('semesterFilter').value = '';
		document.getElementById('seatsFilter').value = '';
        // Add other filter reset lines here if needed
    }
</script>

</body>

</html>