//Functions Code
<?php
session_start();
include('core/dbConfig.php');

if ($_SESSION['role'] != 'hr') {
    header("Location: index.php");
    exit();
}

$hr_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT messages.id, messages.sender_id, messages.message, messages.created_at, users.username AS sender_name FROM messages INNER JOIN users ON messages.sender_id = users.id WHERE messages.receiver_id = ? ORDER BY messages.created_at DESC");
$stmt->bind_param("i", $hr_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<! -- HTML CODES --> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR - View Messages</title>
    <link rel="stylesheet" href="styles/view_messages.css">
</head>
<body>

<header>
    <h1>Messages from Applicants</h1>
</header>

<a href="index.php" class="btn btn-primary">Back to Homepage</a>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Sent Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No messages from applicants yet.</p>
    <?php endif; ?>

    <br>
</div>

</body>
</html>

<?php
$conn->close();
?>