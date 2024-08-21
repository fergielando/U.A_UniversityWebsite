<?php
@include 'config1.php'; 

if (isset($_GET['dept_id'])) {
    $selectedDept = $_GET['dept_id'];

    
    $query = $conn->prepare("
    SELECT 
        f.FacultyID, 
        u.FirstName, 
        u.LastName, 
        GROUP_CONCAT(fd.DeptID) AS Departments, 
        fd.PercentTime,
        f.FacultyType,
        COALESCE(SUM(pt.NumOfClass), 0) AS TotalClassesPT,
        COALESCE(SUM(ft.NumOfClass), 0) AS TotalClassesFT,
        f.Position -- Include the Position column
    FROM faculty f
    INNER JOIN facultydept fd ON f.FacultyID = fd.FacultyID
    INNER JOIN user u ON f.FacultyID = u.UID
    LEFT JOIN facultypt pt ON f.FacultyID = pt.FacultyID
    LEFT JOIN facultyft ft ON f.FacultyID = ft.FacultyID
    WHERE fd.DeptID = ?
    GROUP BY f.FacultyID, u.FirstName, u.LastName, fd.PercentTime, f.FacultyType, f.Position -- Include the Position column
    ");

    
    $query->bind_param("s", $selectedDept);

    
    $query->execute();

    
    $result = $query->get_result();

    
    $facultyData = array();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facultyData[] = $row;
        }
    }


    echo json_encode($facultyData);

  
    $query->close();
} else {
  
    echo json_encode(array());
}
?>
