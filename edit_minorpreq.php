<?php
@include 'config1.php';

if (isset($_GET['minor_id'])) {
    $minorID = $_GET['minor_id'];

    // Retrieve minor information
    $minorQuery = "SELECT * FROM minor WHERE MinorID = $minorID";
    $minorResult = mysqli_query($conn, $minorQuery);
    $minor = mysqli_fetch_assoc($minorResult);

    // Retrieve minor prerequisites including MinorID and PRminorID
    $prerequisiteQuery = "SELECT MinorID, PRminorID, MinGrade, DOLU FROM minorprerequisite WHERE MinorID = $minorID";
    $prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
    $minorPrerequisites = mysqli_fetch_all($prerequisiteResult, MYSQLI_ASSOC);
} else {
    // Handle the case where no minor ID is provided in the URL
    echo "No minor selected for editing prerequisites.";
    exit();
}

// Inside the POST handling section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if (isset($_POST['add_prerequisite'])) {
    $newPrerequisite = $_POST['new_prerequisite'];
    $newPrMinorID = $newPrerequisite['PRminorID'];
    $minGrade = mysqli_real_escape_string($conn, $newPrerequisite['MinGrade']);
    $dolu = mysqli_real_escape_string($conn, $newPrerequisite['DOLU']);

    // Check if the PRminorID already exists for the selected minor
    $checkQuery = "SELECT * FROM minorprerequisite WHERE MinorID = ? AND PRminorID = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "is", $minorID, $newPrMinorID);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        echo "PR Minor ID already exists for this minor.";
    } else {
        $insertQuery = "INSERT INTO minorprerequisite (MinorID, PRminorID, MinGrade, DOLU) VALUES (?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "isss", $minorID, $newPrMinorID, $minGrade, $dolu);
        mysqli_stmt_execute($insertStmt);

        if (mysqli_stmt_affected_rows($insertStmt) > 0) {
            echo "Prerequisite added successfully.";
        } else {
            echo "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($insertStmt);
    }
    mysqli_stmt_close($checkStmt);
} else {
    // Handle form submission to update or delete prerequisites
    $updatedPrerequisites = $_POST['prerequisites'];

    foreach ($updatedPrerequisites as $prerequisite) {
        $prMinorID = mysqli_real_escape_string($conn, $prerequisite['PRminorID']);
        $minGrade = mysqli_real_escape_string($conn, $prerequisite['MinGrade']);
        $dolu = mysqli_real_escape_string($conn, $prerequisite['DOLU']);
        $action = $prerequisite['action'];

        if ($action === 'update') {
            // Update the existing record
            $updateQuery = "UPDATE minorprerequisite SET MinGrade = ?, DOLU = ? WHERE MinorID = ? AND PRminorID = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "ssis", $minGrade, $dolu, $minorID, $prMinorID);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        } elseif ($action === 'delete') {
            // Delete the existing record
            $deleteQuery = "DELETE FROM minorprerequisite WHERE MinorID = ? AND PRminorID = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "is", $minorID, $prMinorID);
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
        <h1>Edit Minor Prerequisites</h1>
        <a href="minors1.php" class="back-button">Back to Minors</a>
    </div>

    <div class="major-container">
        <h2>Edit Prerequisites for <?php echo $minor['MinorName']; ?></h2>
        <form method="POST">
            <input type="hidden" name="minor_id" value="<?php echo $minorID; ?>">
            <table>
                <tr>
                    <th>Action</th>
                    <th>PR Minor ID</th>
                    <th>Minimum Grade</th>
                    <th>DOLU</th>
                </tr>
                <?php foreach ($minorPrerequisites as $key => $prerequisite) : ?>
                    <tr>
                        <td>
                            <select name="prerequisites[<?php echo $key; ?>][action]">
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="prerequisites[<?php echo $key; ?>][PRminorID]" value="<?php echo $prerequisite['PRminorID']; ?>">
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
        <input type="hidden" name="minor_id" value="<?php echo $minorID; ?>">
        <table>
            <tr>
                <th>PR Minor ID</th>
                <th>Minimum Grade</th>
                <th>DOLU</th>
            </tr>
            <tr>
                <td>
                    <select name="new_prerequisite[PRminorID]">
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