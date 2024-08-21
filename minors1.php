<?php
@include 'config1.php';

// Retrieve minor data from the database
$query = "SELECT * FROM minor";
$result = mysqli_query($conn, $query);
$minors = [];

// Initialize a counter for assigning new sequential IDs
// $counter = 0;

while ($row = mysqli_fetch_assoc($result)) {
    //  $row['minorID'] = $counter;
    $minors[] = $row;

    // Increment the counter for the next minor
    // $counter++;
}



// Retrieve minor prerequisites from the database
$prerequisiteQuery = "SELECT * FROM minorprerequisite";
$prerequisiteResult = mysqli_query($conn, $prerequisiteQuery);
$minorPrerequisites = [];

while ($prerequisiteRow = mysqli_fetch_assoc($prerequisiteResult)) {
    $minorPrerequisites[$prerequisiteRow['MinorID']][] = $prerequisiteRow;
}

// Retrieve course names associated with PRminorID
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
   <title>minors Page</title>

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
	  <a href="createminor.php" class="btn">Create a Minor</a>
      <a href="admin_page1.php" class="btn">Back</a>

   </div>

   <div class="major-container">
    <h2>List of Minors</h2>
    <table>
        <tr>
            <th>Minor ID</th>
            <th>Minor Name</th>
            <th>Dept ID</th>
            <th>Edit minor</th>
            <th>Show Prereqs</th>
            <th>Edit Prerequisites</th>
			  <th>Delete</th>
        </tr>
        <?php foreach ($minors as $minor) : ?>
				<?php
            // Check if any students are enrolled in the current minor
            $enrollment_query = "SELECT * FROM studentminor WHERE MinorID = '{$minor['MinorID']}'";
            $enrollment_result = mysqli_query($conn, $enrollment_query);

            // Hide the delete button if students are enrolled
            $disable_delete = mysqli_num_rows($enrollment_result) > 0;
				?>
            <tr>
                <td><?php echo $minor['MinorID']; ?></td>
                <td><?php echo $minor['MinorName']; ?></td>
                <td><?php echo $minor['DeptID']; ?></td>
                <td>
                    <a href="edit_minor1.php?id=<?php echo $minor['MinorID']; ?>">Edit minor</a>
                </td>
                <td>
                    <button class="show-prerequisites-button" data-minorid="<?php echo $minor['MinorID']; ?>">Show Prerequisites</button>
                </td>
                <td>
                    <a href="edit_minorpreq.php?minor_id=<?php echo $minor['MinorID']; ?>">Edit Prerequisites</a>
                </td>
				<td>
                    <?php if ($disable_delete) : ?>
                        Cannot delete
                    <?php else : ?>
                        <a href="delete_minor.php?id=<?php echo $minor['MinorID']; ?>">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- Prerequisites Section (Initially Hidden) -->
            <tr class="prerequisites-row" id="prerequisites-<?php echo $minor['MinorID']; ?>" style="display: none;">
                <td colspan="6">
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
                                echo "<tr><td colspan='3'>No prerequisites</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
