<?php
@include 'config1.php';

// Retrieve major data from the database
$query = "SELECT * FROM major WHERE MajorID <> 0";
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



// Retrieve major prerequisites from the database
$prerequisiteQuery = "SELECT * FROM majorprerequisite";
$prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
$majorPrerequisites = [];

while ($prerequisiteRow = mysqli_fetch_assoc($prerequisiteResult)) {
    $majorPrerequisites[$prerequisiteRow['MajorID']][] = $prerequisiteRow;
}

// Retrieve course names associated with PRmajorID
$courseQuery = "SELECT c.CourseID, c.CourseName
                FROM course c
                JOIN majorprerequisite mp ON FIND_IN_SET(c.CourseID, mp.PRmajorID) > 0";
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get all "Show Prerequisites" buttons
        var showButtons = document.querySelectorAll(".show-prerequisites-button");

        // Add click event listeners to each button
        showButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var majorID = this.getAttribute("data-majorid");
                var prerequisitesRow = document.getElementById("prerequisites-" + majorID);

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
      <h1>Majors</h1>
      <button class="back-button" onclick="goBack()">Back</button>

   </div>

   <div class="major-container">
      <h2>List of Majors</h2>
      <table>
      <tr>
    <th>Major ID</th>
    <th>Major Name</th>
    <th>Dept ID</th>
    <th>Actions</th>
</tr>
<?php foreach ($majors as $major) : ?>
    <tr>
        <td><?php echo $major['MajorID']; ?></td>
        <td><?php echo $major['MajorName']; ?></td>
        <td><?php echo $major['DeptID']; ?></td>
        <td>
            <button class="show-prerequisites-button" data-majorid="<?php echo $major['MajorID']; ?>">Show Prerequisites</button>
        </td>
    </tr>
    <!-- Prerequisites Section (Initially Hidden) -->
    <tr class="prerequisites-row" id="prerequisites-<?php echo $major['MajorID']; ?>" style="display: none;">
        <td colspan="4">
            <div class="prerequisites-content">
                <table>
                    <tr>
                        <th>PRmajorID</th>
                        <th>Course Name</th>
                        <th>Min Grade</th>
                    </tr>
                    <?php
                    if (isset($majorPrerequisites[$major['MajorID']])) {
                        foreach ($majorPrerequisites[$major['MajorID']] as $prerequisite) {
                            $courseIDs = explode(',', $prerequisite['PRmajorID']);
                            $minGrade = $prerequisite['MinGrade'];
                            
                            foreach ($courseIDs as $courseID) {
                                if (isset($courseNames[$courseID])) {
                                    echo "<tr>";
                                    echo "<td>" . $prerequisite['PRmajorID'] . "</td>";
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