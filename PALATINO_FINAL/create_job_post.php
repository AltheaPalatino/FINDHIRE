//Functions 
<?php
session_start();
include('core/dbConfig.php');


if ($_SESSION['role'] != 'hr') {
    header("Location: index.php");
    exit();
}


$stmt = $conn->prepare("SELECT * FROM job_posts WHERE hr_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$job_posts = $result->fetch_all(MYSQLI_ASSOC);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO job_posts (title, description, hr_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $_SESSION['user_id']);
    $stmt->execute();


    header("Location: index.php");
    exit();
}
?>

<! -- HTML CODES --> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Post</title>
    <link rel="stylesheet" href="styles/create_job_post.css">
</head>
<body>

<header>
    <h1>Create Job Post</h1>
</header>
<a href="index.php" class="btn btn-primary">Back to Homepage</a>

<div class="container">
    <form method="POST">
        <label for="title">Job Title:</label>
        <input type="text" id="title" name="title" required><br>
        
        <label for="description">Job Description:</label>
        <textarea id="description" name="description" required></textarea><br>
        
        <button type="submit">Post Job</button>
    </form>

    <hr>

    <div class="job-posts-board">
        <h2>Posted Jobs</h2>
        <?php if (empty($job_posts)): ?>
            <p>No job posts available at the moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($job_posts as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                            <td><?php echo htmlspecialchars($job['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
