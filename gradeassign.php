<?php
// Include the database connection file
include 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch the submission details
if (isset($_GET['submission_id'])) {
    $submissionId = $_GET['submission_id'];

    // Check if the submission exists
    $sql = "SELECT id FROM submissions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "i", $submissionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!mysqli_fetch_assoc($result)) {
        echo "<p class='error'>Submission not found.</p>";
        exit;
    }
}

// Handle grade assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data for marks and letter grade
    $marks = $_POST['marks'];
    $letterGrade = $_POST['letter_grade'];

    // Validate the input fields
    if (empty($marks) || empty($letterGrade)) {
        echo "<p class='error'>Both marks and letter grade are required.</p>";
    } elseif (!is_numeric($marks)) {
        echo "<p class='error'>Marks must be a numeric value.</p>";
    } elseif (strlen($letterGrade) > 2 || !preg_match('/^[a-zA-Z]$/', $letterGrade)) {
        echo "<p class='error'>Letter grade must be one or two alphabetic characters.</p>";
    } else {
        // Convert the letter grade to uppercase for consistency
        $letterGrade = strtoupper($letterGrade);

        // Prepare and execute the SQL to insert the marks and letter grade into the grading table
        $sql = "INSERT INTO grading (submission_id, marks, letter_grade) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            die('MySQL prepare error: ' . mysqli_error($conn));
        }

        // Bind parameters and insert the grade
        mysqli_stmt_bind_param($stmt, "ids", $submissionId, $marks, $letterGrade);

        // Execute the query and check for errors
        if (mysqli_stmt_execute($stmt)) {
            echo "<p class='success'>Grade assigned successfully!</p>";
        } else {
            echo "<p class='error'>Error assigning grade: " . mysqli_error($conn) . "</p>";
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Assignment</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        h1 {
            background-color: #007bff;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        form {
            padding: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.2s ease;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #007bff;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #d9534f;
            font-weight: bold;
            text-align: center;
        }
        .success {
            color: #5cb85c;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Grade Assignment</h1>

    <!-- Grade Assignment Form -->
    <form action="gradeassign.php?submission_id=<?php echo $submissionId; ?>" method="POST">
        <label for="marks">Assign Marks</label>
        <input type="number" step="0.01" name="marks" id="marks" required placeholder="Enter numeric marks" min="0">

        <label for="letter_grade">Assign Letter Grade</label>
        <input type="text" name="letter_grade" id="letter_grade" required placeholder="Enter letter grade (e.g., A, b, C)" maxlength="2">

        <button type="submit">Assign Grade</button>
    </form>
</div>

</body>
</html>
