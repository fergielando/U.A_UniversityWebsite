<?php
session_start();
@include 'config1.php'; // Include your database configuration file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $minor_id = $_POST['minor_id'];
    $dept_id = $_POST['dept_id'];
    $minor_name = $_POST['minor_name'];

    // Validate if fields are not empty
    if (!empty($minor_id) && !empty($dept_id) && !empty($minor_name)) {
        // Check if MinorID already exists
        $check_query = "SELECT * FROM minor WHERE MinorID = '$minor_id'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows == 0) {
            // Insert minor into minor table
            $insert_query = "INSERT INTO minor (MinorID, DeptID, MinorName) VALUES ('$minor_id', '$dept_id', '$minor_name')";
            
            if ($conn->query($insert_query) === TRUE) {
                echo "New minor created successfully";
            } else {
                echo "Error: " . $insert_query . "<br>" . $conn->error;
            }
        } else {
            echo "MinorID already exists";
        }
    } else {
        echo "All fields are required";
    }
} else {
    echo "Invalid request";
}
?>
