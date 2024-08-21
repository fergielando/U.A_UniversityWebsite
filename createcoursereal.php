<?php

@include 'config1.php';

session_start();


if (!isset($_SESSION['UID'])) {
    echo "Please log in to assign courses.";
    exit;
}



$uid = $_SESSION['UID'];
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
            display: flex;
            justify-content: space-between;
        }

        .left-section {
            flex: 1;
            padding: 20px;
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
        }

        form {
            padding: 20px;
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
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }


        
        tr:hover {
            background-color: #e9e9e9;
        }
    </style>
</head>
<body>
    <div class="left-section">
        <h1>Create Course</h1>
        <form action="processcreatecoursereal.php" method="POST">
            <!-- Dropdown list for department ID -->
            <label for="dept_id">Select Department:</label>
<select id="dept_id" name="dept_id" required>
    <option value="">Select a Department</option>
    <?php
    
    $deptQuery = "SELECT DeptID, DeptName FROM dept";
    $deptResult = mysqli_query($conn, $deptQuery);

    if ($deptResult && mysqli_num_rows($deptResult) > 0) {
        while ($deptRow = mysqli_fetch_assoc($deptResult)) {
            $deptID = $deptRow['DeptID'];
            $deptName = $deptRow['DeptName'];
            echo "<option value='$deptID'>$deptID - $deptName</option>";
        }
    }
    ?>
</select><br>

  


  
            

            <!-- Input field for course ID -->
            <label for="course_id">Course ID:</label>
            <input type="text" id="course_id" name="course_id" required><br>

            <!-- Input field for course name -->
            <label for="course_name">Course Name:</label>
            <input type="text" id="course_name" name="course_name" required><br>

            <!-- Input field for course credits -->
            <label for="credits">Credits:</label>
            <input type="text" id="credits" name="credits" required><br>

            <!-- Input field for course description -->
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br>

            <label for="course_type">Course Type:</label>
<select id="course_type" name="course_type" required>
    <option value="Undergraduate">Undergraduate</option>
    <option value="Graduate">Graduate</option>
</select><br>
<!-- Input fields for course prerequisites -->
<label for="prerequisites">Prerequisite Course(s):</label>
<div id="prerequisites-container">
    <div class="prerequisite-input">
        <input type="text" name="prerequisites[]" placeholder="Prerequisite Course ID">
        <input type="text" name="min_grade[]" placeholder="Minimum Grade">
        <button type="button" class="add-prerequisite">Add Prerequisite</button>
    </div>
</div>

<div>
    <label for="dolu">DOLU:</label>
    <input type="text" name="dolu" id="dolu" value="<?php echo date('Y-m-d'); ?>" readonly>
</div>

            <!-- Submit button -->
            <input type="submit" value="Create Course">
        </form>
    </div>

    <div class="right-section">
    <h1>Courses in Selected Department</h1>
    <table>
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Course Type</th> <!-- Added for Course Type -->
                <th>Prerequisite Course</th> <!-- Added for Course Prerequisite -->
            </tr>
        </thead>
        <tbody id="course-list">
            <!-- Courses for the selected department will be displayed here -->
        </tbody>
    </table>
</div>


    <script>
        
        function updateCourseList() {
            var deptSelect = document.getElementById("dept_id");
            var coursesSelect = document.getElementById("courses");
            var selectedDept = deptSelect.value;
            var courseList = document.getElementById("course-list");

            
            courseList.innerHTML = '';

            if (selectedDept !== "") {
                
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_courses.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var courses = JSON.parse(xhr.responseText);

                       
if (courses.length > 0) {
    for (var i = 0; i < courses.length; i++) {
        var course = courses[i];
        var row = document.createElement("tr");
        row.innerHTML = `
            <td>${course.CourseID}</td>
            <td>${course.CourseName}</td>
            <td>${course.Credits || 'N/A'}</td>
            <td>${course.CourseType || 'N/A'}</td> <!-- Display Course Type -->
            <td>${course.PrerequisiteCourse || 'N/A'}</td> <!-- Display Course Prerequisite -->
        `;
        courseList.appendChild(row);
    }
}

                        }
                    }
                };
                xhr.send("dept_id=" + selectedDept);
            }
        

        
        document.getElementById("dept_id").addEventListener("change", updateCourseList);


               
document.addEventListener("DOMContentLoaded", function () {
    const prerequisitesContainer = document.getElementById("prerequisites-container");
    const addPrerequisiteButton = document.querySelector(".add-prerequisite");

    addPrerequisiteButton.addEventListener("click", function () {
        const prerequisiteInput = document.createElement("div");
        prerequisiteInput.classList.add("prerequisite-input");
        prerequisiteInput.innerHTML = `
            <input type="text" name="prerequisites[]" placeholder="Prerequisite Course ID">
            <input type="text" name="min_grade[]" placeholder="Minimum Grade">
            <button type="button" class="remove-prerequisite">Remove</button>
        `;
        prerequisitesContainer.appendChild(prerequisiteInput);
        
        
        const removePrerequisiteButton = prerequisiteInput.querySelector(".remove-prerequisite");
        removePrerequisiteButton.addEventListener("click", function () {
            prerequisitesContainer.removeChild(prerequisiteInput);
        });
    });
});

        
        updateCourseList();



 
    </script>
</body>
</html>
