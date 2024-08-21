<?php
// Include your database connection code here
@include 'config1.php';

// Fetch faculty information from the 'advisor' table
$query = "SELECT DISTINCT advisor.FacultyID, user.FirstName, user.LastName 
          FROM advisor
          INNER JOIN user ON advisor.FacultyID = user.UID
		  ORDER BY advisor.FacultyID";

$result = mysqli_query($conn, $query);

$facultyList = array();
while ($row = mysqli_fetch_assoc($result)) {
    $facultyList[] = $row;
}

// Initialize variables to store student and faculty information
$studentList = array();
$selectedFaculty = "";
$selectedStudent = "";

// Handle form submission for showing advised students
if (isset($_POST['facultyDropdown'])) {
    $selectedFacultyUID = $_POST['facultyDropdown'];
    
    // Fetch students advised by the selected faculty
    $queryShowAdvised = "SELECT advisor.StudentID, user.FirstName, user.LastName
              FROM advisor
              INNER JOIN user ON advisor.StudentID = user.UID
              WHERE advisor.FacultyID = $selectedFacultyUID
			  ORDER BY advisor.StudentID";

    $resultShowAdvised = mysqli_query($conn, $queryShowAdvised);

    while ($row = mysqli_fetch_assoc($resultShowAdvised)) {
        $studentList[] = $row;
    }

    // Retrieve the selected faculty information
    $selectedFacultyQuery = "SELECT UID, FirstName, LastName FROM user WHERE UID = $selectedFacultyUID";
    $facultyResult = mysqli_query($conn, $selectedFacultyQuery);
    $facultyData = mysqli_fetch_assoc($facultyResult);
    $selectedFaculty = $facultyData['FirstName'] . ' ' . $facultyData['LastName'];
}

// Handle form submission for assigning faculty to advise student
if (isset($_POST['assignFaculty'])) {
    $selectedFacultyUID = $_POST['facultyID'];
    $selectedStudentUID = $_POST['studentID'];
    
    // Check if the selected student already has more than 2 advisors
    $advisorCountQuery = "SELECT COUNT(*) AS advisorCount FROM advisor WHERE StudentID = $selectedStudentUID";
    $advisorCountResult = mysqli_query($conn, $advisorCountQuery);
    $advisorCountData = mysqli_fetch_assoc($advisorCountResult);
    $advisorCount = $advisorCountData['advisorCount'];
	
	$facultyStudentCountQuery = "SELECT COUNT(*) AS facultyStudentCount FROM advisor WHERE FacultyID = $selectedFacultyUID";
    $facultyStudentCountResult = mysqli_query($conn, $facultyStudentCountQuery);
    $facultyStudentCountData = mysqli_fetch_assoc($facultyStudentCountResult);
    $facultyStudentCount = $facultyStudentCountData['facultyStudentCount'];

    if ($advisorCount >= 2) {
        $assignError = "The selected student already has 2 or more advisors.";
    } elseif ($facultyStudentCount >= 10) {
        $assignError = "The selected faculty already advises 10 students.";
    } else {
        // Perform the faculty-to-student assignment in your database
        // Make sure to handle this securely and with appropriate validation
        // Example query:
        $assignQuery = "INSERT INTO advisor (FacultyID, StudentID, DOA) 
                        VALUES ($selectedFacultyUID, $selectedStudentUID, NOW())";

        if (mysqli_query($conn, $assignQuery)) {
            // Assignment successful, you can provide a success message
            $assignSuccess = "Faculty assigned successfully.";
        } else {
            // Assignment failed, provide an error message
            $assignError = "Faculty assignment failed. Please try again.";
        }
    }
}

// Fetch students who don't have more than 2 advisors for the "Assign Faculty to Advise Student" dropdown
$queryAssignStudent = "SELECT user.UID AS StudentID, user.FirstName, user.LastName
                    FROM user
                    LEFT JOIN (
                        SELECT StudentID, COUNT(*) AS advisorCount
                        FROM advisor
                        GROUP BY StudentID
                    ) AS advisorCounts ON user.UID = advisorCounts.StudentID
                    WHERE (advisorCounts.advisorCount IS NULL OR advisorCounts.advisorCount < 2) AND StudentID > 50000";

$assignStudentResult = mysqli_query($conn, $queryAssignStudent);

$assignStudentList = array();
while ($row = mysqli_fetch_assoc($assignStudentResult)) {
    $assignStudentList[] = $row;
}

// Handle form submission for unassigning faculty from student
if (isset($_POST['unassignFaculty'])) {
    $selectedUnassignFacultyUID = $_POST['unassignFacultyID'];
    $selectedUnassignStudentUID = $_POST['unassignStudentID'];
    
    // Check if the selected faculty is advising the selected student
    $unassignCheckQuery = "SELECT * FROM advisor WHERE FacultyID = $selectedUnassignFacultyUID AND StudentID = $selectedUnassignStudentUID";
    $unassignCheckResult = mysqli_query($conn, $unassignCheckQuery);

    if (mysqli_num_rows($unassignCheckResult) === 0) {
        $unassignError = "The selected faculty is not advising the selected student.";
    } else {
        // Perform the unassignment in your database
        // Example query:
        $unassignQuery = "DELETE FROM advisor WHERE FacultyID = $selectedUnassignFacultyUID AND StudentID = $selectedUnassignStudentUID";

        if (mysqli_query($conn, $unassignQuery)) {
            // Unassignment successful, you can provide a success message
            $unassignSuccess = "Faculty unassigned successfully from the student.";
        } else {
            // Unassignment failed, provide an error message
            $unassignError = "Faculty unassignment failed. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Advisement Page</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        h2 {
            margin-top: 20px;
            color: #333;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 5px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Faculty Advisement Page</h1>
    <div style="text-align: right; margin-right: 20px;">
        <a href="javascript:history.go(-1)">Back</a>
    </div>
    <form method="post">
        <label for="facultyDropdown">Select Faculty:</label>
        <select id="facultyDropdown" name="facultyDropdown">
            <option value="">Select Faculty</option>
            <?php foreach ($facultyList as $faculty): ?>
                <option value="<?php echo $faculty['FacultyID']; ?>"> <?php echo $faculty['FacultyID']; ?> - <?php echo $faculty['FirstName'] . ' ' . $faculty['LastName']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Show Advised Students</button>
    </form>
    
    <?php if (!empty($selectedFaculty)): ?>
        <h2>Students Advised by <?php echo $selectedFaculty; ?></h2>
        <?php if (empty($studentList)): ?>
            <p>No students are advised by this faculty.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($studentList as $student): ?>
                    <li><?php echo $student['StudentID'] . ' - ' . $student['FirstName'] . ' ' . $student['LastName']; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <div class="unassign-section">
    <h2>Unassign Faculty from Student</h2>
    <?php if (isset($unassignError)): ?>
        <p class="error"><?php echo $unassignError; ?></p>
    <?php elseif (isset($unassignSuccess)): ?>
        <p class="success"><?php echo $unassignSuccess; ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="unassignFacultyID">Select Faculty:</label>
        <select id="unassignFacultyID" name="unassignFacultyID">
            <option value="">Select Faculty</option>
            <?php foreach ($facultyList as $faculty): ?>
                <option value="<?php echo $faculty['FacultyID']; ?>"><?php echo $faculty['FacultyID']; ?> - <?php echo $faculty['FirstName'] . ' ' . $faculty['LastName']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="unassignStudentID">Select Student:</label>
        <select id="unassignStudentID" name="unassignStudentID">
            <option value="">Select Student</option>
            <?php foreach ($studentList as $student): ?>
                <option value="<?php echo $student['StudentID']; ?>"><?php echo $student['StudentID']; ?> - <?php echo $student['FirstName'] . ' ' . $student['LastName']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="unassignFaculty">Unassign</button>
    </form>
</div>

    <div class="assign-section">
        <h2>Assign Faculty to Advise Student</h2>
        <?php if (isset($assignError)): ?>
            <p style="color: red;"><?php echo $assignError; ?></p>
        <?php elseif (isset($assignSuccess)): ?>
            <p style="color: green;"><?php echo $assignSuccess; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="facultyID">Select Faculty:</label>
            <select id="facultyID" name="facultyID">
                <option value="">Select Faculty</option>
                <?php foreach ($facultyList as $faculty): ?>
                    <option value="<?php echo $faculty['FacultyID']; ?>"> <?php echo $faculty['FacultyID']; ?> - <?php echo $faculty['FirstName'] . ' ' . $faculty['LastName']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="studentID">Select Student:</label>
            <select id="studentID" name="studentID">
                <option value="">Select Student</option>
                <?php foreach ($assignStudentList as $student): ?>
                    <option value="<?php echo $student['StudentID']; ?>"> <?php echo $student['StudentID']; ?> - <?php echo $student['FirstName'] . ' ' . $student['LastName']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="assignFaculty">Assign</button>
        </form>
    </div>
    </body>
    </html>