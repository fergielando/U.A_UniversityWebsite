<?php
@include 'config1.php';

// Retrieve major data from the database
$query = "SELECT * FROM major";
$result = mysqli_query($conn, $query);
$majors = [];

// Initialize a counter for assigning new sequential IDs
$counter = 1;

while ($row = mysqli_fetch_assoc($result)) {
    $row['MajorID'] = $counter;
    $majors[] = $row;

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
   <title>Majors Page</title>

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

      .major-container {
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
      <h1>Majors</h1>
      <a href="fac_newhome1.php" class="back-button">Back</a>
   </div>

   <div class="major-container">
      <h2>List of Majors</h2>
      <table>
         <tr>
            <th>Major ID</th>
            <th>Major Name</th>
            <th>Dept ID</th>
         </tr>
         <?php foreach ($majors as $major) : ?>
            <tr>
               <td><?php echo $major['MajorID']; ?></td>
               <td><?php echo $major['MajorName']; ?></td>
               <td><?php echo $major['DeptID']; ?></td>
               <td>
               </td>
            </tr>
         <?php endforeach; ?>
      </table>
   </div>
   
   <!-- Add any other elements, forms, or buttons for adding majors as needed -->

</body>
</html>
