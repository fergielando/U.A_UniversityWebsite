<?php
@include 'config1.php'; 

if (isset($_POST['facultyId'], $_POST['deptId'])) {
    $facultyId = mysqli_real_escape_string($conn, $_POST['facultyId']);
    $deptId = mysqli_real_escape_string($conn, $_POST['deptId']);

    
    mysqli_begin_transaction($conn);

    try {
       
        $typeQuery = "SELECT FacultyType FROM faculty WHERE FacultyID = ?";
        $typeStmt = mysqli_prepare($conn, $typeQuery);
        mysqli_stmt_bind_param($typeStmt, 's', $facultyId);
        mysqli_stmt_execute($typeStmt);
        $typeResult = mysqli_stmt_get_result($typeStmt);
        $typeRow = mysqli_fetch_assoc($typeResult);

        if ($typeRow) {
            $facultyType = $typeRow['FacultyType'];

            
            $countQuery = "SELECT COUNT(*) AS DeptCount FROM facultydept WHERE FacultyID = ?";
            $countStmt = mysqli_prepare($conn, $countQuery);
            mysqli_stmt_bind_param($countStmt, 's', $facultyId);
            mysqli_stmt_execute($countStmt);
            $countResult = mysqli_stmt_get_result($countStmt);
            $countRow = mysqli_fetch_assoc($countResult);
            $deptCount = $countRow['DeptCount'];

            if ($facultyType === 'Full-time' && $deptCount < 2) {
                
                if ($deptCount == 1) {
                    $updateQuery = "UPDATE facultydept SET PercentTime = 50 WHERE FacultyID = ?";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, 's', $facultyId);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }

             
                $newPercentTime = ($deptCount == 0) ? 100 : 50;
                $insertQuery = "INSERT INTO facultydept (FacultyID, DeptID, PercentTime) VALUES (?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($insertStmt, 'ssi', $facultyId, $deptId, $newPercentTime);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);

                echo "Department added to faculty successfully.";
            } elseif ($facultyType === 'Part-time' && $deptCount == 0) {
               
                $insertQuery = "INSERT INTO facultydept (FacultyID, DeptID, PercentTime) VALUES (?, ?, 100)";
                $insertStmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($insertStmt, 'ss', $facultyId, $deptId);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);

                echo "Department added to faculty successfully.";
            } else {
                echo "Cannot add department due to faculty type and existing department assignments.";
            }
        } else {
            echo "Faculty member not found.";
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
       
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }

    mysqli_stmt_close($typeStmt);
    mysqli_stmt_close($countStmt);
} else {
    echo "Faculty ID and Department ID are required.";
}

mysqli_close($conn);
?>
