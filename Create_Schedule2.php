<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

@include 'config1.php'; // Include your database configuration file

session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to assign courses.";
    exit;
}

$uid = $_SESSION['UID'];

// Fetch available courses with additional details, ordered by CRN
$query = "SELECT coursesection.CRN, coursesection.CourseID, coursesection.AvailableSeats, timeslot.TimeSlotID, day.Weekday, masterschedule.CourseName, room.RoomNum, building.BuildingName, periodd.StartTime, periodd.EndTime, coursesection.SectionNum
          FROM coursesection 
          JOIN timeslot ON coursesection.TimeSlotID = timeslot.TimeSlotID 
          JOIN day ON timeslot.DayID = day.DayID
          JOIN masterschedule ON coursesection.CourseID = masterschedule.CourseID 
          JOIN periodd ON timeslot.PeriodID = periodd.PeriodID
          JOIN room ON coursesection.RoomID = room.RoomID
          JOIN building ON room.BuildingID = building.BuildingID
          JOIN dept ON masterschedule.deptID = dept.deptID
          WHERE masterschedule.major = '$major' ";
          
$result = mysqli_query($conn, $query);

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

// Fetch TimeSlotIDs of courses the student is currently enrolled in
$currentEnrollmentsQuery = "SELECT TimeSlotID FROM coursesection JOIN studenthistory ON coursesection.CRN = studenthistory.CRN WHERE studenthistory.StudentID = '$uid'";
$currentEnrollmentsResult = mysqli_query($conn, $currentEnrollmentsQuery);
$currentEnrollmentTimeSlots = [];
while ($enrollmentRow = mysqli_fetch_assoc($currentEnrollmentsResult)) {
    $currentEnrollmentTimeSlots[] = $enrollmentRow['TimeSlotID'];
}

// Handle course assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['courses']) && is_array($_POST['courses'])) {
    $selectedTimeSlots = [];
    $timeSlotConflict = false;

    // Collect TimeSlotIDs of selected courses
    foreach ($_POST['courses'] as $selectedCRN) {
        $selectedCRN = mysqli_real_escape_string($conn, $selectedCRN);
        $courseDetailsQuery = "SELECT TimeSlotID FROM coursesection WHERE CRN = '$selectedCRN'";
        $courseDetailsResult = mysqli_query($conn, $courseDetailsQuery);
        if ($courseDetailsRow = mysqli_fetch_assoc($courseDetailsResult)) {
            if (in_array($courseDetailsRow['TimeSlotID'], $selectedTimeSlots)) {
                $timeSlotConflict = true;
                echo "Enrollment failed: Time slot conflict detected.";
                break;
            }
            $selectedTimeSlots[] = $courseDetailsRow['TimeSlotID'];
        }
    }

    // Check for conflicts with current enrollments
    foreach ($selectedTimeSlots as $selectedTimeSlot) {
        if (in_array($selectedTimeSlot, $currentEnrollmentTimeSlots)) {
            echo "Enrollment failed: Conflict with currently enrolled course.";
            exit;
        }
    }

    if (!$timeSlotConflict) {
        mysqli_begin_transaction($conn);
    
        try {
            foreach ($_POST['courses'] as $selectedCRN) {
                $selectedCRN = mysqli_real_escape_string($conn, $selectedCRN);
                $courseDetailsQuery = "SELECT CourseID, AvailableSeats FROM coursesection WHERE CRN = '$selectedCRN'";
                $courseDetailsResult = mysqli_query($conn, $courseDetailsQuery);
                if ($courseDetailsRow = mysqli_fetch_assoc($courseDetailsResult)) {
                    if ($courseDetailsRow['AvailableSeats'] > 0) {
                        // Enrollment logic for studenthistory table
                        $insertHistoryQuery = "INSERT INTO studenthistory (StudentID, CRN, CourseID, SemesterID, Grade) VALUES (?, ?, ?, ?, ?)";
                        $grade = 'In Progress'; // Set the initial grade
                        $stmt = mysqli_prepare($conn, $insertHistoryQuery);
                        mysqli_stmt_bind_param($stmt, "sssss", $uid, $selectedCRN, $courseDetailsRow['CourseID'], $semesterID, $grade);
                        mysqli_stmt_execute($stmt);
    
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
                        echo "Enrollment failed: No available seats in course CRN $selectedCRN.";
                        mysqli_rollback($conn);
                        exit;
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
if (isset($_GET['drop_course'])) {
    $dropCRN = mysqli_real_escape_string($conn, $_GET['drop_course']);

    // Check if the user is enrolled in the course
    $checkEnrollmentQuery = "SELECT StudentID FROM enrollment WHERE StudentID = '$uid' AND CRN = '$dropCRN'";
    $checkEnrollmentResult = mysqli_query($conn, $checkEnrollmentQuery);

    if (mysqli_num_rows($checkEnrollmentResult) > 0) {
        // Remove the course from studenthistory
        $deleteEnrollmentQuery = "DELETE FROM enrollment WHERE StudentID = '$uid' AND CRN = '$dropCRN'";
        mysqli_query($conn, $deleteEnrollmentQuery);

        // Increase the available seats in coursesection
        $updateSeatsQuery = "UPDATE coursesection SET AvailableSeats = AvailableSeats + 1 WHERE CRN = '$dropCRN'";
        mysqli_query($conn, $updateSeatsQuery);

        echo "Course with CRN $dropCRN has been dropped successfully.";
    } else {
        echo "Course with CRN $dropCRN is not currently enrolled.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <div class="header">
      <h1>Academic Profile</h1>
      <a href="student_academic_profile1.php" class="back-button">Back to Academic Profile</a>
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

   </style>
   <script>
       function searchTable() {
           var input, filter, table, tr, td, i, txtValue;
           input = document.getElementById("searchInput");
           filter = input.value.toUpperCase();
           table = document.getElementById("courseTable");
           tr = table.getElementsByTagName("tr");

           for (i = 0; i < tr.length; i++) {
               td = tr[i].getElementsByTagName("td");
               var found = false;
               for (var j = 0; j < td.length; j++) {
                   if (td[j]) {
                       txtValue = td[j].textContent || td[j].innerText;
                       if (txtValue.toUpperCase().indexOf(filter) > -1) {
                           found = true;
                           break;
                       }
                   }
               }
               if (found) {
                   tr[i].style.display = "";
               } else {
                   tr[i].style.display = "none";
               }
           }
       }

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

   </script>
</head>

<body>

<div class="course-assignment-container">
        <h1>Assign/Drop Courses</h1>

        <!-- New major selection form -->
        <form action="" method="post">
            <label for="major">Select Major:</label>
            <select name="major" id="major" onchange="this.form.submit()">
                <option value="">Select a Major</option>
                <?php
                // Fetch majors from your database or define them statically
                $majors = ["Business Intelligence and Analytics", "Information Security and Cybersecurity", 
                "Data Science and Analytics", "Software Development and Programming", 
                "English Literature", "Creative Writing",
            "Biology", "Biochemistry and Molecular Biology", 
            "American History", "European History", 
        "Clinical Psychology", "Cognitive Psychology",
    "Microeconomics", "Macroeconomics",
"Pure Mathematics", "Applied Mathematics",
"Organic Chemistry", "Analytical Chemistry",
"Criminology", "Social Inequality",
"Classical Physics", "Quantum Physics",
"Studio Art", "Art History",
"American Politics", "International Politics ",
"Ethics", "Philosophy of Mind",
"Finance", "Marketing"];
                
                foreach ($majors as $major) {
                    echo "<option value='$major'>$major</option>";
                }
                ?>
            </select>
            <noscript><input type="submit" value="Submit"></noscript>
        </form>

        <!-- Display courses based on the selected major -->
        <?php if (isset($courses) && !empty($courses)): ?>
            <table id="courseTable">
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Course Name</th>
                        <th>Section</th> 
                        <th>Day</th>
                        <th>Building</th>
                        <th>Room</th>
                        <th>Time</th>
                        <th>Available Seats</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['CRN']; ?></td>
                            <td><?php echo $course['CourseName']; ?></td>
                            <td><?php echo $course['SectionNum']; ?></td>
                            <td><?php echo $course['Weekday']; ?></td>
                            <td><?php echo $course['BuildingName']; ?></td>
                            <td><?php echo $course['RoomNum']; ?></td>
                            <td><?php echo $course['StartTime'] . " to " . $course['EndTime']; ?></td>
                            <td><?php echo $course['AvailableSeats']; ?></td>
                            <td>
                                <input type="checkbox" name="courses[]" value="<?php echo $course['CRN']; ?>" data-course-name="<?php echo $course['CourseName']; ?>" onchange="updateSelectedCourses()">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <input type="submit" value="Assign Course">
        <?php elseif (isset($_POST['major'])): ?>
            <p>No courses available for the selected major.</p>
        <?php endif; ?>
    </div>

<!-- Add any additional elements or closing tags as needed -->

</body>
</html>