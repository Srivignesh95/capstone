<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

// Fetch all pending events
$stmt = $pdo->prepare("SELECT e.id, e.title, e.event_date, e.event_time, e.rsvp_deadline, e.rsvp_limit, u.email AS requestor_email, h.name AS hall_name
    FROM events e
    JOIN users u ON e.created_by = u.id
    JOIN halls h ON e.hall_id = h.id
    WHERE e.status = 'pending'
    ORDER BY e.event_date ASC");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Pending Event Requests</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Hall</th>
                        <th>RSVP Deadline</th>
                        <th>RSVP Limit</th>
                        <th>Requestor</th>
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
                            <td><?= $event['rsvp_deadline'] ?></td>
                            <td><?= $event['rsvp_limit'] ?></td>
                            <td><?= htmlspecialchars($event['requestor_email']) ?></td>
                            <td>
                                <form method="POST" action="process_event_status.php" class="d-inline">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                </form>

                                <!-- Button to open modal -->
                                <button type="button" class="btn btn-sm btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $event['id'] ?>">
                                    Reject
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="rejectModal<?= $event['id'] ?>" tabindex="-1" aria-labelledby="rejectLabel<?= $event['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="process_event_status.php">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                        <h5 class="modal-title" id="rejectLabel<?= $event['id'] ?>">Reject Event: <?= htmlspecialchars($event['title']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Reason for rejection:</label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                        </div>
                                        </div>
                                        <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Submit Rejection</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No pending event requests at the moment.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
