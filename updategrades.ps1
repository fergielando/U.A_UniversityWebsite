<?php
@include 'config1.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the CRN from the form
    $CRN = $_POST['CRN'];

    // Loop through the posted grade values and update the database
    foreach ($_POST as $key => $value) {
        // Check if the field name starts with "grade_"
        if (strpos($key, 'grade_') === 0) {
            // Extract student first and last name from the field name
            $fieldNameParts = explode('_', $key);
            if (count($fieldNameParts) === 3) {
                $studentFirstName = $fieldNameParts[1];
                $studentLastName = $fieldNameParts[2];

                // Update the grade in the database
                $updateQuery = "UPDATE enrollment
                                SET Grade = '$value'
                                WHERE CRN = '$CRN'
                                AND StudentID IN (
                                    SELECT UID
                                    FROM user
                                    WHERE FirstName = '$studentFirstName'
                                    AND LastName = '$studentLastName'
                                )";

                $updateResult = mysqli_query($conn, $updateQuery);

                if (!$updateResult) {
                    echo "Error updating grade for $studentFirstName $studentLastName.";
                }
            }
        }
    }

    // Redirect back to the class roster page after updating grades
    header("Location: classroster.php?CRN=" . urlencode($CRN));
} else {
    // If the request is not a POST request, redirect to the class roster page
    header("Location: classroster.php");
}
?>
