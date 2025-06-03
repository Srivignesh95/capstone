<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

// Fetch events submitted by the current requestor
$stmt = $pdo->prepare("
    SELECT e.title, e.event_date, e.rsvp_deadline, e.status, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.created_by = ?
    ORDER BY e.event_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Submitted Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>My Event Requests</h2>

    <?php if (count($events) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Hall</th>
                <th>RSVP Deadline</th>
                <th>Status</th>
            </tr>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo $event['event_date']; ?></td>
                    <td><?php echo htmlspecialchars($event['hall_name']); ?></td>
                    <td><?php echo $event['rsvp_deadline']; ?></td>
                    <td><strong><?php echo ucfirst($event['status']); ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No event requests submitted yet.</p>
    <?php endif; ?>

    <p><a href="submit_event.php">+ Submit New Event</a></p>
    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
