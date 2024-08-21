<?php

@include 'config1.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form1.php');
    exit;
}

if (isset($_GET['CRN'])) {
    $CRN = mysqli_real_escape_string($conn, $_GET['CRN']);
    
    $query = "SELECT * FROM coursesection WHERE CRN = '$CRN'";
    $result = mysqli_query($conn, $query);

    if (!$row = mysqli_fetch_assoc($result)) {
        echo "<div class='message error-message'>Course section not found.</div>";
        echo "<script>setTimeout(function() { window.location.href = 'masterschedule1.php'; }, 5000);</script>";
        exit;
    }
} else {
    echo "<div class='message error-message'>No CRN provided.</div>";
    echo "<script>setTimeout(function() { window.location.href = 'masterschedule1.php'; }, 5000);</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facultyID = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $sectionNum = mysqli_real_escape_string($conn, $_POST['section_num']);
    $semesterID = mysqli_real_escape_string($conn, $_POST['semester_id']);
    $availableSeats = mysqli_real_escape_string($conn, $_POST['available_seats']);

    if (isset($_POST['timeslot_room'])) {
        list($timeslotID, $roomID) = explode('_', $_POST['timeslot_room']);
        $timeslotID = mysqli_real_escape_string($conn, $timeslotID);
        $roomID = mysqli_real_escape_string($conn, $roomID);
    } else {
        echo "<div class='message error-message'>Timeslot and room information is required.</div>";
        echo "<script>setTimeout(function() { window.location.href = 'masterschedule1.php'; }, 5000);</script>";
        exit;
    }


    $checkDeptQuery = "SELECT f.DeptID AS FacultyDept, c.DeptID AS CourseDept
    FROM faculty AS f
    INNER JOIN course AS c ON f.FacultyID = '$facultyID'
    WHERE CRN = '$CRN'";
    $checkDeptResult = mysqli_query($conn, $checkDeptQuery);

    if ($checkDeptResult && $deptRow = mysqli_fetch_assoc($checkDeptResult)) {
        $facultyDeptID = $deptRow['FacultyDept'];
        $courseDeptID = $deptRow['CourseDept'];

        if ($facultyDeptID != $courseDeptID) {
            echo "<div class='message error-message'>Error: The selected faculty does not belong to the same department as the course.</div>";
            exit;
        }
    } else {
        echo "<div class='message error-message'>Error checking faculty department: " . mysqli_error($conn) . "</div>";
        exit;
    }

    $updateQuery = "UPDATE coursesection 
                    SET FacultyID = '$facultyID', RoomID = '$roomID', TimeSlotID = '$timeslotID',
                        SectionNum = '$sectionNum', SemesterID = '$semesterID', AvailableSeats = '$availableSeats'
                    WHERE CRN = '$CRN'";
    
    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        echo "<div class='message success-message'>Course section with CRN $CRN updated successfully.</div>";
        header("refresh:5;url=masterschedule1.php");
        exit;
    } else {
        echo "<div class='message error-message'>Error updating course section: " . mysqli_error($conn) . "</div>";
        exit;
    }
}

$departmentsQuery = "SELECT DeptID, DeptName FROM dept";
$departmentsResult = mysqli_query($conn, $departmentsQuery);
$departments = [];
while ($deptRow = mysqli_fetch_assoc($departmentsResult)) {
    $departments[] = $deptRow;
}

$query = "SELECT cs.*, c.CourseName, u.FirstName, u.LastName
FROM coursesection cs 
JOIN course c ON cs.CourseID = c.CourseID 
JOIN faculty f ON cs.FacultyID = f.FacultyID
JOIN user u ON f.FacultyID = u.UID
WHERE cs.CRN = '$CRN'";


$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {

} else {
echo "Course section not found.";
exit;
}


$deptQuery = "SELECT DeptID FROM course WHERE CourseID = (SELECT CourseID FROM coursesection WHERE CRN = '$CRN')";
$deptResult = mysqli_query($conn, $deptQuery);

if ($deptRow = mysqli_fetch_assoc($deptResult)) {
    $deptID = $deptRow['DeptID'];
} else {
    echo "Department not found for the given CRN.";
    exit;
}


$facultyQuery = "SELECT * FROM facultydept WHERE DeptID = '$deptID'";
$facultyResult = mysqli_query($conn, $facultyQuery);
$availableSlotsQuery = "
SELECT 
    t.TimeSlotID, 
	GROUP_CONCAT(DISTINCT d.Weekday ORDER BY d.Weekday SEPARATOR '/') AS Weekdays,
    p.StartTime, 
    p.EndTime, 
    b.BuildingName, 
    r.RoomNum, 
    r.RoomID,
    r.RoomType  -- Add this line to fetch RoomType
FROM timeslot t
JOIN day d ON t.DayID = d.DayID
JOIN periodd p ON t.PeriodID = p.PeriodID
JOIN room r ON 1 = 1
JOIN building b ON r.BuildingID = b.BuildingID
LEFT JOIN coursesection cs ON t.TimeSlotID = cs.TimeSlotID AND r.RoomID = cs.RoomID
WHERE cs.CRN IS NULL OR cs.CRN = '$CRN'
AND r.RoomType <> 'office'
GROUP BY t.TimeSlotID, p.StartTime, p.EndTime, b.BuildingName, r.RoomNum, r.RoomID, r.RoomType
ORDER BY Weekdays, p.StartTime, r.RoomNum;";

$availableSlotsResult = mysqli_query($conn, $availableSlotsQuery);

$availableSlots = [];
while ($slotRow = mysqli_fetch_assoc($availableSlotsResult)) {
    $availableSlots[] = $slotRow;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <style>

 .header {
         background: #000;
         color: #fff;
         padding: 0px;
         text-align: center;
         display: flex;
         justify-content: space-between;
      }

      .header h1 {
         font-size: 36px;
      }

      .header .back-button {
         background: #000;
         color: #fff;
         padding: 10px 20px;
         text-decoration: none;
         border-radius: 5px;
         margin-right: 10px;
      }
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f2f2f2;
}

.container {
    display: flex;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.left-column {
    flex: 1;
    padding: 20px;
}

.right-column {
    flex: 1;
    padding: 20px;
}

h1 {
    text-align: center;
}


.form-container {
    margin-top: 20px;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input[type="text"],
input[type="number"],
select {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}


input[type="submit"] {
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

input[type="submit"]:hover {
    background-color: #0056b3;
}


#faculty-table {
    border-collapse: collapse;
    width: 100%;
}

#faculty-table th,
#faculty-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

#faculty-table th {
    background-color: #f2f2f2;
}


table {
    margin-top: 20px;
}


select {
    width: 100%;
}


#timeslot, #section_num, #faculty_id {
    width: 100%;
}


@media screen and (max-width: 600px) {
    .container {
        flex-direction: column;
    }

    .left-column,
    .right-column {
        flex: auto;
        padding: 10px;
    }

    form {
        flex-direction: column;
    }

    input[type="text"],
    input[type="number"],
    select {
        font-size: 14px;
    }

    .message {
    font-size: 18px;
    padding: 10px;
    margin-bottom: 15px;
    text-align: center;
}

.error-message {
    color: red;
    border: 1px solid red;
}

.success-message {
    color: green;
    border: 1px solid green;
}

}

</style>

</head>
<body>

<div class="header">
        <h1>Create Course Section</h1>
        <a href="master_schedule1.php" class="back-button">Back</a>
    </div>


<div class="container">
        <div class="left-column">
            <h1>Faculty Data</h1>

             
        <label for="dept_id">Select Department:</label>
        <select id="dept_id" name="dept_id" required>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['DeptID']; ?>">
                    <?php echo $dept['DeptName']; ?>
                </option>
            <?php endforeach; ?>
        </select>
            
            <table id="faculty-table">
                <thead>
                    <tr>
                        <th>Faculty ID</th>
                        <th>Percent Time</th>
                        <th>Faculty</th>
                        <th>Total Classes for Part Time Faculty</th>
                        <th>Total Classes for Full Time Faculty</th>
                    </tr>
                </thead>
                <tbody>
                  
                </tbody>
            </table>




            </div>
<div class="right-column">
      <div class="form-container">
      <h1>Update Course Section - <?php echo htmlspecialchars($row['CourseName']); ?> (<?php echo $row['FirstName'] . ' ' . $row['LastName']; ?>)</h1>

    
    <form method="POST" action="updatecoursesection.php?CRN=<?php echo $CRN; ?>">
        <label for="CRN">CRN:</label>
        <input type="text" name="CRN" id="CRN" value="<?php echo $row['CRN']; ?>" readonly>
        
        <label for="section_num">Section Number:</label>
        <input type="text" name="section_num" id="section_num" value="<?php echo $row['SectionNum']; ?>" required>

        <label for="faculty_id">Faculty:</label>
<select name="faculty_id" id="faculty_id" required>
    
</select>

 
<label for="timeslot">Select Timeslot:</label>
<select id="timeslot_room" name="timeslot_room" required>
            <?php foreach ($availableSlots as $slot): ?>
					<?php
            $selected = ($slot['TimeSlotID'] . '_' . $slot['RoomID'] === $row['TimeSlotID'] . '_' . $row['RoomID']) ? 'selected' : '';
        ?>
                <option value="<?php echo $slot['TimeSlotID'] . '_' . $slot['RoomID']; ?>" <?php echo $selected; ?>>
                    <?php echo $slot['Weekdays'] . ', ' . $slot['StartTime'] . '-' . $slot['EndTime'] . ', ' . $slot['BuildingName'] . ' - Room ' . $slot['RoomNum'] . ' (' . $slot['RoomType'] . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select>



        <label for="semester_id">Semester ID:</label>
        <input type="text" name="semester_id" id="semester_id" value="<?php echo $row['SemesterID']; ?>" required>

        <label for="available_seats">Available Seats:</label>
        <input type="number" name="available_seats" id="available_seats" value="<?php echo $row['AvailableSeats']; ?>" required>
        
        <input type="submit" value="Update Course Section">
    </form>
    </div>
</div>


    <script>
    
    const deptSelect = document.getElementById('dept_id');
    const facultyTable = document.getElementById('faculty-table');

    
    deptSelect.addEventListener('change', function () {
        const selectedDeptID = deptSelect.value;

        
        fetch('getfac.php?dept_id=' + selectedDeptID)
            .then(response => response.json())
            .then(data => {
                
                facultyTable.querySelector('tbody').innerHTML = '';

                
                data.forEach(faculty => {
                    const row = facultyTable.querySelector('tbody').insertRow();
                    const cell1 = row.insertCell(0); 
                    const cell2 = row.insertCell(1); 
                    const cell3 = row.insertCell(2); 
                    const cell4 = row.insertCell(3); 
                    const cell5 = row.insertCell(4); 

                    cell1.textContent = faculty.FacultyID;
                    cell2.textContent = faculty.PercentTime;
                    cell3.textContent = faculty.FirstName + ' ' + faculty.LastName; 
                    cell4.textContent = faculty.TotalClassesPT; 
                    cell5.textContent = faculty.TotalClassesFT; 
                });
            })
            .catch(error => {
                console.error('Error fetching faculty data:', error);
            });
    });


    document.addEventListener('DOMContentLoaded', function () {
    const deptID = '<?php echo $deptID; ?>'; 

    fetch('getfac.php?dept_id=' + deptID)
        .then(response => response.json())
        .then(data => {
            const facultySelect = document.getElementById('faculty_id');
            facultySelect.innerHTML = ''; 

            data.forEach(faculty => {
                const option = document.createElement('option');
                option.value = faculty.FacultyID;
                option.textContent = faculty.FirstName + ' ' + faculty.LastName;
                facultySelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching faculty data:', error);
        });
});

    
</script>
</body>
</html>
