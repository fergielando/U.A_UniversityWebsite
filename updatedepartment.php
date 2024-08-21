<?php
@include 'config1.php'; 

$deptId = $_POST['deptId'] ?? '';
$deptName = $_POST['deptName'] ?? '';
$chairId = $_POST['chairId'] ?? '';
$deptManagerId = $_POST['deptManagerId'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNum = $_POST['phoneNum'] ?? '';
$roomID = $_POST['roomID'] ?? '';

function displayMessageWithRefresh($message) {
    echo '<div style="color: red; font-size: 18px;">' . $message . '</div>';
    echo '<script>
            setTimeout(function() {
                window.location.href = "Departments_page1.php";
            }, 5000);
          </script>';
}


$updateQuery = "UPDATE dept SET ";


function isChairOrManager($conn, $facultyId, $excludeDeptId) {
    $query = "SELECT 1 FROM faculty 
              WHERE FacultyID = '".mysqli_real_escape_string($conn, $facultyId)."' 
              AND (Position = 'Chair' OR Position = 'Manager')
              AND FacultyID NOT IN (SELECT ChairID FROM dept WHERE DeptID = '".mysqli_real_escape_string($conn, $excludeDeptId)."')
              AND FacultyID NOT IN (SELECT DeptManager FROM dept WHERE DeptID = '".mysqli_real_escape_string($conn, $excludeDeptId)."')";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}


function isDeptNameExists($conn, $deptName, $excludeDeptId) {
    $query = "SELECT 1 FROM dept 
              WHERE DeptName = '".mysqli_real_escape_string($conn, $deptName)."' 
              AND DeptID != '".mysqli_real_escape_string($conn, $excludeDeptId)."'";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}


function updateFacultyPosition($conn, $facultyId, $newPosition) {
    if ($facultyId != '') {
        $updateQuery = "UPDATE faculty SET Position = '".mysqli_real_escape_string($conn, $newPosition)."' 
                        WHERE FacultyID = '".mysqli_real_escape_string($conn, $facultyId)."'";
        mysqli_query($conn, $updateQuery);
    }
}

if ($deptId != '' && $deptName != '') {
    
    $currentDeptQuery = "SELECT PhoneNum FROM dept WHERE DeptID = '".mysqli_real_escape_string($conn, $deptId)."'";
    $currentDeptResult = mysqli_query($conn, $currentDeptQuery);
    $currentDeptRow = mysqli_fetch_assoc($currentDeptResult);

    if ($currentDeptRow) {
        $currentPhoneNum = $currentDeptRow['PhoneNum'];

        
        if ($phoneNum != '' && $phoneNum != $currentPhoneNum) {
            
            $phoneNumInUse = isValueInUse($conn, 'PhoneNum', $phoneNum, $deptId);
            if ($phoneNumInUse) {
                displayMessageWithRefresh("Error: The phone number is already in use by another department.");
                exit;
            }
        }
    } else {
        echo "Error: Failed to fetch current department details.";
        exit;
    }

    if ($deptId != '' && $deptName != '') {
        
        $currentDeptQuery = "SELECT PhoneNum FROM dept WHERE DeptID = '".mysqli_real_escape_string($conn, $deptId)."'";
        $currentDeptResult = mysqli_query($conn, $currentDeptQuery);
        $currentDeptRow = mysqli_fetch_assoc($currentDeptResult);
    
        if ($currentDeptRow) {
            $currentPhoneNum = $currentDeptRow['PhoneNum'];
    
            if ($phoneNum != '' && $phoneNum != $currentPhoneNum) {
          
                $phoneNumInUse = isValueInUse($conn, 'PhoneNum', $phoneNum, $deptId);
                if ($phoneNumInUse) {
                    displayMessageWithRefresh("Error: The phone number is already in use by another department.");
                    exit;
                }
            }
        } else {
            echo "Error: Failed to fetch current department details.";
            exit;
        }
    
      
        if (($chairId != '' && $deptManagerId != '' && $chairId == $deptManagerId) || ($chairId != '' && isChairOrManager($conn, $chairId, $deptId)) || ($deptManagerId != '' && isChairOrManager($conn, $deptManagerId, $deptId))) {
            displayMessageWithRefresh("Error: The selected individual for Chair and Manager roles is not allowed.");
            exit;
        }
    
        
        if (isDeptNameExists($conn, $deptName, $deptId)) {
            displayMessageWithRefresh("Error: A department with the same name already exists.");
            exit;
        }
    
        
        $currentDeptQuery = "SELECT ChairID, DeptManager FROM dept WHERE DeptID = '".mysqli_real_escape_string($conn, $deptId)."'";
        $currentDeptResult = mysqli_query($conn, $currentDeptQuery);
        $currentDeptRow = mysqli_fetch_assoc($currentDeptResult);
    
       
        if ($chairId != $currentDeptRow['ChairID']) {
            updateFacultyPosition($conn, $currentDeptRow['ChairID'], 'Professor');
            updateFacultyPosition($conn, $chairId, 'Chair');
        }
        if ($deptManagerId != $currentDeptRow['DeptManager']) {
            updateFacultyPosition($conn, $currentDeptRow['DeptManager'], 'Professor');
            updateFacultyPosition($conn, $deptManagerId, 'Manager');
        }
    
        
        function isValueInUse($conn, $columnName, $value, $excludeDeptId) {
            $query = "SELECT 1 FROM dept 
                      WHERE $columnName = '".mysqli_real_escape_string($conn, $value)."' 
                      AND DeptID != '".mysqli_real_escape_string($conn, $excludeDeptId)."'";
            $result = mysqli_query($conn, $query);
            return mysqli_num_rows($result) > 0;
        }
    
      
        if ($email != '' && isValueInUse($conn, 'Email', $email, $deptId)) {
            displayMessageWithRefresh("Error: The email is already in use by another department.");
            exit;
        }
        if ($roomID != '' && isValueInUse($conn, 'RoomID', $roomID, $deptId)) {
            displayMessageWithRefresh("Error: The room ID is already in use by another department.");
            exit;
        }
    
       
        $updateFields = [];
        if ($deptName != '') {
            $updateFields[] = "DeptName = '".mysqli_real_escape_string($conn, $deptName)."'";
        }
        if ($chairId != '') {
            $updateFields[] = "ChairID = '".mysqli_real_escape_string($conn, $chairId)."'";
        }
        if ($deptManagerId != '') {
            $updateFields[] = "DeptManager = '".mysqli_real_escape_string($conn, $deptManagerId)."'";
        }
        if ($email != '') {
            $updateFields[] = "Email = '".mysqli_real_escape_string($conn, $email)."'";
        }
        if ($phoneNum != '') {
            $updateFields[] = "PhoneNum = '".mysqli_real_escape_string($conn, $phoneNum)."'";
        }
        if ($roomID != '') {
            $updateFields[] = "RoomID = '".mysqli_real_escape_string($conn, $roomID)."'";
        }
    
        
        $updateQuery .= implode(', ', $updateFields);
        $updateQuery .= " WHERE DeptID = '".mysqli_real_escape_string($conn, $deptId)."'";
    
        
        $result = mysqli_query($conn, $updateQuery);
    
        if ($result) {
            displayMessageWithRefresh("Department updated successfully.");
        } else {
            displayMessageWithRefresh("Error updating department: " . mysqli_error($conn));
        }
    }

}
?>
