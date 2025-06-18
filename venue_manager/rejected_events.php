<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.event_date, e.event_time, e.rsvp_deadline, e.rsvp_limit, 
           e.is_public, e.description, e.created_by, e.banner_image, h.name AS hall_name, u.email
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    JOIN users u ON e.created_by = u.id
    WHERE e.status = 'rejected'
    ORDER BY e.event_date DESC
");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Rejected Event Requests</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Hall</th>
                        <th>Creator</th>
                        <th>RSVP Limit</th>
                        <th>Visibility</th>
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
                            <td><?= htmlspecialchars($event['email']) ?></td>
                            <td><?= $event['rsvp_limit'] ?></td>
                            <td><?= $event['is_public'] ? 'Public' : 'Private' ?></td>
                            <td>
                                <a href="edit_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="delete_event.php?event_id=<?= $event['id'] ?>" 
                                   onclick="return confirm('Delete this rejected event permanently?')" 
                                   class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No rejected events at this time.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
