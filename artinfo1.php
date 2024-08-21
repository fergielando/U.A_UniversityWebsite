<?php
@include 'config1.php';

// Fetching art department details
$artDeptQuery = "SELECT * FROM dept WHERE DeptID = 'ART'";
$artDeptResult = mysqli_query($conn, $artDeptQuery);
$artDeptDetails = mysqli_fetch_assoc($artDeptResult);

// Fetching art courses
$artCoursesQuery = "SELECT c.*, GROUP_CONCAT(cp.PRCourseID) AS Prerequisites
                    FROM course c
                    LEFT JOIN courseprerequisite cp ON c.CourseID = cp.CourseID
                    WHERE c.DeptID = 'ART'
                    GROUP BY c.CourseID";
$artCoursesResult = mysqli_query($conn, $artCoursesQuery);
$artCourses = [];
while ($course = mysqli_fetch_assoc($artCoursesResult)) {
	$course['Prerequisites'] = $course['Prerequisites'] ? explode(',', $course['Prerequisites']) : [];
    $artCourses[] = $course;
}

//Fetching Department Email
$deptemailQuery = "SELECT Email FROM dept WHERE DeptID = 'ART'";
$deptemailResult = mysqli_query($conn, $deptemailQuery);
$deptemailDetails = mysqli_fetch_assoc($deptemailResult);

//Fetching Department Phone
$deptphoneQuery = "SELECT PhoneNum FROM dept WHERE DeptID = 'ART'";
$deptphoneResult = mysqli_query($conn, $deptphoneQuery);
$deptphoneDetails = mysqli_fetch_assoc($deptphoneResult);

//Fetching Department Office
$deptroomQuery = "SELECT RoomID FROM dept WHERE DeptID = 'ART'";
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
WHERE DeptID = 'ART'";
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
WHERE DeptID = 'ART'";
$deptmanagerResult = mysqli_query($conn, $deptmanagerQuery);
$deptmanagerDetails = mysqli_fetch_assoc($deptmanagerResult);

$facultyQuery = "SELECT 
    f.FacultyID, 
    f.FacultyType, 
    u.FirstName AS FacultyFirstName, 
    u.LastName AS FacultyLastName, 
    f.Position AS Position, 
    f.Specialty AS Specialty, 
    lt.Email AS FacultyEmail,
    COALESCE(fft.OfficeID, fpt.OfficeID) AS OfficeID
FROM faculty f
JOIN facultydept fd ON f.FacultyID = fd.FacultyID
JOIN user u ON f.FacultyID = u.UID
JOIN logintable lt ON u.UID = lt.UID
LEFT JOIN facultyft fft ON f.FacultyID = fft.FacultyID
LEFT JOIN facultypt fpt ON f.FacultyID = fpt.FacultyID
WHERE fd.DeptID = 'ART'";

$facultyResult = mysqli_query($conn, $facultyQuery);
$facultyList = [];
while ($faculty = mysqli_fetch_assoc($facultyResult)) {
    $facultyList[] = $faculty;
}

//Fetching Department Majors
$artMajorsQuery = "SELECT m.*, GROUP_CONCAT(mp.PRmajorID) AS Prerequisites
                    FROM major m
                    LEFT JOIN majorprerequisite mp ON m.MajorID = mp.MajorID
                    WHERE m.DeptID = 'ART'
                    GROUP BY m.MajorID";
$artMajorsResult = mysqli_query($conn, $artMajorsQuery);
$artMajors = [];
while ($major = mysqli_fetch_assoc($artMajorsResult)) {
	$major['Prerequisites'] = $major['Prerequisites'] ? explode(',', $major['Prerequisites']) : [];
    $artMajors[] = $major;
}

//Fetching Department Minors
$artMinorsQuery = "SELECT m.*, GROUP_CONCAT(mp.PRminorID) AS Prerequisites
                    FROM minor m
                    LEFT JOIN minorprerequisite mp ON m.MinorID = mp.MinorID
                    WHERE m.DeptID = 'ART'
                    GROUP BY m.MinorID";
$artMinorsResult = mysqli_query($conn, $artMinorsQuery);
$artMinors = [];
while ($minor = mysqli_fetch_assoc($artMinorsResult)) {
	$minor['Prerequisites'] = $minor['Prerequisites'] ? explode(',', $minor['Prerequisites']) : [];
    $artMinors[] = $minor;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Department - UA University</title>
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
            <h1>Welcome to the <?php echo $artDeptDetails['DeptName']; ?> Department</h1>
            <button class="back-button" onclick="goBack()">Back</button>
        </div>
    </header>

    <main>
    <section>
            <h2>Message from the Chair and Manager - <?php echo $deptchairDetails['ChairFirstName'] . ' ' . $deptchairDetails['ChairLastName']; ?> - Chair and <?php echo $deptmanagerDetails['ManagerFirstName'] . ' ' . $deptmanagerDetails['ManagerLastName']; ?> - Manager</h2>
            <p>Welcome to the <?php echo $artDeptDetails['DeptName']; ?> Department at UA University! Our department fosters creativity and critical thinking in the visual arts, and is committed to excellence in nurturing and challenging the artistic skills of our students.</p>
             <ul>
                <li>Department Email: <a href="mailto:<?php echo $deptemailDetails['Email']; ?>"><?php echo $deptemailDetails['Email']; ?></a></li>
                <li>Department Phone: <?php echo $deptphoneDetails['PhoneNum']; ?></li>
				   <li>Department Office: <?php echo $deptroomDetails['RoomID']; ?></li>
            </ul>
            <p>Department Chair:</p>
            <ul>
                <li>Name: <?php echo $deptchairDetails['ChairFirstName'] . ' ' . $deptchairDetails['ChairLastName']; ?></li>
                <li>Email: <a href="mailto:<?php echo $deptchairDetails['ChairEmail']; ?>"><?php echo $deptchairDetails['ChairEmail']; ?></a></li>
                <li>Phone: (555) 234-5678</li>
            </ul>
            <p>Department Manager:</p>
            <ul>
                <li>Name: <?php echo $deptmanagerDetails['ManagerFirstName'] . ' ' . $deptmanagerDetails['ManagerLastName']; ?></li>
                <li>Email: <a href="mailto:<?php echo $deptmanagerDetails['ManagerEmail']; ?>"><?php echo $deptmanagerDetails['ManagerEmail']; ?></a></li>
                <li>Phone: (555) 123-4134</li>
            </ul>
        </section>

        <section>
        <h2>Faculty in the <?php echo $artDeptDetails['DeptName']; ?> Department</h2>
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
                <?php foreach ($artMajors as $major): ?>
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
                <?php foreach ($artMinors as $minor): ?>
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
                <?php foreach ($artCourses as $course): ?>
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
        <!-- Footer content can be added here -->
    </footer>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>