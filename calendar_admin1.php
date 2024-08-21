<?php
// Include the database connection file
error_reporting(E_ALL);
ini_set('display_errors', 1);
@include 'config1.php';



// Retrieve events from the database
$query = "SELECT * FROM events";
$result = mysqli_query($conn, $query);
$events = [];

while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Calendar</title>
   <link rel="stylesheet" href="css/fatman1.css">
   <style>
      /* Add your CSS styles for the calendar here */
   </style>
</head>
<body>

   <h1>Admin Calendar</h1>

   <!-- Form for adding or editing events -->
   <form method="post" action="">
      <!-- Add input fields for event details (e.g., title, date, time) -->
      <!-- Add submit button -->
   </form>

   <!-- Display the list of events -->
   <h2>Events</h2>
   <ul>
      <?php foreach ($events as $event) : ?>
         <li>
            <?php echo $event['title']; ?> - <?php echo $event['date']; ?> <?php echo $event['time']; ?>
            <!-- Add edit and delete buttons -->
         </li>
      <?php endforeach; ?>
   </ul>

   <script>
      // Add JavaScript code for handling client-side interactions (e.g., form validation)
   </script>

</body>
</html>
