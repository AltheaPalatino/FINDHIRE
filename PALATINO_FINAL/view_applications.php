<?php
session_start();
include('core/dbConfig.php');

if ($_SESSION['role'] != 'hr') {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT a.applicant_id, a.resume, a.description, u.username, a.id AS application_id, a.status FROM applications a INNER JOIN users u ON a.applicant_id = u.id");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applicants</title>
    <link rel="stylesheet" href="styles/view_applications.css">
    
</head>
<body>

<header>
    <h1>All Applicants</h1>
</header>

<a href="index.php" class="btn btn-primary">Back to Homepage</a>

<div class="container">
    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo "<p style='color: green;'>Application processed successfully!</p>";
        } else {
            echo "<p style='color: red;'>An error occurred while processing the application. Please try again.</p>";
        }
    }
    ?>


    <table>
        <thead>
            <tr>
                <th>Applicant Name</th>
                <th>Number/email</th>
                <th>Resume</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <a href="uploads/<?php echo htmlspecialchars($row['resume']); ?>" target="_blank">Download Resume</a>
                    </td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending') { ?>
                            <form action="process_application.php" method="POST" style="display:inline;">
                                <button type="submit" name="action" value="accept" class="btn btn-success" onclick="return confirm('Are you sure you want to accept this applicant?')">Accept</button>
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>" />
                            </form>
                            <form action="process_application.php" method="POST" style="display:inline;">
                                <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this applicant?')">Reject</button>
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>" />
                            </form>
                        <?php } else { ?>
                            <em>Already <?php echo htmlspecialchars($row['status']); ?></em>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>