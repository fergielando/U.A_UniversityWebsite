<?php
@include 'config1.php'; // Include your database configuration file

session_start();

// Check for user's session UID
if (!isset($_SESSION['UID'])) {
    echo "Please log in to delete courses.";
    exit;
}

$uid = $_SESSION['UID'];

// Query to get all courses
$allCoursesQuery = "SELECT CourseID, CourseName FROM course";
$allCoursesResult = mysqli_query($conn, $allCoursesQuery);

if (!$allCoursesResult) {
    echo "Error fetching courses: " . mysqli_error($conn);
    exit;
}

$allCourses = [];
while ($courseRow = mysqli_fetch_assoc($allCoursesResult)) {
    $allCourses[] = $courseRow;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course</title>
    
   <style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

h1 {
    color: #333;
    text-align: center;
}

form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 300px;
    margin: 20px auto;
}

label {
    display: block;
    margin-bottom: 10px;
    color: #666;
}

select, input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

input[type="submit"] {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}
</style>
</head>
<body>
    <h1>Delete Course</h1>
    <form action="processdeletecourse.php" method="POST">
        <label for="course_id">Select Course:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($allCourses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['CourseID']); ?>">
                    <?php echo htmlspecialchars($course['CourseID'] . ' - ' . $course['CourseName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Delete Course">
    </form>
</body>
</html>
