<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$event_id || !in_array($action, ['approve', 'reject'])) {
        header("Location: pending_events.php");
        exit;
    }

    // Fetch event info + user email
    $eventStmt = $pdo->prepare("
        SELECT e.title, u.email
        FROM events e
        JOIN users u ON e.created_by = u.id
        WHERE e.id = ?
    ");
    $eventStmt->execute([$event_id]);
    $event = $eventStmt->fetch();

    if (!$event) {
        header("Location: pending_events.php");
        exit;
    }

    $eventTitle = $event['title'];
    $userEmail = $event['email'];

    if ($action === 'approve') {
        $pdo->prepare("UPDATE events SET status = 'approved', rejection_reason = NULL WHERE id = ?")
            ->execute([$event_id]);

        // Send approval email
        $subject = "Your Event '$eventTitle' Has Been Approved!";
        $body = "Hi,\n\nYour event '$eventTitle' has been approved by the Venue Manager.\n\nYou can now manage your guests and view it on the dashboard.\n\nThank you!\nEventJoin Team";
    } else {
        $reason = $_POST['rejection_reason'] ?? 'No reason provided.';
        $pdo->prepare("UPDATE events SET status = 'rejected', rejection_reason = ? WHERE id = ?")
            ->execute([$reason, $event_id]);

        // Send rejection email
        $subject = "Your Event '$eventTitle' Has Been Rejected";
        $body = "Hi,\n\nYour event '$eventTitle' has been rejected by the Venue Manager.\n\nReason: $reason\n\nPlease update your event and resubmit.\n\nRegards,\nEventJoin Team";
    }

    // Send email
    $headers = "From: no-reply@eventjoin.com";
    mail($userEmail, $subject, $body, $headers);
}

header("Location: pending_events.php");
exit;
