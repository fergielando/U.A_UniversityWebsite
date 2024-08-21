<?php
@include 'config1.php';


if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    
    $prerequisiteQuery = "SELECT * FROM courseprerequisite WHERE CourseID = '$course_id'";
    $prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);

    if ($prerequisiteResult) {
        $prerequisites = [];
        while ($row = mysqli_fetch_assoc($prerequisiteResult)) {
            $prerequisites[] = $row;
        }
    } else {
        die("Error fetching course prerequisites.");
    }

    
    $courseDeptQuery = "SELECT * FROM course WHERE DeptID = (SELECT DeptID FROM course WHERE CourseID = '$course_id')";
    $courseDeptResult = mysqli_query($conn, $courseDeptQuery);

    if ($courseDeptResult) {
        $coursesInSameDept = [];
        while ($row = mysqli_fetch_assoc($courseDeptResult)) {
            $coursesInSameDept[] = $row;
        }
    } else {
        die("Error fetching courses in the same department.");
    }
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Course Prerequisites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }

        label {
            display: block;
            margin-top: 10px;
            color: #666;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button[type="button"] {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
        }

        button[type="button"]:hover {
            background-color: #c0392b;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            margin-top: 20px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
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
<h2>Update Course Prerequisites</h2>
    <form method="post" action="update_prerequisites_process.php">
        <!-- Display course prerequisites data in a form for editing -->
        <?php foreach ($prerequisites as $index => $prerequisite) : ?>
            <div class="prerequisite">
                <label for="prerequisite_<?php echo $index; ?>">Prerequisite Course ID:</label>
                <input type="text" name="prerequisite_ids[]" id="prerequisite_<?php echo $index; ?>" value="<?php echo $prerequisite['PRcourseID']; ?>"><br>
                <label for="min_grade_<?php echo $index; ?>">Min Grade:</label>
                <select name="min_grades[]">
    <option value="A" <?php if ($prerequisite['MinGrade'] === 'A') echo 'selected'; ?>>A</option>
    <option value="B" <?php if ($prerequisite['MinGrade'] === 'B') echo 'selected'; ?>>B</option>
    <option value="C" <?php if ($prerequisite['MinGrade'] === 'C') echo 'selected'; ?>>C</option>
    <option value="D" <?php if ($prerequisite['MinGrade'] === 'D') echo 'selected'; ?>>D</option>
    <option value="F" <?php if ($prerequisite['MinGrade'] === 'F') echo 'selected'; ?>>F</option>
</select><br>

                <input type="hidden" name="prerequisite_ids_original[]" value="<?php echo $prerequisite['PRcourseID']; ?>">
                <a href="delete_prerequisite.php?course_id=<?php echo $course_id; ?>&prerequisite_id=<?php echo $prerequisite['PRcourseID']; ?>">Delete</a>
            </div>
        <?php endforeach; ?>

        <!-- Hidden input for course ID -->
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

        <input type="submit" name="update_prerequisites" value="Update Prerequisites">
    </form>

    <!-- Display courses from the same department -->
    <h2>Courses in the Same Department</h2>
    <table>
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Credits</th>
                <!-- Add more columns as needed -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coursesInSameDept as $courseDept) : ?>
                <tr>
                    <td><?php echo $courseDept['CourseID']; ?></td>
                    <td><?php echo $courseDept['CourseName']; ?></td>
                    <td><?php echo $courseDept['Credits']; ?></td>
                    <!-- Add more columns as needed -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    </script>
</body>
</html>
