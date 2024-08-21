<?php
@include 'config1.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:login_form1.php');
}

// Fetch courses data for filtering
$query = "SELECT CourseID, CourseName, DeptID FROM course";
$result = mysqli_query($conn, $query);

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseID = $_POST['courseID'];
    $prCourseID = $_POST['prCourseID'];
    $minGrade = $_POST['minGrade'];
    $dolu = $_POST['dolu'];
    


    // Perform database insertion for prerequisites
    $insertQuery = "INSERT INTO courseprerequisite (CourseID, PRcourseID, MinGrade, DOLU)
                    VALUES ('$courseID', '$prCourseID', '$minGrade', '$dolu')";
    if (mysqli_query($conn, $insertQuery)) {
        // Insertion successful, you can redirect or show a success message
        header('location: create_prerequisite.php'); // Redirect to the same page after insertion
    } else {
        // Insertion failed, handle the error
        echo "Error: " . mysqli_error($conn);
    }
}

// Filter courses by DeptID
if (isset($_POST['deptID'])) {
    $filteredDeptID = $_POST['deptID'];
    // Update the query to fetch courses for the specific department
    $deptQuery = "SELECT CourseID, CourseName FROM course WHERE DeptID = '$filteredDeptID'";
    $deptResult = mysqli_query($conn, $deptQuery);

    $filteredCourses = [];
    while ($row = mysqli_fetch_assoc($deptResult)) {
        $filteredCourses[] = $row;
    }
} else {
    $filteredCourses = $courses;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  

</head>
<body>
<!DOCTYPE html>
<html lang="en">
<a href="course_catalog1.php" class="btn">Back</a>
<head>
    <!-- Add your CSS and other head content here -->
    <style>
       body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: space-between;
}

.left-section {
    flex: 1;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 500px;
}

.right-section {
    flex: 1;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 500px;
}

h1 {
    color: #333;
    text-align: center;
}

form {
    padding: 20px;
}

label {
    display: block;
    margin-top: 10px;
    color: #333;
    font-weight: bold;
}

input[type="text"],
textarea,
select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
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
    font-weight: bold;
}

input[type="submit"]:hover {
    background-color: #4cae4c;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #e9e9e9;
}

/* Styling for the select input */
select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    display: block;


        }
    </style>
</head>
<body>
    <!-- Your existing HTML content here -->

    <div class="container">
        <!-- ... Your form and other content ... -->
    </div>
   <div class="course-list-table">
    <!-- Course list content will go here -->
    <h3>Available Courses</h3>
    
    
    <table id="course-list-table">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Course ID</th>
                <th>Course Prequisites</th>
            </tr>
        </thead>
        <tbody>
            <!-- This table will be updated with JavaScript -->
        </tbody>
    </table>
</div>

    <ul>
        <!-- ... Your department links ... -->
    </ul>

    <div class="course-list">
        <!-- ... Your course list ... -->
    </div>

    <!-- Add your footer here -->
</body>
</html>


       
   <div class="container">
      <h2>Create Course Prerequisite</h2>
      <form method="POST" action="create_prerequisite.php">
         <div>
         <label for="courseID">Course ID:</label>
<select name="courseID" id="courseID">
   <?php foreach ($courses as $course): ?>
      <option value="<?php echo htmlspecialchars($course['CourseID']); ?>">
        <?php echo htmlspecialchars($course['CourseID']) . " - " . htmlspecialchars($course['CourseName']); ?>
      </option>
   <?php endforeach; ?>
</select>

         </div>
         <div>
            <label for="prCourseID">Prerequisite Course ID:</label>
            <input type="text" name="prCourseID" id="prCourseID" required>
         </div>
         <div>
    <label for="minGrade">Minimum Grade:</label>
    <select name="minGrade" id="minGrade" required>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
    </select>
</div>

         <div>
         <div>
         <div>
    <label for="dolu">DOLU:</label>
    <input type="text" name="dolu" id="dolu" value="<?php echo date('Y-m-d'); ?>" readonly>
</div>

</div>

         </div>
         <div>
            <button type="submit">Create Prerequisite</button>
         </div>
      </form>
   </div>
  


   <form method="POST" action="create_prerequisite.php">
    <!-- ... other form fields ... -->
    <!-- Include the department dropdown here -->
    <div>
        <label for="deptID">Select Department:</label>
        <select name="deptID" id="deptID">
            <option value="">All Departments</option>
            <?php
            // Fetch distinct DeptID values from your courses table
            $query = "SELECT DISTINCT DeptID FROM course";
            $result = mysqli_query($conn, $query);

            while ($row = mysqli_fetch_assoc($result)) {
                $deptID = $row['DeptID'];
                echo '<option value="' . $deptID . '">Department ' . $deptID . '</option>';
            }
            ?>
        </select>




        
    </div>
    <!-- ... other form fields ... -->
</form>

<script>
    // Initially clear the course list
    function clearCourseList() {
        var courseIDSelect = document.getElementById('courseID');
        courseIDSelect.innerHTML = ''; // Clear existing options
        var defaultOption = document.createElement('option');
        defaultOption.textContent = 'Select a course';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        courseIDSelect.appendChild(defaultOption);
    }

    function updateCourseList(deptID) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                // Update Course List Table
                var tableBody = document.getElementById('course-list-table').getElementsByTagName('tbody')[0];
                tableBody.innerHTML = '';
                for (var i = 0; i < response.length; i++) {
                    var course = response[i];
                    var newRow = tableBody.insertRow(tableBody.rows.length);
                    var cell1 = newRow.insertCell(0);
                    var cell2 = newRow.insertCell(1);
                    var cell3 = newRow.insertCell(2); // Add a new cell for prerequisites

                    cell1.innerHTML = course.CourseName;
                    cell2.innerHTML = course.CourseID;
                    cell3.innerHTML = course.PrerequisiteCourse; // Display prerequisites
                }

                // Update CourseID Select List
                var courseIDSelect = document.getElementById('courseID');
                courseIDSelect.innerHTML = ''; // Clear existing options
                response.forEach(function(course) {
                    var option = document.createElement('option');
                    option.value = course.CourseID;
                    option.textContent = course.CourseID + ' - ' + course.CourseName;
                    courseIDSelect.appendChild(option);
                });
            }
        };
        xhr.open('POST', 'fetch_courses1.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('dept_id=' + deptID);
    }

    document.getElementById('deptID').addEventListener('change', function () {
        var selectedDeptID = this.value;
        updateCourseList(selectedDeptID);
    });

    clearCourseList();
</script>






   <!-- Add your footer here -->
</body>
</html>
