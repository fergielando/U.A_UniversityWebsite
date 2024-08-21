<?php
// Include your database connection and other necessary files
@include 'config1.php';

session_start();

// Function to generate a random temporary password
function generateRandomPassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);

    $select = "SELECT logintable.*, user.FirstName FROM logintable JOIN user ON logintable.UID = user.UID WHERE Email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
			if ($row['NumOfLogin'] >= 3) {
            // Account is locked
				$error[] = 'Account locked. Please contact the administrator.';
				$updateLockedOut = "UPDATE logintable SET LockedOut = 1 WHERE UID = '{$row['UID']}'";
				mysqli_query($conn, $updateLockedOut);

            // After locking the account, send an email with a temporary password
				if ($row['NumOfLogin'] >= 2) {
					$temporaryPassword = generateRandomPassword(); // Generate a random temporary password
					$hashedTempPassword = md5($temporaryPassword); // Hash the temporary password

                // Update the user's password in the database with the temporary password
					$updateTempPassword = "UPDATE logintable SET Password = '$hashedTempPassword' WHERE UID = '{$row['UID']}'";
					mysqli_query($conn, $updateTempPassword);

                // Send the temporary password to the user's email
					$to = 'admin@uauniversityproject.online'; // User's email address
					$subject = "$email Locked Out";
					$msg = "$email has been locked due to multiple failed login attempts. Here is their temporary password: $temporaryPassword";

                // You may need to configure your SMTP settings for the mail() function to work correctly
					mail($to, $subject, $msg);
            }
        } else {
            if ($row['Password'] == $password) {
                // Successful login, reset failed attempts
                $updateFailedAttempts = "UPDATE logintable SET NumOfLogin = 0 WHERE UID = '{$row['UID']}'";
                mysqli_query($conn, $updateFailedAttempts);

                // Handle different user types
                if ($row['UserType'] == 'admin') {
                    $_SESSION['admin_name'] = $row['FirstName'];
                    $_SESSION['UID'] = $row['UID'];
                    header('location: admin_page1.php');
                } elseif ($row['UserType'] == 'student') {
                    $_SESSION['user_name'] = $row['FirstName'];
                    $_SESSION['UID'] = $row['UID'];
                    header('location: user_page1.php');
                } elseif ($row['UserType'] == 'faculty') {
                    $_SESSION['faculty_name'] = $row['FirstName'];
                    $_SESSION['UID'] = $row['UID'];
                    header('location: fac_newhome1.php');
                } elseif ($row['UserType'] == 'statsoffice') {
                    $_SESSION['statsoffice_name'] = $row['FirstName'];
                    $_SESSION['UID'] = $row['UID'];
                    header('location: statsoffice_page1.php');
                }
            } else {
                // Failed login attempt, increment login attempts
				if ( !($row['UserType'] == 'admin')) {
                $updateFailedAttempts = "UPDATE logintable SET NumOfLogin = NumOfLogin + 1 WHERE UID = '{$row['UID']}'";
                mysqli_query($conn, $updateFailedAttempts);

                $error[] = 'Incorrect email or password!';

                if ($row['NumOfLogin'] >= 2) {
                    // Lock the account after 3 failed attempts
                    $updateLockAccount = "UPDATE logintable SET NumOfLogin = 3 WHERE UID = '{$row['UID']}'";
                    mysqli_query($conn, $updateLockAccount);
					$updateLockedOut2 = "UPDATE logintable SET LockedOut = 1 WHERE UID = '{$row['UID']}'";
					mysqli_query($conn, $updateLockedOut2);
                }
            } else {
				$error[] = 'Incorrect email or password!';
				}
		}
       }
    } else {
        $error[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login form</title>
    <link rel="stylesheet" href="css/fatman1.css">
    <style>
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
      body {
            background-color: #c2e8c1;
        }
    </style>
</head>
<body>
<div class="header">
        <img src="ua.png" alt="U.A. Logo" class="logo">
        <h1> U.A. University</h1>
    </div>
<div class="form-container">
    <form action="" method="post">
        <h3>Login</h3>
        <?php
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<span class="error-msg">' . $error . '</span>';
            }
        }
        ?>
        <input type="email" name="email" required placeholder="Email">
        <input type="password" name="password" required placeholder="Password">
        <input type="submit" name="submit" value="Login now" class="form-btn">
        <p>Don't have an account? <a href="register_form1.php">Register now</a></p>
    </form>
</div>
</body>
</html>
