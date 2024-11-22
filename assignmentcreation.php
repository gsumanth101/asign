<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);
            color: #333;
        }

        h2 {
            text-align: center;
            color: #007BFF;
            margin: 30px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        form {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            margin: 20px auto;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        input[type="text"],
        textarea,
        input[type="date"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        input[type="date"]:focus,
        input[type="file"]:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        a {
            color: #007BFF;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #0056b3;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            form {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <h2>Create Assignment</h2>

    <form action="assignmentcreation.php" method="post" enctype="multipart/form-data">
        <label for="assignment_title">Assignment Title:</label>
        <input type="text" id="assignment_title" name="assignment_title" required>

        <label for="assignment_description">Assignment Description:</label>
        <textarea id="assignment_description" name="assignment_description" required></textarea>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" required>

        <label for="assignment_file">Upload Assignment File:</label>
        <input type="file" id="assignment_file" name="assignment_file">

        <input type="submit" value="Create Assignment">
    </form>

    <?php
    // Include the database connection file
    include 'connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $title = $_POST["assignment_title"];
        $description = $_POST["assignment_description"];
        $due_date = $_POST["due_date"];
        $file = $_FILES["assignment_file"];

        // Insert assignment details into the database
        $sql = "INSERT INTO assignments (title, description, due_date, file_path) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $title, $description, $due_date, $file_path);

        // Handle file upload and store the file path
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["assignment_file"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["assignment_file"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx"
            && $imageFileType != "txt" && $imageFileType != "zip") {
            echo "Sorry, only PDF, DOC, DOCX, TXT, and ZIP files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
                mysqli_stmt_execute($stmt);
                echo "The file ". htmlspecialchars(basename($_FILES["assignment_file"]["name"])). " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
    ?>
</body>
</html>