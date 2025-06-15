<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

// Fetch all events with relevant RSVP stats
$stmt = $pdo->query("SELECT 
        e.id, e.title, e.event_date, e.rsvp_limit, 
        COUNT(r.id) AS total_rsvps,
        SUM(CASE WHEN r.rsvp_status = 'yes' THEN 1 ELSE 0 END) AS confirmed_rsvps,
        SUM(CASE WHEN r.rsvp_status = 'invited' THEN 1 ELSE 0 END) AS invited_guests
    FROM events e
    LEFT JOIN event_rsvps r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY e.event_date ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Event RSVP Statistics</h2>

    <?php if (count($events) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>RSVP Limit</th>
                        <th>Confirmed RSVPs</th>
                        <th>Invited Guests</th>
                        <th>Total RSVPs</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= $event['rsvp_limit'] ?></td>
                            <td><?= $event['confirmed_rsvps'] ?? 0 ?></td>
                            <td><?= $event['invited_guests'] ?? 0 ?></td>
                            <td><?= $event['total_rsvps'] ?? 0 ?></td>
                            <td>
                                <?php
                                    $percent = ($event['confirmed_rsvps'] / $event['rsvp_limit']) * 100;
                                    if ($percent >= 100) {
                                        echo '<span class="badge bg-danger">Full</span>';
                                    } elseif ($percent >= 75) {
                                        echo '<span class="badge bg-warning text-dark">Almost Full</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Open</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="export_event_rsvps.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-success">
                                    Export CSV
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No events found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
