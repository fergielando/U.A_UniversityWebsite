<?php
@include 'config1.php'; 

$deptId = isset($_GET['deptId']) ? $_GET['deptId'] : '';

if ($deptId != '') {
    
    $deptQuery = "SELECT d.*, 
                         u1.FirstName AS ChairFirstName, u1.LastName AS ChairLastName, 
                         u2.FirstName AS ManagerFirstName, u2.LastName AS ManagerLastName
                  FROM dept d
                  LEFT JOIN user u1 ON d.ChairID = u1.UID
                  LEFT JOIN user u2 ON d.DeptManager = u2.UID
                  WHERE d.DeptID = '".mysqli_real_escape_string($conn, $deptId)."'";
    $result = mysqli_query($conn, $deptQuery);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        echo '<form action="updateDepartment.php" method="post">';
        echo '<input type="hidden" name="deptId" value="'.htmlspecialchars($row['DeptID']).'">';
        echo '<label for="deptName">Department Name:</label>';
        echo '<input type="text" id="deptName" name="deptName" value="'.htmlspecialchars($row['DeptName']).'"><br>';

        
        echo '<label for="chairId">Chair ID:</label>';
        echo '<input type="text" id="chairId" name="chairId" value="'.htmlspecialchars($row['ChairID']).'"><br>';
        echo '<label for="chairName">Chair Name:</label>';
        echo '<input type="text" id="chairName" value="'.htmlspecialchars($row['ChairFirstName'].' '.$row['ChairLastName']).'" disabled><br>';

        
        echo '<label for="deptManagerId">Department Manager ID:</label>';
        echo '<input type="text" id="deptManagerId" name="deptManagerId" value="'.htmlspecialchars($row['DeptManager']).'"><br>';
        echo '<label for="managerName">Department Manager Name:</label>';
        echo '<input type="text" id="managerName" value="'.htmlspecialchars($row['ManagerFirstName'].' '.$row['ManagerLastName']).'" disabled><br>';

        echo '<label for="email">Email:</label>';
        echo '<input type="email" id="email" name="email" value="'.htmlspecialchars($row['Email']).'"><br>';

        echo '<label for="phoneNum">Phone Number:</label>';
        echo '<input type="tel" id="phoneNum" name="phoneNum" value="'.htmlspecialchars($row['PhoneNum']).'"><br>';

        
        echo '<label for="roomID">Room ID (Open Offices):</label>';
        echo '<select id="roomID" name="roomID">';
        
        
        $officeQuery = "SELECT r.RoomID, r.RoomNum, r.BuildingID 
                        FROM room r 
                        WHERE r.RoomType = 'Office' 
                         AND (r.RoomID = '" . mysqli_real_escape_string($conn, $row['RoomID']) . "'
						OR r.RoomID NOT IN (SELECT RoomID FROM dept WHERE RoomID IS NOT NULL))";
        $officeResult = mysqli_query($conn, $officeQuery);

        while ($office = mysqli_fetch_assoc($officeResult)) {
            $selected = ($row['RoomID'] == $office['RoomID']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($office['RoomID']) . '" ' . $selected . '>';
            echo 'Building ' . htmlspecialchars($office['BuildingID']) . ', Room ' . htmlspecialchars($office['RoomNum']);
            echo '</option>';
        }

        echo '</select><br>';

        echo '<input type="submit" value="Update Department">';
        echo '</form>';
    } else {
        echo 'No details found for the selected department.';
    }
} else {
    echo 'Please select a department.';
}
?>
