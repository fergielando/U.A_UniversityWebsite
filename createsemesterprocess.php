<?php
@include 'config1.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $SemesterID = $_POST['SemesterID'];
    $SemesterName = $_POST['SemesterName'];
    $SemesterYear = $_POST['SemesterYear'];
    $StartTime = $_POST['StartTime'];
    $EndTime = $_POST['EndTime'];

    // Check if SemesterID exists
    $checkQuery = "SELECT * FROM semester WHERE SemesterID = '$SemesterID'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "SemesterID already exists. Please choose a different ID.";
    } else {
        // Insert into the database
        $insertQuery = "INSERT INTO semester (SemesterID, SemesterName, SemesterYear, StartTime, EndTime) 
                        VALUES ('$SemesterID', '$SemesterName', '$SemesterYear', '$StartTime', '$EndTime')";
        
        if (mysqli_query($conn, $insertQuery)) {
            echo "Semester created successfully!";
        } else {
            echo "Error: " . $insertQuery . "<br>" . mysqli_error($conn);
        }
    }
    mysqli_close($conn);
}
?>
