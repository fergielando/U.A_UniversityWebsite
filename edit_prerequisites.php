<?php
@include 'config1.php';

if (isset($_GET['major_id'])) {
    $majorID = $_GET['major_id'];

    // Retrieve major information
    $majorQuery = "SELECT * FROM major WHERE MajorID = $majorID";
    $majorResult = mysqli_query($conn, $majorQuery);
    $major = mysqli_fetch_assoc($majorResult);

    // Retrieve major prerequisites including MajorID and PRmajorID
    $prerequisiteQuery = "SELECT MajorID, PRmajorID, MinGrade, DOLU FROM majorprerequisite WHERE MajorID = $majorID";
    $prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
    $majorPrerequisites = mysqli_fetch_all($prerequisiteResult, MYSQLI_ASSOC);
} else {
    // Handle the case where no major ID is provided in the URL
    echo "No major selected for editing prerequisites.";
    exit();
}

// Inside the POST handling section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if (isset($_POST['add_prerequisite'])) {
        $newPrerequisite = $_POST['new_prerequisite'];
        $newPrMajorID = mysqli_real_escape_string($conn, $newPrerequisite['PRmajorID']);
        
        // Check if the PRmajorID already exists for the selected major
        $checkQuery = "SELECT * FROM majorprerequisite WHERE MajorID = ? AND PRmajorID = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "is", $majorID, $newPrMajorID);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            echo "PR Major ID already exists for this major.";
        } else {
            $minGrade = mysqli_real_escape_string($conn, $newPrerequisite['MinGrade']);
            $dolu = mysqli_real_escape_string($conn, $newPrerequisite['DOLU']);

            // Insert the new prerequisite
            $insertQuery = "INSERT INTO majorprerequisite (MajorID, PRmajorID, MinGrade, DOLU) VALUES ('$majorID', 'BU6350', 'C', '2023-12-14')";
$insertResult = mysqli_query($conn, $insertQuery);

if ($insertResult) {
    echo "Prerequisite added successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}
        }
        mysqli_stmt_close($checkStmt);
    } else {
    // Handle form submission to update or delete prerequisites
    $updatedPrerequisites = $_POST['prerequisites'];

    foreach ($updatedPrerequisites as $prerequisite) {
        $prMajorID = mysqli_real_escape_string($conn, $prerequisite['PRmajorID']);
        $minGrade = mysqli_real_escape_string($conn, $prerequisite['MinGrade']);
        $dolu = mysqli_real_escape_string($conn, $prerequisite['DOLU']);
        $action = $prerequisite['action'];

        if ($action === 'update') {
            // Update the existing record
            $updateQuery = "UPDATE majorprerequisite SET MinGrade = ?, DOLU = ? WHERE MajorID = ? AND PRmajorID = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "ssis", $minGrade, $dolu, $majorID, $prMajorID);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        } elseif ($action === 'delete') {
            // Delete the existing record
            $deleteQuery = "DELETE FROM majorprerequisite WHERE MajorID = ? AND PRmajorID = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "is", $majorID, $prMajorID);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);
        }
    }
	    echo "Prerequisites updated successfully.";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (your existing head content) ... -->
</head>
<body>
    <div class="header">
        <h1>Edit Major Prerequisites</h1>
        <a href="majors1.php" class="back-button">Back to Majors</a>
    </div>

    <div class="major-container">
        <h2>Edit Prerequisites for <?php echo $major['MajorName']; ?></h2>
        <form method="POST">
            <input type="hidden" name="major_id" value="<?php echo $majorID; ?>">
            <table>
                <tr>
                    <th>Action</th>
                    <th>PR Major ID</th>
                    <th>Minimum Grade</th>
                    <th>DOLU</th>
                </tr>
                <?php foreach ($majorPrerequisites as $key => $prerequisite) : ?>
                    <tr>
                        <td>
                            <select name="prerequisites[<?php echo $key; ?>][action]">
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="prerequisites[<?php echo $key; ?>][PRmajorID]" value="<?php echo $prerequisite['PRmajorID']; ?>">
                        </td>
                        <td>
                            <input type="text" name="prerequisites[<?php echo $key; ?>][MinGrade]" value="<?php echo $prerequisite['MinGrade']; ?>">
                        </td>
                        <td>
                            <input type="text" name="prerequisites[<?php echo $key; ?>][DOLU]" value="<?php echo $prerequisite['DOLU']; ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit">Save Changes</button>
        </form>
    </div>
    
    <div class="major-container">
    <h2>Add Prerequisites</h2>
    <form method="POST">
        <input type="hidden" name="major_id" value="<?php echo $majorID; ?>">
        <table>
            <tr>
                <th>PR Major ID</th>
                <th>Minimum Grade</th>
                <th>DOLU</th>
            </tr>
            <tr>
                <td>
                    <select name="new_prerequisite[PRmajorID]">
                        <?php
                        $courseQuery = "SELECT CourseID, CourseName FROM course";
                        $courseResult = mysqli_query($conn, $courseQuery);
                        while ($row = mysqli_fetch_assoc($courseResult)) {
                            echo '<option value="'. $row['CourseID'] .'">' . $row['CourseID'] . ' - ' . $row['CourseName'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <input type="text" name="new_prerequisite[MinGrade]">
                </td>
                <td>
                    <input type="text" name="new_prerequisite[DOLU]" value="<?php echo date('Y-m-d'); ?>" readonly>
                </td>
            </tr>
        </table>
        <button type="submit" name="add_prerequisite">Add Prerequisite</button>
    </form>
</div>

</body>
</html>