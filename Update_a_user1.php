<?php
@include 'config1.php';

// Function to retrieve users from the database with filters
function getUsers($conn, $filters = []) {
   $query = "SELECT u.*, l.UserType FROM user u LEFT JOIN logintable l ON u.UID = l.UID WHERE 1=1 && u.UID > 0";

   foreach ($filters as $key => $value) {
       if (!empty($value)) {
           $value = mysqli_real_escape_string($conn, $value);
           if ($key === 'UserType') {
               // Filter by UserType
               $query .= " AND l.UserType = '$value'";
           } else {
               // Filter by other fields
               $query .= " AND u.$key LIKE '%$value%'";
           }
       }
   }

   $result = mysqli_query($conn, $query);
   return mysqli_fetch_all($result, MYSQLI_ASSOC);
}



// Handle filter request
$filters = [];
if (isset($_POST['filter'])) {
   $filters['UID'] = $_POST['UID'] ?? '';
    $filters['FirstName'] = $_POST['FirstName'] ?? '';
    $filters['LastName'] = $_POST['LastName'] ?? '';
    $filters['Gender'] = $_POST['Gender'] ?? '';
    $filters['City'] = $_POST['City'] ?? '';
    $filters['UserType'] = $_POST['UserType'] ?? '';
}

$users = getUsers($conn, $filters); // Get filtered users

// Search for a user by UID
if (isset($_POST['search'])) {
    $uid = mysqli_real_escape_string($conn, $_POST['search_uid']);
    $users = getUsers($conn, $uid); // Get users that match the UID
}

// Update user information
if (isset($_POST['submit'])) {
   
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $zipcode = mysqli_real_escape_string($conn, $_POST['zipcode']);
    

    
    $updateUserQuery = "UPDATE user SET firstname='$firstName', lastname='$lastName', gender='$gender', dob='$dob', street='$street', city='$city', state='$state', zipcode='$zipcode' WHERE uid='$uid'";
    mysqli_query($conn, $updateUserQuery);

   

   
    header('location:admin_page1.php');
}

if (isset($_GET['uid'])) { 
    $uid = mysqli_real_escape_string($conn, $_GET['uid']);
    $getUserQuery = "SELECT * FROM user WHERE uid='$uid'";
    $result = mysqli_query($conn, $getUserQuery);
    $user = mysqli_fetch_assoc($result);
}
?>
<style>
.button-container {
   background-color: #000; /* Black background for the container */
   padding: 10px;
   text-align: center;
}

.button-container .btn {
   background-color: transparent; /* Transparent background for buttons */
   color: #fff; /* White text */
   padding: 10px 20px;
   margin: 5px;
   border: 2px solid #fff; /* White border */
   border-radius: 5px;
   text-decoration: none; /* Remove underline from links */
   font-size: 16px;
   transition: background-color 0.3s, color 0.3s; /* Smooth transition for hover effect */
}

.button-container .btn:hover {
   background-color: #90ee90; /* Light green background on hover */
   color: #000; /* Black text on hover */
}
      .header {
         background: #000; 
         color: #fff; 
         padding: 20px;
         background-color: #000;
         text-align: left;
         margin-top: 20px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .header h1 {
         font-size: 36px; 
         margin: 0;
      }

      .header .logo {
         width: 50px;
         height: 50px;
      }

      .welcome-statement {
         color: #333;
         font-size: 18px;
         padding: 40px;
         text-align: center;
         font-family: 'Poppins', cursive; 
         border: 2px solid #444; 
         box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); 
      }

      table {
         width: 100%; /* Full width */
         max-width: 100%; /* Ensures table is not wider than its container */
         border-collapse: collapse;
         table-layout: auto; /* New line: Ensures the table respects the width */
      }

      th, td {
         border: 1px solid #000;
         padding: 15px;
         text-align: left;
         font-size: 14px;
         word-wrap: break-word; /* New line: Allows words to break and wrap */
      }

      th {
         background-color: #f2f2f2; /* Gives a slight background color to the header */
      }

      /* Style for every other row */
      tr:nth-child(even) {
         background-color: #ccffcc; /* Light green background */
      }

      /* Hover effect for rows */
      tr:hover {
         background-color: #e6ffe6; /* Lighter green on hover */
      }

      td, th {
         padding: 10px;
         border: 1px solid #ccc;
         text-align: center;
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
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="header">
   <h1>User Management</h1>
   <div class="button-container">
      <a href="admin_page1.php" class="btn">Back to Admin Page</a>
      <a href="create_a_user1.php" class="btn">Create a User</a>
      <a href="locked_out1.php" class="btn">Locked out Users</a>
      <a href="manage_holds.php" class="btn">Create Hold</a>
      <!-- Other buttons as needed -->
   </div>
</div>

  <!-- Filter Form -->
<div class="filter-container">
    <form action="" method="post">
        <input type="text" name="UID" placeholder="UID">
        <input type="text" name="FirstName" placeholder="First Name">
        <input type="text" name="LastName" placeholder="Last Name">
        <input type="text" name="Gender" placeholder="Gender">
        <input type="text" name="City" placeholder="City">
        
        <!-- Add UserType select input -->
        <select name="UserType">
            <option value="">All</option>
            <?php
            // Fetch distinct UserTypes from the database
            $userTypesQuery = "SELECT DISTINCT UserType FROM logintable";
            $userTypesResult = mysqli_query($conn, $userTypesQuery);

            while ($row = mysqli_fetch_assoc($userTypesResult)) {
                $selected = ($_POST['UserType'] ?? '') === $row['UserType'] ? 'selected' : '';
                echo '<option value="' . $row['UserType'] . '" ' . $selected . '>' . $row['UserType'] . '</option>';
            }
            ?>
        </select>

        <input type="submit" name="filter" value="Filter" class="form-btn">
    </form>
</div>



<!-- Users Table -->
<div class="user-table">
<table>
   <tr>
      <th>UID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Gender</th>
      <th>Date of Birth</th>
      <th>Street</th>
      <th>City</th>
      <th>State</th>
      <th>ZipCode</th>
      <th>Edit</th>
      <th>View Academic Profile</th>
     
   </tr>
   <?php foreach ($users as $user): ?>
   <tr>
      <td><?php echo htmlspecialchars($user['UID']); ?></td>
      <td><?php echo htmlspecialchars($user['FirstName']); ?></td>
      <td><?php echo htmlspecialchars($user['LastName']); ?></td>
      <td><?php echo htmlspecialchars($user['Gender']); ?></td>
      <td><?php echo htmlspecialchars($user['DOB']); ?></td>
      <td><?php echo htmlspecialchars($user['Street']); ?></td>
      <td><?php echo htmlspecialchars($user['City']); ?></td>
      <td><?php echo htmlspecialchars($user['State']); ?></td>
      <td><?php echo htmlspecialchars($user['ZipCode']); ?></td>
      <td><a href="edit_user.php?UID=<?php echo $user['UID']; ?>">Edit</a></td>
   <td>
       <?php
       if ($user['UserType'] == 'student') {
           // Link for viewing the academic profile
           echo '<a href="view_academic_profile1.php?UID=' . $user['UID'] . '">View Academic Profile</a>';
       }
	   elseif ($user['UserType'] == 'faculty') {
           // Link for viewing the academic profile
           echo '<a href="adminfaculty_personalinfo.php?UID=' . $user['UID'] . '">View Faculty Information</a>';
       }
	   elseif ($user['UserType'] == 'statsoffice') {
           // Link for viewing the academic profile
           echo '<a href="adminstatsoffice_personalinfo.php?UID=' . $user['UID'] . '">View Stats Office Information</a>';
       }
	   elseif ($user['UserType'] == 'admin') {
           // Link for viewing the academic profile
           echo '<a href="admin_personalinfo.php?UID=' . $user['UID'] . '">View Admin Information</a>';
       }
       ?>
   </td>





</tr>
<?php
endforeach;
?>
</table>

</div>

<!-- Your existing scripts and footer here -->

</body>
</html>


