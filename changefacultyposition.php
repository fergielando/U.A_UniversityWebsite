<?php
@include 'config1.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facultyId = $_POST['facultyId'];
    $newPosition = $_POST['newPosition'];

   
    $query = $conn->prepare("SELECT FacultyID FROM faculty WHERE FacultyID = ?");
    $query->bind_param("s", $facultyId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        
        $updateQuery = $conn->prepare("UPDATE faculty SET Position = ? WHERE FacultyID = ?");
        $updateQuery->bind_param("ss", $newPosition, $facultyId);

        if ($updateQuery->execute()) {
            echo "Faculty position updated successfully.";
        } else {
            echo "Error updating faculty position: " . $conn->error;
        }

        $updateQuery->close();
    } else {
        echo "Faculty with the provided ID not found.";
    }

    $query->close();
}
?>
