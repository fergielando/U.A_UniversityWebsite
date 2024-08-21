<?php
@include 'config1.php'; 

if (isset($_POST['facultyId'], $_POST['deptId'])) {
    $facultyId = mysqli_real_escape_string($conn, $_POST['facultyId']);
    $deptId = mysqli_real_escape_string($conn, $_POST['deptId']);

    
    mysqli_begin_transaction($conn);

    function redirectWithMessage($message, $redirectTo, $delaySeconds = 5) {
        echo "<div style='color: red; font-weight: bold;'>" . htmlspecialchars($message) . "</div>";
        header("Refresh: $delaySeconds; url=$redirectTo");
        exit;
    }
    
    if (isset($_POST['facultyId'], $_POST['deptId'])) {
        $facultyId = mysqli_real_escape_string($conn, $_POST['facultyId']);
        $deptId = mysqli_real_escape_string($conn, $_POST['deptId']);
    
        
        $checkQuery = "SELECT 1 FROM facultydept WHERE FacultyID = ? AND DeptID = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, 'ss', $facultyId, $deptId);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) === 0) {
            mysqli_stmt_close($checkStmt);
            redirectWithMessage("Error: The faculty member is not part of the department.", "editdepartment.php");
        }
        mysqli_stmt_close($checkStmt);
    
        
        mysqli_begin_transaction($conn);
    }


    try {
        
        $typeQuery = "SELECT FacultyType FROM faculty WHERE FacultyID = ?";
        $typeStmt = mysqli_prepare($conn, $typeQuery);
        mysqli_stmt_bind_param($typeStmt, 's', $facultyId);
        mysqli_stmt_execute($typeStmt);
        $typeResult = mysqli_stmt_get_result($typeStmt);
        $typeRow = mysqli_fetch_assoc($typeResult);

        if ($typeRow) {
            $facultyType = $typeRow['FacultyType'];


            $deleteQuery = "DELETE FROM facultydept WHERE FacultyID = ? AND DeptID = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, 'ss', $facultyId, $deptId);
            mysqli_stmt_execute($deleteStmt);

            
            $countQuery = "SELECT COUNT(*) AS DeptCount FROM facultydept WHERE FacultyID = ?";
            $countStmt = mysqli_prepare($conn, $countQuery);
            mysqli_stmt_bind_param($countStmt, 's', $facultyId);
            mysqli_stmt_execute($countStmt);
            $countResult = mysqli_stmt_get_result($countStmt);
            $countRow = mysqli_fetch_assoc($countResult);
            $deptCount = $countRow['DeptCount'];

            if ($deptCount == 0) {
             
                $nullQuery = "UPDATE facultydept SET DeptID = NULL, PercentTime = 0 WHERE FacultyID = ?";
                $nullStmt = mysqli_prepare($conn, $nullQuery);
                mysqli_stmt_bind_param($nullStmt, 's', $facultyId);
                mysqli_stmt_execute($nullStmt);
                mysqli_stmt_close($nullStmt);
            } elseif ($facultyType === 'Full-time' && $deptCount == 1) {
                
                $updateQuery = "UPDATE facultydept SET PercentTime = 100 WHERE FacultyID = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, 's', $facultyId);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }

            echo "Department removed from faculty successfully.";
        } else {
            echo "Faculty member not found.";
        }

   
    mysqli_commit($conn);
    echo "Department removed from faculty successfully.";
    
    header("Refresh: 5; editdepartment.php");
} catch (Exception $e) {
  
    mysqli_rollback($conn);
    echo "Error: " . $e->getMessage();
}

    mysqli_stmt_close($typeStmt);
    mysqli_stmt_close($countStmt);
    mysqli_stmt_close($deleteStmt);
} else {
    echo "Faculty ID and Department ID are required.";
}

mysqli_close($conn);
?>
