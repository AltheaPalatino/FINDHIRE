//Functions Code
<?php
session_start();
include('core/dbConfig.php');

$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SESSION['role'] != 'applicant') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resume = $_FILES['resume']['name'];
    $description = $_POST['description'];

    if ($_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $resume_path = $upload_dir . basename($resume);

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
            $stmt = $conn->prepare("INSERT INTO applications (job_post_id, applicant_id, resume, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss",$_GET['job_post_id'], $_SESSION['user_id'], $resume, $description);
            $stmt->execute();

            header("Location: index.php");
            exit();
        } else {
            echo "Failed to upload the resume. Please try again.";
        }
    } else {
        echo "Error uploading the resume.";
    }
}
?>

<! -- HTML CODES --> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link rel="stylesheet" href="styles/apply_job.css">
</head>
<body>

<header>
    <h1>Job Application</h1>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="message_hr.php">Messages</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Apply for a Job</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="description">Email:</label>
            <textarea name="description" id="description" required></textarea>
        </div>

        <div class="form-group">
            <label for="resume">Resume (PDF):</label>
            <input type="file" name="resume" id="resume" accept=".pdf" required>
        </div>

        <button type="submit">Apply</button>
    </div>
</div>

</body>
</html>