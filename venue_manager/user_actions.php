<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$user_id || !in_array($action, ['make_admin', 'delete'])) {
        header("Location: dashboard.php");
        exit;
    }

    if ($action === 'make_admin') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'venue_manager' WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "User promoted to admin.";
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT id FROM events WHERE created_by = ?");
        $stmt->execute([$user_id]);
        $eventIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($eventIds)) {
            foreach ($eventIds as $eventId) {
                $pdo->prepare("DELETE FROM event_rsvps WHERE event_id = ?")->execute([$eventId]);
                $pdo->prepare("DELETE FROM guests WHERE event_id = ?")->execute([$eventId]);
            }
            $in = str_repeat('?,', count($eventIds) - 1) . '?';
            $pdo->prepare("DELETE FROM events WHERE id IN ($in)")->execute($eventIds);
        }

        $pdo->prepare("DELETE FROM event_rsvps WHERE user_id = ?")->execute([$user_id]);

        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

        $_SESSION['success'] = "User and all associated data deleted successfully.";
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>
