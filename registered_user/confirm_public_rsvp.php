<?php
session_start();
require_once '../config/conn.php';

// Make sure user is logged in and is a registered user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registered_user') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    echo "No event specified.";
    exit;
}

// Check if event exists and is public
$eventStmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND is_public = 1 AND status = 'approved'");
$eventStmt->execute([$eventId]);
$event = $eventStmt->fetch();

if (!$event) {
    echo "Event not found or is not public.";
    exit;
}

// Check if user already RSVP'd
$rsvpCheck = $pdo->prepare("SELECT * FROM event_rsvps WHERE user_id = ? AND event_id = ?");
$rsvpCheck->execute([$userId, $eventId]);

$alreadyRSVPd = $rsvpCheck->rowCount() > 0;

if (!$alreadyRSVPd) {
    $insert = $pdo->prepare("INSERT INTO event_rsvps (user_id, event_id, rsvp_status) VALUES (?, ?, 'yes')");
    $insert->execute([$userId, $eventId]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RSVP Confirmation</title>
</head>
<body>
    <h2>Public Event RSVP</h2>
    <p>
        <?php if ($alreadyRSVPd): ?>
            You've already RSVP'd to this event.
        <?php else: ?>
            Thank you! Your RSVP for <strong><?php echo htmlspecialchars($event['title']); ?></strong> has been recorded.
        <?php endif; ?>
    </p>

    <p><a href="browse_events.php">Back to Event List</a></p>
</body>
</html>
