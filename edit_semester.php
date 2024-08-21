<!DOCTYPE html>
<html>
<head>
    <title>Edit Semester</title>
</head>
<body>
    <h2>Edit Semester</h2>
    <form method="post" action="edit_semester.php">
        Semester ID:
        <select name="SemesterID" required onchange="fetchSemesterDetails(this.value)">
            <option value="">Select Semester ID</option>
            <?php
            @include 'config1.php';
            session_start();

            // Fetch SemesterIDs from the database
            $fetchQuery = "SELECT SemesterID, SemesterName FROM semester WHERE SemesterID != 0";
            $result = mysqli_query($conn, $fetchQuery);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['SemesterID']}'>{$row['SemesterID']} - {$row['SemesterName']}</option>";
            }
            ?>
        </select><br><br>
        Semester Name: <input type="text" name="SemesterName" required><br><br>
        Semester Year: <input type="text" name="SemesterYear" required><br><br>
        Start Time: <input type="date" name="StartTime" required><br><br>
        End Time: <input type="date" name="EndTime" required><br><br>
        <input type="submit" name="submit" value="Update Semester">
    </form>

    <script>
        function fetchSemesterDetails(selectedID) {
            // AJAX call to fetch details when a SemesterID is selected
            if (selectedID !== '') {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        var semesterDetails = JSON.parse(this.responseText);
                        document.getElementsByName('SemesterName')[0].value = semesterDetails.SemesterName;
                        document.getElementsByName('SemesterYear')[0].value = semesterDetails.SemesterYear;
                        document.getElementsByName('StartTime')[0].value = semesterDetails.StartTime;
                        document.getElementsByName('EndTime')[0].value = semesterDetails.EndTime;
                    }
                };
                xhttp.open("GET", "fetch_semester_details.php?id=" + selectedID, true);
                xhttp.send();
            }
        }
    </script>
</body>
</html>

<?php
@include 'config1.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $SemesterID = $_POST['SemesterID'];
    $SemesterName = $_POST['SemesterName'];
    $SemesterYear = $_POST['SemesterYear'];
    $StartTime = $_POST['StartTime'];
    $EndTime = $_POST['EndTime'];


    // Update the semester details in the database
    $updateQuery = "UPDATE semester 
                    SET SemesterName = '$SemesterName', 
                        SemesterYear = '$SemesterYear', 
                        StartTime = '$StartTime', 
                        EndTime = '$EndTime' 
                    WHERE SemesterID = '$SemesterID'";
					
	 $result = mysqli_query($conn, $updateQuery);

    if ($result) {
        echo "<script>alert('Semester details updated successfully!');</script>";
    } else {
        echo "Error updating semester details: " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
?>