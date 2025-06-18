<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->prepare("SELECT e.id, e.title, e.event_date, e.event_time, h.name AS hall_name, u.email AS creator_email
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    JOIN users u ON e.created_by = u.id
    WHERE e.delete_requested = 1
    ORDER BY e.event_date DESC");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Deletion Requests</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Hall</th>
                        <th>Requested By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= date('g:i A', strtotime($event['event_time'])) ?></td>
                            <td><?= htmlspecialchars($event['hall_name']) ?></td>
                            <td><?= htmlspecialchars($event['creator_email']) ?></td>
                            <td>
                                <form method="POST" action="delete_event.php" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No deletion requests at the moment.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
