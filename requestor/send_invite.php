<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, title FROM events 
    WHERE created_by = ? AND status = 'approved'
");
$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send RSVP Links</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Send RSVP Link for Your Approved Events</h2>
    <?php if (count($events) > 0): ?>
        <ul>
            <?php foreach ($events as $event): ?>
                <li>
                    <?php echo htmlspecialchars($event['title']); ?> —
                    <input type="text" value="http://localhost/capstone/guest/rsvp.php?event_id=<?php echo $event['id']; ?>" readonly size="60">
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No approved events found.</p>
    <?php endif; ?>
    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
