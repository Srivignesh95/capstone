<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Delete related RSVPs and Guests first (to maintain foreign key constraints)
    $pdo->prepare("DELETE FROM event_rsvps WHERE event_id = ?")->execute([$event_id]);
    $pdo->prepare("DELETE FROM guests WHERE event_id = ?")->execute([$event_id]);

    // Delete the event itself
    $deleteStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $deleteStmt->execute([$event_id]);

    $_SESSION['success'] = "Event deleted successfully.";
    header("Location: deletion_requests.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid request or missing event ID.";
    header("Location: deletion_requests.php");
    exit;
}
