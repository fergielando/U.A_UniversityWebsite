<?php
session_start();
@include 'config1.php'; // Include your database configuration file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $major_id = $_POST['major_id'];
    $dept_id = $_POST['dept_id'];
    $major_name = $_POST['major_name'];

    // Validate if fields are not empty
    if (!empty($major_id) && !empty($dept_id) && !empty($major_name)) {
        // Check if MajorID already exists
        $check_query = "SELECT * FROM major WHERE MajorID = '$major_id'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows == 0) {
            // Insert major into major table
            $insert_query = "INSERT INTO major (MajorID, DeptID, MajorName) VALUES ('$major_id', '$dept_id', '$major_name')";
            
            if ($conn->query($insert_query) === TRUE) {
                echo "New major created successfully";
            } else {
                echo "Error: " . $insert_query . "<br>" . $conn->error;
            }
        } else {
            echo "MajorID already exists";
        }
    } else {
        echo "All fields are required";
    }
} else {
    echo "Invalid request";
}
?>
