//Functions
<?php
session_start();
include('core/dbConfig.php');

if ($_SESSION['role'] != 'applicant') {
    header("Location: index.php");
    exit();
}

$hr_id = 2;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $message = htmlspecialchars($message);

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $_SESSION['user_id'], $hr_id, $message);

    if ($stmt->execute()) {
        echo "<p>Message sent successfully.</p>";
    } else {
        echo "<p>Error sending message: " . $conn->error . "</p>";
    }

    $stmt->close();
}
?>

<! -- HTML CODES --> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message HR</title>
    <link rel="stylesheet" href="styles/message_hr.css">
</head>
<body>

<header>
    <h1>Send Message to HR</h1>
</header>

<div class="container">
    <form method="POST">
        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="4" required></textarea>

        <button type="submit">Send Message</button>
    </form>
    <a href="index.php">Back to Homepage</a>
</div>

</body>
</html>

<?php
$conn->close();
?>