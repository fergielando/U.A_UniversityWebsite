<?php
// Include your database connection and other necessary files
@include 'config1.php';

session_start();

// Check if the user is an admin, if not, redirect to the admin login page or show an error message
if (!isset($_SESSION['admin_name'])) {
    // Redirect or display an error message here
}

// Handle account management actions
if (isset($_POST['reset_login'])) {
    $uid_to_reset = mysqli_real_escape_string($conn, $_POST['uid_to_reset']);

    // Reset the NumOfLogin value for the user
    $reset_login_query = "UPDATE logintable SET NumOfLogin = 0 WHERE UID = '$uid_to_reset'";
    mysqli_query($conn, $reset_login_query);

    // Redirect or display a success message
}

if (isset($_POST['unlock_account'])) {
    $uid_to_unlock = mysqli_real_escape_string($conn, $_POST['uid_to_unlock']);

    // Unlock the user account
    $unlock_account_query = "UPDATE logintable SET NumOfLogin = 0 WHERE UID = '$uid_to_unlock'";
    mysqli_query($conn, $unlock_account_query);

    // Redirect or display a success message
}

// Fetch and display a list of locked or flagged accounts
$locked_accounts_query = "SELECT * FROM logintable WHERE NumOfLogin > 0";
$locked_accounts_result = mysqli_query($conn, $locked_accounts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add your HTML head content here -->
</head>
<body>
    <h1>Admin Panel: Manage User Accounts</h1>

    <h2>Locked or Flagged Accounts</h2>
    <table>
        <tr>
            <th>User ID</th>
            <th>Email</th>
            <th>NumOfLogin</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_array($locked_accounts_result)) : ?>
            <tr>
                <td><?php echo $row['UID']; ?></td>
                <td><?php echo $row['Email']; ?></td>
                <td><?php echo $row['NumOfLogin']; ?></td>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="uid_to_reset" value="<?php echo $row['UID']; ?>">
                        <input type="submit" name="reset_login" value="Reset Login Attempts">
                    </form>
                    <form method="post" action="">
                        <input type="hidden" name="uid_to_unlock" value="<?php echo $row['UID']; ?>">
                        <input type="submit" name="unlock_account" value="Unlock Account">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    
    <!-- Add other content or navigation links as needed -->
</body>
</html>