<?php
@include 'config1.php'; 


$deptID = '';


if (isset($_GET['id'])) {
    $deptID = mysqli_real_escape_string($conn, $_GET['id']);
}

function getDepartments($conn) {
    $query = "SELECT DeptID, DeptName FROM dept"; 
    $result = mysqli_query($conn, $query);
    $departments = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
    return $departments;
}
$departments = getDepartments($conn);

function getFacultyWithoutDepartment($conn) {
    $query = "SELECT f.FacultyID, u.FirstName, u.LastName, f.FacultyType 
              FROM faculty f
              LEFT JOIN facultydept fd ON f.FacultyID = fd.FacultyID
              LEFT JOIN user u ON f.FacultyID = u.UID
              WHERE fd.DeptID IS NULL";
    $result = mysqli_query($conn, $query);
    $facultyList = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $facultyList[] = $row;
    }
    return $facultyList;
}

$facultyWithoutDepartment = getFacultyWithoutDepartment($conn);


    
    function getFacultyInTwoDepartments($conn) {
        $query = "SELECT f.FacultyID, u.FirstName, u.LastName, 
                         GROUP_CONCAT(d.DeptName ORDER BY d.DeptName ASC SEPARATOR ', ') AS Departments
                  FROM faculty f
                  JOIN user u ON f.FacultyID = u.UID
                  JOIN facultydept fd ON f.FacultyID = fd.FacultyID
                  JOIN dept d ON fd.DeptID = d.DeptID
                  GROUP BY f.FacultyID
                  HAVING COUNT(fd.DeptID) = 2";
    
        $result = mysqli_query($conn, $query);
        $facultyList = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $facultyList[] = $row;
        }
        return $facultyList;
    }
    
    $facultyInTwoDepartments = getFacultyInTwoDepartments($conn);
    ?>
    <!DOCTYPE html>
<html>
<head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        select, input[type="text"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        #departmentDetails {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        .faculty-table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
    }

    .faculty-table th, .faculty-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .faculty-table th {
        background-color: #4CAF50;
        color: white;
    }

    .faculty-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .faculty-table tr:hover {
        background-color: #ddd;
    }

	.back-button {
    background: #000;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
}

/* Position the back button to the top right */
.header {
    position: relative;
}

.back-button {
    position: absolute;
    top: 20px;
    right: 20px;
}


    </style>
     <script type="text/javascript">
    function loadDepartmentDetails(deptId) {
        if (deptId == "") {
            document.getElementById("departmentDetails").innerHTML = "";
            return;
        }

        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("departmentDetails").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "getdepartmentdetails.php?deptId=" + deptId, true);
        xmlhttp.send();
    }
    function loadFacultyByDepartment(deptId) {
    if (deptId == "") {
        document.getElementById("facultyDetails").innerHTML = "";
        return;
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var facultyData = JSON.parse(this.responseText);
            var html = "<h3>Faculty Members</h3>";

            if (facultyData.length > 0) {
                html += "<table class='faculty-table'>";
                html += "<tr><th>ID</th><th>Name</th><th>Departments</th><th>Percent Time</th><th>Total Classes PT</th><th>Total Classes FT</th><th>Faculty Type</th><th>Position</th></tr>";
                facultyData.forEach(function(faculty) {
                    html += "<tr>";
                    html += "<td>" + faculty.FacultyID + "</td>";
                    html += "<td>" + faculty.FirstName + " " + faculty.LastName + "</td>";
                    html += "<td>" + (faculty.Departments ? faculty.Departments : 'N/A') + "</td>"; 
                    html += "<td>" + faculty.PercentTime + "</td>";
                    html += "<td>" + faculty.TotalClassesPT + "</td>";
                    html += "<td>" + faculty.TotalClassesFT + "</td>";
                    html += "<td>" + faculty.FacultyType + "</td>";
                    html += "<td>" + faculty.Position + "</td>"; 
                    html += "</tr>";
                });
                html += "</table>";
            } else {
                html += "No faculty members found for this department.";
            }

            document.getElementById("facultyDetails").innerHTML = html;
        }
    };
    xmlhttp.open("GET", "getfac1.php?dept_id=" + deptId, true);
    xmlhttp.send();
}



</script>
</head>
<body>
    <div class="header">
    <h1>Department Management</h1>
    <a href="Departments_page1.php" class="back-button">Back</a>
	</div>
	
    <label for="departmentSelect">Choose a Department:</label>
    <select id="departmentSelect" onchange="loadDepartmentDetails(this.value); loadFacultyByDepartment(this.value);">
        <option value="">Select a Department</option>
        <?php foreach ($departments as $department): ?>
            <option value="<?php echo htmlspecialchars($department['DeptID']); ?>">
                <?php echo htmlspecialchars($department['DeptName']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div id="facultyDetails">
    
</div>



<div class="add-department-form">
    <h2>Add Department to Faculty</h2>
    <form action="adddepartmenttofaculty.php" method="post">
        <label for="facultyInput">Faculty ID:</label>
        <input type="text" id="facultyInput" name="facultyId" required placeholder="Enter Faculty ID">

        <label for="departmentSelect">Select Department:</label>
        <select id="departmentSelect" name="deptId">
        
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo htmlspecialchars($department['DeptID']); ?>">
                    <?php echo htmlspecialchars($department['DeptName']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Add Department">
    </form>
</div>



<div class="remove-department-form">
    <h2>Remove Department from Faculty</h2>
    <form action="removedepartmentfromfaculty.php" method="post">
        <label for="facultyInputRemove">Faculty ID:</label>
        <input type="text" id="facultyInputRemove" name="facultyId" required placeholder="Enter Faculty ID">

        <label for="departmentSelectRemove">Select Department:</label>
        <select id="departmentSelectRemove" name="deptId">
           
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo htmlspecialchars($department['DeptID']); ?>">
                    <?php echo htmlspecialchars($department['DeptName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Remove Department">
    </form>
</div>


<h2>Change Faculty Position</h2>
<form action="changefacultyposition.php" method="post">
    <label for="facultyIdChange">Faculty ID:</label>
    <input type="text" id="facultyIdChange" name="facultyId" required placeholder="Enter Faculty ID">

    <label for="newPosition">Select New Position:</label>
    <select id="newPosition" name="newPosition">
        <?php
        
        $positions = array("Professor", "Associate Professor", "Assistant Professor", "Instructor");
        foreach ($positions as $position) {
            echo '<option value="' . htmlspecialchars($position) . '">' . htmlspecialchars($position) . '</option>';
        }
        ?>
    </select>

    <input type="submit" value="Change Position">
</form>

    
        <div id="facultyNoDepartment">
    <h2>Faculty Members Not Currently Assigned To A Department</h2>
    <table class="faculty-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Faculty Type</th>
        </tr>
        <?php foreach ($facultyWithoutDepartment as $faculty): ?>
            <tr>
                <td><?php echo htmlspecialchars($faculty['FacultyID']); ?></td>
                <td><?php echo htmlspecialchars($faculty['FirstName']) . " " . htmlspecialchars($faculty['LastName']); ?></td>
                <td><?php echo htmlspecialchars($faculty['FacultyType']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>


<div id="facultyTwoDepartments">
    <h2>Faculty Members in Two Departments</h2>
    <table class="faculty-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Departments</th>
        </tr>
        <?php foreach ($facultyInTwoDepartments as $faculty): ?>
            <tr>
                <td><?php echo htmlspecialchars($faculty['FacultyID']); ?></td>
                <td><?php echo htmlspecialchars($faculty['FirstName']) . " " . htmlspecialchars($faculty['LastName']); ?></td>
                <td><?php echo htmlspecialchars($faculty['Departments']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>


    



</body>
</html>
