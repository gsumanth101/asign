<?php
// Include the database connection file
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['assignment_id'];
    $submissionFile = $_FILES['submission_file'];

    // Handle file upload and store the file path
    $targetDir = "uploads/submissions/";
    $targetFile = $targetDir . basename($submissionFile['name']);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validate file type and size
    $allowedExtensions = array('pdf', 'doc', 'docx', 'txt', 'zip');
    if (!in_array($fileType, $allowedExtensions)) {
        echo "<p class='error'>Sorry, only PDF, DOC, DOCX, TXT, and ZIP files are allowed.</p>";
        $uploadOk = 0;
    }

    if ($submissionFile['size'] > 500000) {
        echo "<p class='error'>Sorry, your file is too large.</p>";
        $uploadOk = 0;
    }

    if (file_exists($targetFile)) {
        echo "<p class='error'>Sorry, file already exists.</p>";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "<p class='error'>Sorry, your file was not uploaded.</p>";
    } else {
        if (move_uploaded_file($submissionFile['tmp_name'], $targetFile)) {
            $filePath = $targetFile;

            // Insert submission into the database
            $sql = "INSERT INTO submissions (assignment_id, student_id, submission_file_path) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iis", $assignmentId, $userId, $filePath); // Replace $userId with the actual user ID

            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>Submission successful! Let's wait for your grade.</p>";
            } else {
                echo "<p class='error'>Error submitting assignment: " . mysqli_error($conn) . "</p>";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "<p class='error'>Error uploading file: " . error_get_last()['message'] . "</p>";
        }
    }
}

// Function to display submitted files and their grades
function displaySubmissions($conn, $assignmentId) {
    $sql = "SELECT id, submission_file_path, grade FROM submissions WHERE assignment_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $assignmentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "<h2>Submitted Files</h2>";
        echo "<table class='styled-table'><thead><tr><th>File</th><th>Grade</th><th>Action</th></tr></thead><tbody>";
        
        $basePath = 'http://localhost/new/eyebook_update/views/faculty/'; // Define the base path for grading link
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td><a href='" . htmlspecialchars($row['submission_file_path']) . "' target='_blank'>" . basename($row['submission_file_path']) . "</a></td>
                    <td>" . htmlspecialchars($row['grade'] ?? '-') . "</td>
                    <td><a class='btn-grade' href='" . $basePath . "gradeassign.php?submission_id=" . $row['id'] . "'>Grade</a></td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No submissions found for this assignment.</p>";
    }
}

// Call the function to display submissions
if (isset($assignmentId)) {
    displaySubmissions($conn, $assignmentId);
}

mysqli_close($conn);
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 20px;
    }

    .error {
        color: #d9534f;
        background-color: #f2dede;
        padding: 10px;
        border: 1px solid #ebcccc;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .success {
        color: #3c763d;
        background-color: #dff0d8;
        padding: 10px;
        border: 1px solid #d6e9c6;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 18px;
        background-color: #fff;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: left;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
        border: 1px solid #dddddd;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    .btn-grade {
        text-decoration: none;
        color: #fff;
        background-color: #009879;
        padding: 8px 12px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .btn-grade:hover {
        background-color: #007a63;
    }
</style>
