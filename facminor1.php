<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
@include 'config1.php';

// Retrieve minor data from the database
$query = "SELECT * FROM minor";
$result = mysqli_query($conn, $query);
$minors = [];

// Initialize a counter for assigning new sequential IDs
$counter = 1;

while ($row = mysqli_fetch_assoc($result)) {
   $row['MinorID'] = $counter;
    $minors[] = $row;

     // Increment the counter for the next major
     $counter++;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE-edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Minors Page</title>

   <link rel="stylesheet" href="css/fatman1.css">

   <style>
      .header {
         background: #000;
         color: #fff;
         padding: 20px;
         text-align: center;
         display: flex;
         justify-content: space-between;
      }

      .header h1 {
         font-size: 36px;
      }

      .header .back-button {
         background: #000;
         color: #fff;
         padding: 10px 20px;
         text-decoration: none;
         border-radius: 5px;
         margin-right: 10px;
      }

      .minor-container {
         padding: 20px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
      }

      table, th, td {
         border: 1px solid #000;
      }

      th, td {
         padding: 8px;
         text-align: left;
      }

      .edit-button {
         background: #000;
         color: #fff;
         padding: 10px 20px;
         text-decoration: none;
         border-radius: 5px;
         margin: 5px;
         display: inline-block;
      }
      /* Style for every other row */
   tr:nth-child(even) {
      background-color: #ccffcc; /* Light green background */
   }
   </style>
</head>
<body>

   <div class="header">
      <h1>Minors</h1>
      <a href="fac_newhome1.php" class="back-button">Back</a>
   </div>

   <div class="minor-container">
      <h2>List of Minors</h2>
      <table>
         <tr>
            <th>Minor ID</th>
            <th>Minor Name</th>
            <th>Dept ID</th>
         </tr>
         <?php foreach ($minors as $minor) : ?>
            <tr>
               <td><?php echo $minor['MinorID']; ?></td>
               <td><?php echo $minor['MinorName']; ?></td>
               <td><?php echo $minor['DeptID']; ?></td>
               </td>
            </tr>
         <?php endforeach; ?>
      </table>
   </div>
   
   <!-- Add any other elements, forms, or buttons for adding minors as needed -->

</body>
</html>
