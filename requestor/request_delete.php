<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];

    $checkStmt = $pdo->prepare("SELECT id FROM events WHERE id = ? AND created_by = ?");
    $checkStmt->execute([$eventId, $_SESSION['user_id']]);
    $event = $checkStmt->fetch();

    if ($event) {
        $updateStmt = $pdo->prepare("UPDATE events SET delete_requested = 1 WHERE id = ?");
        $updateStmt->execute([$eventId]);
    }
}

header("Location: my_events.php");
exit;
