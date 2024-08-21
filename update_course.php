<?php
@include 'config1.php';


if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    
    $query = "SELECT * FROM course WHERE CourseID = '$course_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $course = mysqli_fetch_assoc($result);
    } else {
        die("Course not found.");
    }
} else {
    die("Invalid request.");
}


$deptQuery = "SELECT DeptID, DeptName FROM dept";
$deptResult = mysqli_query($conn, $deptQuery);

$departments = [];
while ($row = mysqli_fetch_assoc($deptResult)) {
    $departments[] = $row;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = isset($_POST['course_name']) ? $_POST['course_name'] : '';
    $dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : '';
    $credits = isset($_POST['credits']) ? $_POST['credits'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $course_type = isset($_POST['course_type']) ? $_POST['course_type'] : '';
    
    
    $update_query = "UPDATE course SET CourseName='$course_name', DeptID='$dept_id', Credits='$credits', Description='$description', CourseType='$course_type' WHERE CourseID='$course_id'";

    if (mysqli_query($conn, $update_query)) {
        echo "<p>Course updated successfully. You will be redirected in 5 seconds...</p>";
        
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'course_catalog1.php'; 
                }, 5000);
              </script>";
    } else {
        echo "Error updating course: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Course</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            background-color: #007BFF;
            color: #fff;
            padding: 10px;
            text-align: center;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        select {
            height: 40px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            color: green;
            font-size: 18px;
            text-align: center;
            margin-top: 20px;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h2>Update Course</h2>
    <form method="post" action="">
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" id="course_name" value="<?php echo $course['CourseName']; ?>"><br>
        <label for="dept_id">Department ID:</label>
        <select name="dept_id" id="dept_id">
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['DeptID']; ?>" <?php echo ($course['DeptID'] == $dept['DeptID']) ? 'selected' : ''; ?>>
                    <?php echo $dept['DeptName']; ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <label for="credits">Credits:</label>
        <input type="number" name="credits" id="credits" value="<?php echo $course['Credits']; ?>"><br>
        <label for="description">Description:</label>
        <textarea name="description" id="description"><?php echo $course['Description']; ?></textarea><br>
        <label for="course_type">Course Type:</label>
        <select name="course_type" id="course_type">
            <option value="Undergraduate" <?php echo ($course['CourseType'] === 'Undergraduate') ? 'selected' : ''; ?>>Undergraduate</option>
            <option value="Graduate" <?php echo ($course['CourseType'] === 'Graduate') ? 'selected' : ''; ?>>Graduate</option>
        </select><br>
        <input type="submit" value="Update Course">
    </form>
</body>
</html>

