<?php
@include 'config1.php';

// Fetching MIS department details
$DeptQuery = "SELECT * FROM dept WHERE DeptID = 'MIS'";
$DeptResult = mysqli_query($conn, $DeptQuery);
$DeptDetails = mysqli_fetch_assoc($DeptResult);

// Fetching MIS courses
$CoursesQuery = "SELECT c.*, GROUP_CONCAT(cp.PRCourseID) AS Prerequisites
                    FROM course c
                    LEFT JOIN courseprerequisite cp ON c.CourseID = cp.CourseID
                    WHERE c.DeptID = 'MIS'
                    GROUP BY c.CourseID";
$CoursesResult = mysqli_query($conn, $CoursesQuery);
$Courses = [];
while ($course = mysqli_fetch_assoc($CoursesResult)) {
	$course['Prerequisites'] = $course['Prerequisites'] ? explode(',', $course['Prerequisites']) : [];
    $Courses[] = $course;
}

//Fetching Department Email
$deptemailQuery = "SELECT Email FROM dept WHERE DeptID = 'MIS'";
$deptemailResult = mysqli_query($conn, $deptemailQuery);
$deptemailDetails = mysqli_fetch_assoc($deptemailResult);

//Fetching Department Phone
$deptphoneQuery = "SELECT PhoneNum FROM dept WHERE DeptID = 'MIS'";
$deptphoneResult = mysqli_query($conn, $deptphoneQuery);
$deptphoneDetails = mysqli_fetch_assoc($deptphoneResult);

//Fetching Department Office
$deptroomQuery = "SELECT RoomID FROM dept WHERE DeptID = 'MIS'";
$deptroomResult = mysqli_query($conn, $deptroomQuery);
$deptroomDetails = mysqli_fetch_assoc($deptroomResult);

// Fetching Chair Details
$deptchairQuery = "SELECT 
faculty.FacultyID, 
user.FirstName AS ChairFirstName, 
user.LastName AS ChairLastName, 
logintable.Email AS ChairEmail
FROM dept 
				JOIN faculty  ON faculty.FacultyID = dept.ChairID
                JOIN user  ON faculty.FacultyID = user.UID
				JOIN logintable  ON user.UID = logintable.UID
WHERE DeptID = 'MIS'";
$deptchairResult = mysqli_query($conn, $deptchairQuery);
$deptchairDetails = mysqli_fetch_assoc($deptchairResult);

// Fetching Dept Manager Details
$deptmanagerQuery = "SELECT 
faculty.FacultyID, 
user.FirstName AS ManagerFirstName, 
user.LastName AS ManagerLastName, 
logintable.Email AS ManagerEmail
FROM dept 
				JOIN faculty  ON faculty.FacultyID = dept.DeptManager
                JOIN user  ON faculty.FacultyID = user.UID
				JOIN logintable  ON user.UID = logintable.UID
WHERE DeptID = 'MIS'";
$deptmanagerResult = mysqli_query($conn, $deptmanagerQuery);
$deptmanagerDetails = mysqli_fetch_assoc($deptmanagerResult);

// Fetching faculty in the MIS department including their emails and OfficeID from the facultyft and facultypf tables
$facultyQuery = "SELECT 
    f.FacultyID, 
    f.FacultyType, 
    u.FirstName AS FacultyFirstName, 
    u.LastName AS FacultyLastName, 
    f.Position AS Position, 
    f.Specialty AS Specialty, 
    lt.Email AS FacultyEmail,
    COALESCE(ff.OfficeID, fp.OfficeID) AS OfficeID
FROM faculty f
JOIN facultydept fd ON f.FacultyID = fd.FacultyID
JOIN user u ON f.FacultyID = u.UID
JOIN logintable lt ON u.UID = lt.UID
LEFT JOIN facultyft ff ON f.FacultyID = ff.FacultyID
LEFT JOIN facultypt fp ON f.FacultyID = fp.FacultyID
WHERE fd.DeptID = 'MIS'";

$facultyResult = mysqli_query($conn, $facultyQuery);
$facultyList = [];
while ($faculty = mysqli_fetch_assoc($facultyResult)) {
    $facultyList[] = $faculty;
}

//Fetching Department Majors
$MajorsQuery = "SELECT m.*, GROUP_CONCAT(mp.PRmajorID) AS Prerequisites
                    FROM major m
                    LEFT JOIN majorprerequisite mp ON m.MajorID = mp.MajorID
                    WHERE m.DeptID = 'MIS'
                    GROUP BY m.MajorID";
$MajorsResult = mysqli_query($conn, $MajorsQuery);
$Majors = [];
while ($major = mysqli_fetch_assoc($MajorsResult)) {
	$major['Prerequisites'] = $major['Prerequisites'] ? explode(',', $major['Prerequisites']) : [];
    $Majors[] = $major;
}

//Fetching Department Minors
$MinorsQuery = "SELECT m.*, GROUP_CONCAT(mp.PRminorID) AS Prerequisites
                    FROM minor m
                    LEFT JOIN minorprerequisite mp ON m.MinorID = mp.MinorID
                    WHERE m.DeptID = 'MIS'
                    GROUP BY m.MinorID";
$MinorsResult = mysqli_query($conn, $MinorsQuery);
$Minors = [];
while ($minor = mysqli_fetch_assoc($MinorsResult)) {
	$minor['Prerequisites'] = $minor['Prerequisites'] ? explode(',', $minor['Prerequisites']) : [];
    $Minors[] = $minor;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $DeptDetails['DeptName']; ?> Department - UA University</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <div class="header">
            <h1>Welcome to the <?php echo $DeptDetails['DeptName']; ?> Department</h1>
            <button class="back-button" onclick="goBack()">Back</button>
        </div>
    </header>

    <main>
    <main>
        <section>
            <h2>Message from the Chair and Manager - <?php echo $deptchairDetails['ChairFirstName'] . ' ' . $deptchairDetails['ChairLastName']; ?> - Chair and <?php echo $deptmanagerDetails['ManagerFirstName'] . ' ' . $deptmanagerDetails['ManagerLastName']; ?> - Manager</h2>
            <p>Welcome to the <?php echo $DeptDetails['DeptName']; ?> Department at UA University! Our department is dedicated to exploring the dynamic and growing field of information systems, and providing a comprehensive education that prepares our students for the challenges of the modern technological landscape.</p>
              <p>Contact Information:</p>
            <ul>
                <li>Department Email: <a href="mailto:<?php echo $deptemailDetails['Email']; ?>"><?php echo $deptemailDetails['Email']; ?></a></li>
                <li>Department Phone: <?php echo $deptphoneDetails['PhoneNum']; ?></li>
				   <li>Department Office: <?php echo $deptroomDetails['RoomID']; ?></li>
            </ul>
            <p>Department Chair:</p>
            <ul>
                 <li>Name: <?php echo $deptchairDetails['ChairFirstName'] . ' ' . $deptchairDetails['ChairLastName']; ?></li>
                <li>Email: <a href="mailto:<?php echo $deptchairDetails['ChairEmail']; ?>"><?php echo $deptchairDetails['ChairEmail']; ?></a></li>
                <li>Phone: (555) 123-4567</li>
            </ul>
			<p>Department Manager:</p>
            <ul>
                <li>Name: <?php echo $deptmanagerDetails['ManagerFirstName'] . ' ' . $deptmanagerDetails['ManagerLastName']; ?></li>
                <li>Email: <a href="mailto:<?php echo $deptmanagerDetails['ManagerEmail']; ?>"><?php echo $deptmanagerDetails['ManagerEmail']; ?></a></li>
                <li>Phone: (555) 347-8790</li>
            </ul>
        </section>

            <section>
            <h2>Faculty in the <?php echo $DeptDetails['DeptName']; ?> Department</h2>
            <table>
    <tr>
        <th>Faculty ID</th>
        <th>Faculty Name</th>
        <th>Position</th>
        <th>Specialty</th>
        <th>Faculty Type</th>
        <th>Email</th>
        <th>OfficeID</th> <!-- Added OfficeID column -->
    </tr>
    <?php foreach ($facultyList as $faculty): ?>
    <tr>
        <td><?php echo $faculty['FacultyID']; ?></td>
        <td><?php echo $faculty['FacultyFirstName'] . ' ' . $faculty['FacultyLastName']; ?></td>
        <td><?php echo $faculty['Position']; ?></td>
        <td><?php echo $faculty['Specialty']; ?></td>
        <td><?php echo $faculty['FacultyType']; ?></td>
        <td><?php echo $faculty['FacultyEmail']; ?></td>
        <td><?php echo $faculty['OfficeID']; ?></td> <!-- Displaying OfficeID -->
    </tr>
    <?php endforeach; ?>
</table>
        </section>
        <section>
            <h2>Major Offerings</h2>
            <table>
                <tr>
                    <th>Major Name</th>
						<th>Major Prerequisites</th>
                </tr>
                <?php foreach ($Majors as $major): ?>
                <tr>
                    <td><?php echo $major['MajorName']; ?></td>
						<td>
                <?php if (!empty($major['Prerequisites'])): ?>
                    <ul>
                        <?php foreach ($major['Prerequisites'] as $prerequisite): ?>
                            <li><?php echo $prerequisite; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    No prerequisites
                <?php endif; ?>
					  </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
		
        <section>
            <h2>Minor Offerings</h2>
            <table>
                <tr>
                    <th>Minor Name</th>
						<th>Minor Prerequisites</th>
                </tr>
                <?php foreach ($Minors as $minor): ?>
                <tr>
                    <td><?php echo $minor['MinorName']; ?></td>
						<td>
                <?php if (!empty($minor['Prerequisites'])): ?>
                    <ul>
                        <?php foreach ($minor['Prerequisites'] as $prerequisite): ?>
                            <li><?php echo $prerequisite; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    No prerequisites
                <?php endif; ?>
					  </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
        
        <section>
            <h2>Course Offerings</h2>
            <table>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
						<th>Course Prerequisites</th>
						<th>Credits</th>
                    <th>Description</th>
						<th>Course Type</th>
                </tr>
                <?php foreach ($Courses as $course): ?>
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
                    No prerequisites
                <?php endif; ?>
					  </td>
					   <td><?php echo $course['Credits']; ?></td>
                    <td><?php echo $course['Description']; ?></td>
					   <td><?php echo $course['CourseType']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <footer>
    <script>
    function goBack() {
        window.history.back();
    }
</script>
    </footer>
    </footer>
</body>
</html>