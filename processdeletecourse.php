<?php
@include 'config1.php'; 

session_start();


if (!isset($_SESSION['UID'])) {
    echo "Please log in to continue.";
    exit;
}


if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    
    $checkEnrollmentQuery = "SELECT * FROM coursesection WHERE CourseID = ? AND (SemesterID = 20241 OR SemesterID = 20232)";
    $checkEnrollmentStmt = $conn->prepare($checkEnrollmentQuery);

    if ($checkEnrollmentStmt) {
        
        $checkEnrollmentStmt->bind_param("s", $course_id);

        
        $checkEnrollmentStmt->execute();

        
        $enrollmentResult = $checkEnrollmentStmt->get_result();

        if ($enrollmentResult->num_rows > 0) {
            echo '<p style="color: red; font-size: 23px;">Cannot delete the course because students are currenlty enrolled in this course.</p>';
            return '<p style="color: red; font-size: 23px;">Cannot delete the course because students are currenlty enrolled in this course.</p>';
} else {
      
            
            if ($deleteStmt = $conn->prepare("DELETE FROM course WHERE CourseID = ?")) {
                
                $deleteStmt->bind_param("s", $course_id);

                
                $deleteStmt->execute();

                
                if ($deleteStmt->affected_rows > 0) {
                    echo "Course successfully deleted.";
                } else {
                    echo "No course found with the given ID, or deletion failed.";
                    return "No course found with the given ID, or deletion failed.";
                }

                
                $deleteStmt->close();
            } else {
                echo "Error preparing delete statement: " . $conn->error;
                return "Error preparing delete statement: " . $conn->error;
            }
        }

        
        $checkEnrollmentStmt->close();
    } else {
        echo "Error preparing check enrollment statement: " . $conn->error;
        return "Error preparing check enrollment statement: " . $conn->error;
    }
} else {
    echo "Course ID not provided.";
}


?>
!DOCTYPE html>
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