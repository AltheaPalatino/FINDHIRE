//Functions 
<?php
session_start();
include('core/dbConfig.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$recipient_role = ($role === 'hr') ? 'applicant' : 'hr';

$stmt = $conn->prepare("SELECT id, username FROM users WHERE role = ? AND id != ?");
$stmt->bind_param("si", $recipient_role, $user_id);
$stmt->execute();
$recipients = $stmt->get_result();
$stmt->close();

$messages_query = "
    SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, u.username AS sender_name
    FROM messages m
    INNER JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? OR m.receiver_id = ?)
    AND (m.sender_id = ? OR m.receiver_id = ?)
    ORDER BY m.created_at ASC";
$chat_stmt = $conn->prepare($messages_query);
$chat_stmt->bind_param("iiii", $user_id, $user_id, $_GET['recipient_id'], $_GET['recipient_id']);
$chat_stmt->execute();
$messages = $chat_stmt->get_result();
$chat_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $recipient_id = (int)$_POST['recipient_id'];
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $send_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $send_stmt->bind_param("iis", $user_id, $recipient_id, $message);
        $send_stmt->execute();
        $send_stmt->close();
        header("Location: chat.php?recipient_id=" . $recipient_id);
        exit();
    }
}
?>

<! -- HTML CODES --> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    
</head>
<body>

<div class="container">
    <h1>Chat</h1>

    <form method="GET" action="chat.php">
        <label for="recipient_id">Select Recipient:</label>
        <select name="recipient_id" id="recipient_id" required>
            <option value="">Choose a recipient</option>
            <?php while ($recipient = $recipients->fetch_assoc()): ?>
                <option value="<?php echo $recipient['id']; ?>" 
                    <?php echo (isset($_GET['recipient_id']) && $_GET['recipient_id'] == $recipient['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($recipient['username']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Start Chat</button>
    </form>

    <?php if (isset($_GET['recipient_id']) && $messages->num_rows > 0): ?>
        <div class="message-box">
            <?php while ($message = $messages->fetch_assoc()): ?>
                <div class="message">
                    <span class="sender"><?php echo htmlspecialchars($message['sender_name']); ?>:</span>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <span class="time" style="font-size: 0.8em; color: gray;">(<?php echo $message['created_at']; ?>)</span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php elseif (isset($_GET['recipient_id'])): ?>
        <p>No messages yet. Start the conversation!</p>
    <?php endif; ?>

    <?php if (isset($_GET['recipient_id'])): ?>
        <form method="POST" action="chat.php?recipient_id=<?php echo $_GET['recipient_id']; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo (int)$_GET['recipient_id']; ?>">
            <textarea name="message" placeholder="Type your message here..." required></textarea>
            <button type="submit">Send</button> 

            <a class="back-link" href="index.php">Back to Homepage</a>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
