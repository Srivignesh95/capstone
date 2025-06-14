<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->prepare("SELECT e.*, h.name AS hall_name, u.first_name, u.last_name FROM events e JOIN halls h ON e.hall_id = h.id JOIN users u ON e.created_by = u.id WHERE e.status = 'approved' ORDER BY e.event_date ASC");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Approved Events</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Requestor</th>
                        <th>Visibility</th>
                        <th>RSVP Limit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= date('g:i A', strtotime($event['event_time'])) ?></td>
                            <td><?= htmlspecialchars($event['hall_name']) ?></td>
                            <td><?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?></td>
                            <td><?= $event['is_public'] ? 'Public' : 'Private' ?></td>
                            <td><?= $event['rsvp_limit'] ?></td>
                            <td>
                                <a href="edit_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                <a href="delete_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">There are no approved events at the moment.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
