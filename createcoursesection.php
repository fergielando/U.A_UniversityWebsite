<?php
@include 'config1.php'; 

session_start();


if (!isset($_SESSION['UID'])) {
    echo "Please log in to assign courses.";
    exit;
}

$uid = $_SESSION['UID'];



$allCoursesQuery = "SELECT CourseID, CourseName FROM course";
$allCoursesResult = mysqli_query($conn, $allCoursesQuery);
$allCourses = [];
while ($courseRow = mysqli_fetch_assoc($allCoursesResult)) {
    $allCourses[] = $courseRow;
}

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
WHERE NOT EXISTS (
    SELECT 1
    FROM coursesection cs
    WHERE t.TimeSlotID = cs.TimeSlotID AND r.RoomID = cs.RoomID
)
AND r.RoomType <> 'office'
GROUP BY t.TimeSlotID, p.StartTime, p.EndTime, b.BuildingName, r.RoomNum, r.RoomID, r.RoomType
ORDER BY Weekdays, p.StartTime, r.RoomNum;";

$availableSlotsResult = mysqli_query($conn, $availableSlotsQuery);

$availableSlots = [];
while ($slotRow = mysqli_fetch_assoc($availableSlotsResult)) {
    $availableSlots[] = $slotRow;
}

$departmentsQuery = "SELECT DeptID, DeptName FROM dept";
$departmentsResult = mysqli_query($conn, $departmentsQuery);
$departments = [];
while ($deptRow = mysqli_fetch_assoc($departmentsResult)) {
    $departments[] = $deptRow;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    
    
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
    .back-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .back-button:hover {
        background-color: #0056b3;
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
            <h1>Create Course Section</h1>
                <form action="coursesectionprocess.php" method="POST">
                
                    <label for="course_id">Select Course:</label>
                    <select id="course_id" name="course_id" required>
                        <?php foreach ($allCourses as $course): ?>
                            <option value="<?php echo $course['CourseID']; ?>">
                                <?php echo $course['CourseID'] . ' - ' . $course['CourseName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>




        <label for="timeslot">Select Timeslot:</label>
        <select id="timeslot" name="timeslot" required>
            <?php foreach ($availableSlots as $slot): ?>
                <option value="<?php echo $slot['TimeSlotID'] . '_' . $slot['RoomID']; ?>">
                    <?php echo $slot['Weekdays'] . ', ' . $slot['StartTime'] . '-' . $slot['EndTime'] . ', ' . $slot['BuildingName'] . ' - Room ' . $slot['RoomNum'] . ' (' . $slot['RoomType'] . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select>

       
     
        <label for="CRN">CRN:</label>
        <input type="text" id="CRN" name="CRN" required><br>

    
        <label for="semester_id">Semester ID:</label>
        <input type="text" id="semester_id" name="semester_id" value="20241" readonly><br>


        <label for="available_seats">Available Seats:</label>
        <input type="number" id="available_seats" name="available_seats" required><br>

        <label for="section_num">Section Number:</label>
        <input type="text" id="section_num" name="section_num" required><br>

        <label for="faculty_id">Faculty ID:</label>
<input type="text" id="faculty_id" name="faculty_id" required>



        <input type="submit" value="Create Course">
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




    const courseSelect = document.getElementById('course_id');
const facultySelect = document.getElementById('faculty_id');

courseSelect.addEventListener('change', function() {
    const selectedCourseID = courseSelect.value;

    
    fetch(`getfacultyforcourse.php?course_id=${selectedCourseID}`)
        .then(response => response.json())
        .then(data => {
            
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

</body>
</html>










