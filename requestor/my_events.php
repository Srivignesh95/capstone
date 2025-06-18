<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.event_date, e.rsvp_deadline, e.status, e.delete_requested, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.created_by = ?
    ORDER BY e.event_date DESC
");

$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content container py-4">
    <h2 class="mb-4">My Event Requests</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Hall</th>
                        <th>RSVP Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= htmlspecialchars($event['hall_name']) ?></td>
                            <td><?= $event['rsvp_deadline'] ?></td>
                            <td>
                                
                            <?php if ($event['status'] === 'rejected'): ?>
                                <strong><?= ucfirst($event['status']) . ' — Check your email for more details' ?></strong>
                            <?php else: ?>
                                <strong><?= ucfirst($event['status']) ?></strong>
                            <?php endif; ?>

                            </td>
                            <td>
                                <a href="edit_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">Edit</a>
                                <?php if ($event['status'] === 'approved'): ?>
                                    <a href="manage_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Manage Guests</a>
                                <?php endif; ?>
                                <?php if ($event['delete_requested']): ?>
                                    <span class="badge bg-warning text-dark">Deletion Pending</span>
                                <?php else: ?>
                                    <form method="POST" action="request_delete.php" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Send deletion request to venue manager?');">
                                            Request Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You haven’t submitted any events yet.</p>
    <?php endif; ?>

    <div class="mt-3">
        <a href="submit_event.php" class="btn btn-success">+ Submit New Event</a>
        <a href="/capstone/registered_user/my_events.php" class="btn btn-link">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
