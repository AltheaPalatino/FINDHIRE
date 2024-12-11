<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; 
$user_id = $_SESSION['user_id'];

include('core/dbConfig.php');

$chat_users = [];
if ($role == 'hr') {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'applicant'");
} else {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'hr'");
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chat_users[] = $row;
}

$job_posts = [];
if ($role == 'applicant') {
    $stmt = $conn->prepare("SELECT a.status, j.title FROM applications a 
                            LEFT JOIN job_posts j ON a.job_post_id = j.id 
                            WHERE a.applicant_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $application_status = $row['status']; 
        if ($application_status == 'accepted') {
            $accepted_job_title = $row['title'];
        }
    }

    $stmt = $conn->prepare("SELECT id, title, description FROM job_posts");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $job_posts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>

<header>
    <h1>FINDHIRE</h1>
</header>

<nav>
    <?php if ($role == 'hr'): ?>
        <a href="create_job_post.php">Post a Job</a>
        <a href="view_applications.php">Applications</a>
        <a href="view_messages.php">Messages</a>
    <?php elseif ($role == 'applicant'): ?>
        <a href="message_hr.php">Messages</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <div class="role-message">
        <?php if ($role == 'hr'): ?>
            <p>You are logged in as HR.</p>
        <?php elseif ($role == 'applicant'): ?>
            <p>You are logged in as an Applicant.</p>
        <?php endif; ?>
    </div>

    <?php if ($role == 'applicant'): ?>
    <div class="job-posts-board">
        <h2>Available Job Posts</h2>
        <?php if (empty($job_posts)): ?>
            <p>No job posts available at the moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Description</th>
                        <th>Apply</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($job_posts as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                            <td><?php echo htmlspecialchars($job['description']); ?></td>
                            <td><a href="apply_job.php?job_post_id=<?php echo $job['id']; ?>">Apply</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($role == 'applicant'): ?>
    <div class="accepted-jobs">
        <?php
            $stmt = $conn->prepare("SELECT j.title FROM applications a 
                                    LEFT JOIN job_posts j ON a.job_post_id = j.id 
                                    WHERE a.applicant_id = ? AND a.status = 'accepted'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0): ?>
                <h3>Accepted Job Titles</h3>
                <ul>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['title']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No accepted jobs yet.</p>
            <?php endif; ?>
    </div>

    <div class="rejected-jobs">
        <?php
            $stmt = $conn->prepare("SELECT j.title FROM applications a 
                                    LEFT JOIN job_posts j ON a.job_post_id = j.id 
                                    WHERE a.applicant_id = ? AND a.status = 'rejected'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0): ?>
                <h3>Rejected Job Titles</h3>
                <ul>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['title']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No rejections yet.</p>
            <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="chat-section">
        <h2>Start a Chat</h2>
        <?php if (empty($chat_users)): ?>
            <p>No users available for chat at the moment.</p>
        <?php else: ?>
            <ul class="chat-users">
                <?php foreach ($chat_users as $user): ?>
                    <li>
                        <a href="chat.php?recipient_id=<?php echo $user['id']; ?>">
                            Chat with <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

</div>

</body>
</html>