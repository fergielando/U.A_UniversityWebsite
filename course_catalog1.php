<?php
@include 'config1.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:login_form1.php');
}

// Retrieve course data from the database
$query = "SELECT 
course.CourseID,
course.CourseName,
dept.DeptName,
course.Credits,
course.Description,
course.CourseType
FROM course 
JOIN dept ON course.DeptID = dept.DeptID
WHERE CourseID <> 'NULL'
ORDER BY course.CourseID ASC";

$result = mysqli_query($conn, $query);

$courses = [];

while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

// Fetch distinct department IDs for the filter
$query = "SELECT DISTINCT dept.DeptName FROM dept WHERE DeptID <> 'NULL'";
$result = mysqli_query($conn, $query);

$departmentIDs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $departmentIDs[] = $row['DeptName'];
}

// Fetch distinct credits for the filter
$query = "SELECT DISTINCT course.Credits FROM course WHERE Credits <> 0 ORDER BY course.Credits ASC";
$result = mysqli_query($conn, $query);

$credit = [];
while ($row = mysqli_fetch_assoc($result)) {
    $credit[] = $row['Credits'];
}

// Fetch distinct course type for the filter
$query = "SELECT DISTINCT course.CourseType FROM course WHERE course.courseType <> 'NULL'";
$result = mysqli_query($conn, $query);

$coursetype = [];
while ($row = mysqli_fetch_assoc($result)) {
    $coursetype[] = $row['CourseType'];
}

//Get Course Prerequisites for each Course
foreach ($courses as &$course) {
    $courseID = $course['CourseID'];
    $prerequisitesQuery = "SELECT PRCourseID FROM courseprerequisite WHERE CourseID = '$courseID'";
    $prerequisitesResult = mysqli_query($conn, $prerequisitesQuery);

    $prerequisites = [];
    while ($row = mysqli_fetch_assoc($prerequisitesResult)) {
        $prerequisites[] = $row['PRCourseID'];
    }

    $course['Prerequisites'] = $prerequisites;
}
unset($course); // Unset the reference after the loop

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Course Catalog</title>
   <link rel="stylesheet" href="css/fatman1.css">

   
   <div class="header">
      <img src="ua.png" alt="U.A. Logo" class="logo">
      <h1>U.A. University Admin Course Catalog</h1>
      <div class="buttons">
         <a href="createcoursereal.php" class="btn">Create Course</a>
         <a href="deletecourse.php" class="btn">Delete Course</a>
         <a href="create_prerequisite.php" class="btn">Create Course Prerequisite</a>
      <a href="admin_page1.php" class="btn">Back to Admin Page</a>
      </div>
   </div>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    /* Header styles */
.header {
    background: #000;
    color: #fff;
    padding: 20px;
    text-align: center;
}

.header h1 {
    font-size: 36px;
    margin: 0;
}

.logo {
    width: 50px;
    height: 50px;
}

.buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.update-btn {
    background-color: #4CAF50; /* Green */
    color: white;
    padding: 6px 12px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 4px;
}


    
    table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ddd;
    }
    th, td {
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #e9e9e9;
    }
      .search-container {
         margin: 20px 0;
         text-align: center;
      }

      .search-container input[type="text"] {
         padding: 10px;
         border: 1px solid #ccc;
         border-radius: 5px;
         font-size: 16px;
      }

      .search-container button {
         padding: 10px 20px;
         background-color: #000;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         font-size: 16px;
      }

      .search-container button:hover {
         background-color: #333;
      }
      .filter-container {
         display: flex;
         justify-content: space-between;
         margin: 10px 0;
         padding: 10px;
         background-color: #f2f2f2;
      }

      .filter-container label {
         font-weight: bold;
      }

      .filter-container select {
         padding: 5px;
      }
</style>

</head>
<body>

<div class="header">
    <h1>Course Management</h1>
</div>

   <div class="search-container">
       General Search: 
      <input type="text" id="searchInput" placeholder="General Search..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
   </div>
   
   <!-- Filter container for CourseID/Course Name -->
   <div class="search-container">
       Course ID:
      <input type="text" id="courseidSearch" placeholder="Search by Course ID..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
      
      Course Name:
      <input type="text" id="coursenameSearch" placeholder="Search by Course Name..." onkeyup="searchAndFilterTable()">
      <button onclick="resetTable()">Reset</button>
   </div>

   <!-- Filter container for Credits -->
   <div class="filter-container">
   
   <label for="deptFilter">Department:</label>
   <select id="deptFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($departmentIDs as $departmentID): ?>
         <option value="<?php echo $departmentID; ?>"><?php echo $departmentID; ?></option>
      <?php endforeach; ?>
   </select>
   
   <label for="creditsFilter">Credits:</label>
   <select id="creditsFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($credit as $Credits): ?>
         <option value="<?php echo $Credits; ?>"><?php echo $Credits; ?></option>
      <?php endforeach; ?>
   </select>
   
      <label for="coursetypeFilter">Course Type:</label>
   <select id="coursetypeFilter" onchange="searchAndFilterTable()">
      <option value="">All</option>
      <?php foreach ($coursetype as $CourseType): ?>
         <option value="<?php echo $CourseType; ?>"><?php echo $CourseType; ?></option>
      <?php endforeach; ?>
   </select>
</div>

	 <h2>Courses</h2>
    <table>
    <thead>
    <tr>
            <th>Course ID</th>
            <th>Course Name</th>
			  <th>Prerequisites</th>
            <th>Department</th>
            <th>Credits</th>
            <th>Description</th>
            <th>Course Type</th>
        <th>Update Course Info</th> <!-- New column for actions -->
		 <th>Manage Course Prerequisites</th> <!-- New column for actions -->
    </tr>
</thead>
<tbody>
    <?php foreach ($courses as $course): ?>
        <tr>
               <td><?php echo $course['CourseID']; ?></td>
               <td><?php echo $course['CourseName']; ?></td>
				  <td>
                <?php if (!empty($course['Prerequisites'])): ?>
                    <ul>
                        <?php foreach ($course['Prerequisites'] as $prerequisite): ?>
                            <li><?php echo $prerequisite; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    None
                <?php endif; ?>
             </td>
               <td><?php echo $course['DeptName']; ?></td>
               <td><?php echo $course['Credits']; ?></td>
               <td><?php echo $course['Description']; ?></td>
               <td><?php echo $course['CourseType']; ?></td>
            <td>
                <!-- Update button -->
                <a href="update_course.php?id=<?php echo $course['CourseID']; ?>" class="update-btn">Update</a>
            </td>
            <td>
                <!-- Update button -->
                <a href="update_prerequisites_page.php?id=<?php echo $course['CourseID']; ?>" class="update-btn">Update</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
</table>

<script>
      function searchAndFilterTable() {
        var searchText = document.getElementById('searchInput').value.toUpperCase();
        var courseidText = document.getElementById('courseidSearch').value.toUpperCase();
        var coursenameText = document.getElementById('coursenameSearch').value.toUpperCase();
        var filters = {
            'Department': document.getElementById('deptFilter').value.toUpperCase(),
            'Credits': document.getElementById('creditsFilter').value.toUpperCase(),
            'Course Type': document.getElementById('coursetypeFilter').value.toUpperCase(),
            // Add other filters here
        };

        var table = document.querySelector('table');
        var rows = table.getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var showRow = true;

            for (var filter in filters) {
                var columnIndex = getColumnIndex(filter);
                if (columnIndex > -1) {
                    var cell = row.getElementsByTagName('td')[columnIndex];
                    if (cell) {
                        var cellValue = cell.textContent || cell.innerText;
                        if (cellValue.toUpperCase().indexOf(filters[filter]) === -1 && filters[filter] !== 'ALL') {
                            showRow = false;
                            break;
                        }
                    }
                }
            }

            var courseidCell = row.getElementsByTagName('td')[0]; // Assuming Course ID is in the 1st column
            if (courseidCell) {
                var courseidValue = courseidCell.textContent || courseidCell.innerText;
                if (courseidValue.toUpperCase().indexOf(courseidText) === -1 && courseidText !== '') {
                    showRow = false;
                }
            }
            
            var coursenameCell = row.getElementsByTagName('td')[1]; // Assuming Course Name is in the 1st column
            if (coursenameCell) {
                var coursenameValue = coursenameCell.textContent || coursenameCell.innerText;
                if (coursenameValue.toUpperCase().indexOf(coursenameText) === -1 && coursenameText !== '') {
                    showRow = false;
                }
            }
			
 // Modified code for 'Course Type' filter comparison
    var columnIndex = getColumnIndex('Course Type'); // Get the index of 'Course Type' column
    if (columnIndex > -1) {
        var cell = row.getElementsByTagName('td')[columnIndex];
        if (cell) {
            var cellValue = cell.textContent || cell.innerText;
            var filterValue = filters['Course Type'];

            if (filterValue !== '' && filterValue !== 'ALL') {
                if (cellValue.toUpperCase() !== filterValue.toUpperCase()) {
                    showRow = false;
                }
            }
        }
    }
            
            var rowData = row.textContent || row.innerText;
            if (rowData.toUpperCase().indexOf(searchText) > -1 && showRow) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function getColumnIndex(columnName) {
         var table = document.querySelector("table");
         var header = table.querySelector("thead");
         var thArray = Array.from(header.querySelectorAll("th"));
         for (var i = 0; i < thArray.length; i++) {
            if (thArray[i].textContent.trim() === columnName) {
               return i;
            }
         }
         return -1;
      }

    function resetTable() {
        var table = document.querySelector('table');
        var rows = table.getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            rows[i].style.display = '';
        }

        document.getElementById('searchInput').value = '';
        // Reset all filter dropdowns to default
        document.getElementById('courseidSearch').value = '';
        document.getElementById('coursenameSearch').value = '';
        document.getElementById('deptFilter').value = '';
        document.getElementById('creditsFilter').value = '';
        document.getElementById('coursetypeFilter').value = '';
        // Add other filter reset lines here if needed
    }

   </script>
   
</body>
</html>