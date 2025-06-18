<?php
session_start();
require_once '../config/conn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    echo "<div class='main-content container py-4'><div class='alert alert-danger'>No event specified.</div></div>";
    include '../includes/footer.php';
    exit;
}

$eventStmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND is_public = 1 AND status = 'approved'");
$eventStmt->execute([$eventId]);
$event = $eventStmt->fetch();

if (!$event) {
    echo "<div class='main-content container py-4'><div class='alert alert-warning'>Event not found or not public.</div></div>";
    include '../includes/footer.php';
    exit;
}

$rsvpCheck = $pdo->prepare("SELECT * FROM event_rsvps WHERE user_id = ? AND event_id = ?");
$rsvpCheck->execute([$userId, $eventId]);
$alreadyRSVPd = $rsvpCheck->rowCount() > 0;

if (!$alreadyRSVPd) {
    $insert = $pdo->prepare("INSERT INTO event_rsvps (user_id, event_id, rsvp_status) VALUES (?, ?, 'yes')");
    $insert->execute([$userId, $eventId]);
}

$userStmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

$userEmail = $user['email'];
$userName = $user['name'];

$eventTitle = $event['title'];
$eventDate = date('F j, Y', strtotime($event['event_date']));
$eventTime = date('g:i A', strtotime($event['event_time']));
$eventHall = $event['hall_id']; 

$subject = "RSVP Confirmation – $eventTitle";
$headers = "From: no-reply@eventjoin.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$body = "Hi $userName,

This is a confirmation that you've successfully Registered to the public event:

Event: $eventTitle
Date: $eventDate at $eventTime

Thank you for registering!

Regards,
EventJoin Team";

mail($userEmail, $subject, $body, $headers);

?>

<div class="main-content container py-5">
    <h2 class="mb-4">Public Event RSVP Confirmation</h2>

    <div class="alert <?= $alreadyRSVPd ? 'alert-info' : 'alert-success' ?>">
        <?php if ($alreadyRSVPd): ?>
            You have already RSVP’d to <strong><?= htmlspecialchars($event['title']) ?></strong>.
        <?php else: ?>
            Thank you! Your RSVP for <strong><?= htmlspecialchars($event['title']) ?></strong> has been successfully recorded.
        <?php endif; ?>
    </div>

    <a href="/capstone/index.php" class="btn btn-outline-primary mt-3">← Back to Event List</a>
</div>

<?php include '../includes/footer.php'; ?>
