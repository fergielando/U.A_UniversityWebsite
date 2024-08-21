<?php
@include 'config1.php';



$deptID = '';


if (isset($_GET['id'])) {
    $deptID = mysqli_real_escape_string($conn, $_GET['id']);
}

// Create a new department
if (isset($_POST['create'])) {
    $newDeptID = mysqli_real_escape_string($conn, $_POST['newDeptID']);
    $newDeptName = mysqli_real_escape_string($conn, $_POST['newDeptName']);
    $newChairId = mysqli_real_escape_string($conn, $_POST['newChairId']);
    $newDeptManager = mysqli_real_escape_string($conn, $_POST['newDeptManager']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['newEmail']);
    $newPhoneNum = mysqli_real_escape_string($conn, $_POST['newPhoneNum']);
    $newRoomID = mysqli_real_escape_string($conn, $_POST['newRoomID']);


    if (isset($_POST['create'])) {
      $newDeptID = mysqli_real_escape_string($conn, $_POST['newDeptID']);
      $newDeptName = mysqli_real_escape_string($conn, $_POST['newDeptName']);
      $newChairId = mysqli_real_escape_string($conn, $_POST['newChairId']);
      $newDeptManager = mysqli_real_escape_string($conn, $_POST['newDeptManager']);
      $newEmail = mysqli_real_escape_string($conn, $_POST['newEmail']);
      $newPhoneNum = mysqli_real_escape_string($conn, $_POST['newPhoneNum']);
    // Check for existing department name or ID
    if (departmentExists($conn, $newDeptID, $newDeptName)) {
      echo "Department ID or Name already exists. Please use a different ID or Name.";
  }
  // Check if the same person is assigned as both manager and chair
  else if (isSamePerson($newChairId, $newDeptManager)) {
      echo "The same person cannot be both chair and manager. Please assign different individuals.";
  }
  // Check if the chair or manager already holds a position in the faculty
  else if (isExistingRole($conn, $newChairId, 'Chair') || isExistingRole($conn, $newDeptManager, 'Manager')) {
      echo "This chair or manager is already assigned to a position. Please select different individuals.";
  }
  // Check if the email or phone number already exists
  else if (isContactInfoExists($conn, $newEmail, $newPhoneNum)) {
      echo "The email address or phone number is already in use. Please use different contact information.";
  }
  else {
      $insertQuery = "INSERT INTO dept (DeptID, DeptName, ChairID, DeptManager, Email, PhoneNum, RoomID) VALUES ('$newDeptID', '$newDeptName', '$newChairId', '$newDeptManager', '$newEmail', '$newPhoneNum', '$newRoomID')";
      if (mysqli_query($conn, $insertQuery)) {
          header('location:department_page1.php');
      } else {
          echo "Department creation failed: " . mysqli_error($conn);
      }
  }
}  
  
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

$officeQuery = "
SELECT r.RoomID, r.RoomNum, r.RoomID AS OfficeRoomID, b.BuildingName 
FROM room r 
INNER JOIN building b ON r.BuildingID = b.BuildingID
WHERE r.RoomType = 'Office' 
AND r.RoomID NOT IN (SELECT RoomID FROM dept WHERE RoomID IS NOT NULL)";
$officeResult = mysqli_query($conn, $officeQuery);
$offices = [];
while ($row = mysqli_fetch_assoc($officeResult)) {
    $offices[] = $row;
}


function departmentExists($conn, $deptID, $deptName) {
   // Prepare a SQL query to check if the department exists
   $query = $conn->prepare("SELECT * FROM dept WHERE DeptID = ? OR DeptName = ?");
   $query->bind_param("ss", $deptID, $deptName);
   $query->execute();
   $result = $query->get_result();
   return $result->num_rows > 0;
}

function isExistingRole($conn, $roleId, $role) {
   // Adjust the query to check the faculty table and the position column
   $query = $conn->prepare("SELECT * FROM faculty WHERE FacultyID = ? AND Position = ?");
   $query->bind_param("ss", $roleId, $role);
   $query->execute();
   $result = $query->get_result();
   return $result->num_rows > 0;
}

function isSamePerson($chairId, $managerId) {
   return $chairId === $managerId;
}


function isContactInfoExists($conn, $email, $phoneNum) {
   $query = $conn->prepare("SELECT * FROM dept WHERE Email = ? OR PhoneNum = ?");
   $query->bind_param("ss", $email, $phoneNum);
   $query->execute();
   $result = $query->get_result();
   return $result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
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
   </style>
</head>
</head>
<body>
   <div class="header">
      <h1>Create Department</h1>
      <a href="Departments_page1.php" class="back-button">Back to Department Page</a>
   </div>

   <!-- Add a form for creating a new department -->
   <div class="create-department-container">
      <h2>Create a New Department</h2>
      <form action="" method="post">
         <input type="text" name="newDeptID" placeholder="Department ID">
         <input type="text" name="newDeptName" placeholder="Department Name">
         <input type="text" name="newChairId" placeholder="Chair ID">
         <input type="text" name="newDeptManager" placeholder="Department Manager">
         <input type="text" name="newEmail" placeholder="Email">
         <input type="text" name="newPhoneNum" placeholder="Phone Number">
         <label for="newRoomID">Room ID:</label>
         <select name="newRoomID" id="newRoomID">
    <option value="">Select a Room - Offices Only</option>
    <?php foreach ($offices as $office): ?>
        <option value="<?php echo htmlspecialchars($office['RoomID']); ?>">
            <?php echo htmlspecialchars($office['BuildingName'] . ' - ' . $office['RoomNum'] . ' - ' . $office['RoomID']); ?>
        </option>
    <?php endforeach; ?>
</select>


         
        
         <input type="submit" name="create" value="Create">
      </form>
   </div>

<script type="text/javascript">

function loadFacultyByDepartment(deptId) {
    if (deptId === "") {
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

   
    if (deptId === "no_dept") {
        xmlhttp.open("GET", "getfacultywithoutdepartment.php", true);
    } else {
        xmlhttp.open("GET", "getfac1.php?dept_id=" + deptId, true);
    }
    xmlhttp.send();
}


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
        xmlhttp.open("GET", "getDepartmentDetails.php?deptId=" + deptId, true);
        xmlhttp.send();
    }
</script>

<h1>Faculty Info</h1>
<select id="departmentSelect" onchange="loadDepartmentDetails(this.value); loadFacultyByDepartment(this.value);">
    <option value="">Select a Department</option>
    <option value="no_dept">Faculty Without Department</option>
    <?php foreach ($departments as $department): ?>
        <option value="<?php echo htmlspecialchars($department['DeptID']); ?>">
            <?php echo htmlspecialchars($department['DeptName']); ?>
        </option>
    <?php endforeach; ?>
</select>




    
<div id="facultyDetails">
 
</div>

</body>
</html>