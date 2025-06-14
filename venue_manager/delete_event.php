<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    $_SESSION['error'] = "Event ID missing.";
    header("Location: approved_events.php");
    exit;
}

// Delete related RSVPs and Guests first (to maintain foreign key constraints)
$pdo->prepare("DELETE FROM event_rsvps WHERE event_id = ?")->execute([$event_id]);
$pdo->prepare("DELETE FROM guests WHERE event_id = ?")->execute([$event_id]);

// Delete the event
$deleteStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$deleteStmt->execute([$event_id]);

$_SESSION['success'] = "Event deleted successfully.";
header("Location: approved_events.php");
exit;
?>
