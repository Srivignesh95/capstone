<?php
session_start();
require_once '../config/conn.php';

$event_id = $_GET['event_id'] ?? null;
$guest_id = $_GET['guest_id'] ?? null;

if (!isset($_SESSION['user_id']) || !$event_id || !$guest_id) {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM guests WHERE id = ? AND event_id = ?");
$stmt->execute([$guest_id, $event_id]);

header("Location: manage_event.php?event_id=$event_id");
exit;
