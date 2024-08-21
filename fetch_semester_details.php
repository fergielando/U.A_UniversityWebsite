<?php
@include 'config1.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $selectedSemesterID = $_GET['id'];

    $fetchDetailsQuery = "SELECT * FROM semester WHERE SemesterID = '$selectedSemesterID'";
    $result = mysqli_query($conn, $fetchDetailsQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $semesterDetails = mysqli_fetch_assoc($result);
        echo json_encode($semesterDetails); // Return details as JSON
    } else {
        echo json_encode(array()); // Return empty JSON object if no details found
    }
}
mysqli_close($conn);
?>
