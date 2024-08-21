<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
@include 'config1.php';

// Retrieve minor data from the database
$query = "SELECT * FROM minor WHERE MinorID <> 0";
$result = mysqli_query($conn, $query);
$minors = [];

// Initialize a counter for assigning new sequential IDs
$counter = 1;

while ($row = mysqli_fetch_assoc($result)) {
    $row['MinorID'] = $counter;
    $minors[] = $row;

    // Increment the counter for the next minor
    $counter++;
}

// Retrieve major prerequisites from the database
$prerequisiteQuery = "SELECT * FROM minorprerequisite";
$prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
$minorrPrerequisites = [];

while ($prerequisiteRow = mysqli_fetch_assoc($prerequisiteResult)) {
    $minorPrerequisites[$prerequisiteRow['MinorID']][] = $prerequisiteRow;
}

// Retrieve course names associated with PRmajorID
$courseQuery = "SELECT c.CourseID, c.CourseName
                FROM course c
                JOIN minorprerequisite mp ON FIND_IN_SET(c.CourseID, mp.PRminorID) > 0";
$courseResult = mysqli_query($conn, $courseQuery);
$courseNames = [];

while ($courseRow = mysqli_fetch_assoc($courseResult)) {
    $courseNames[$courseRow['CourseID']] = $courseRow['CourseName'];
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get all "Show Prerequisites" buttons
        var showButtons = document.querySelectorAll(".show-prerequisites-button");

        // Add click event listeners to each button
        showButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var minorID = this.getAttribute("data-minorid");
                var prerequisitesRow = document.getElementById("prerequisites-" + minorID);

                // Toggle the display of prerequisites section
                if (prerequisitesRow.style.display === "none" || prerequisitesRow.style.display === "") {
                    prerequisitesRow.style.display = "table-row";
                } else {
                    prerequisitesRow.style.display = "none";
                }
            });
        });
    });

    function goBack() {
        window.history.back();
    }
</script>

   <div class="header">
      <h1>Minors</h1>
      <a href="index.php" class="back-button">Back to Home Page</a>
   </div>

   <div class="minor-container">
      <h2>List of Minors</h2>
      <table>
         <tr>
            <th>Minor ID</th>
            <th>Minor Name</th>
            <th>Dept ID</th>
            <th>Actions</th>
         </tr>
         <?php foreach ($minors as $minor) : ?>
            <tr>
               <td><?php echo $minor['MinorID']; ?></td>
               <td><?php echo $minor['MinorName']; ?></td>
               <td><?php echo $minor['DeptID']; ?></td>
               <td>
                   <button class="show-prerequisites-button" data-minorid="<?php echo $minor['MinorID']; ?>">Show Prerequisites</button>
               </td>
            </tr>
            <!-- Prerequisites Section (Initially Hidden) -->
            <tr class="prerequisites-row" id="prerequisites-<?php echo $minor['MinorID']; ?>" style="display: none;">
                <td colspan="4">
                    <div class="prerequisites-content">
                    <table>
                    <tr>
                        <th>PRminorID</th>
                        <th>Course Name</th>
                        <th>Min Grade</th>
                    </tr>
                    <?php
                    if (isset($minorPrerequisites[$minor['MinorID']])) {
                        foreach ($minorPrerequisites[$minor['MinorID']] as $prerequisite) {
                            $courseIDs = explode(',', $prerequisite['PRminorID']);
                            $minGrade = $prerequisite['MinGrade'];
                            
                            foreach ($courseIDs as $courseID) {
                                if (isset($courseNames[$courseID])) {
                                    echo "<tr>";
                                    echo "<td>" . $prerequisite['PRminorID'] . "</td>";
                                    echo "<td>" . $courseNames[$courseID] . "</td>";
                                    echo "<td>" . $minGrade . "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                    } else {
                        echo "<tr><td colspan='4'>No prerequisites</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </td>
    </tr>
<?php endforeach; ?>

</body>
</html>
