<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE events SET status = ? WHERE id = ?");
        $stmt->execute([$action, $id]);
        header("Location: approve_requests.php");
        exit;
    }
}

$stmt = $pdo->query("
    SELECT e.id, e.title, e.event_date, e.rsvp_deadline, h.name AS hall_name, u.name AS requestor
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    JOIN users u ON e.created_by = u.id
    WHERE e.status = 'pending'
    ORDER BY e.event_date ASC
");

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Event Requests</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Pending Event Requests</h2>

    <?php if (count($events) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Hall</th>
                <th>RSVP Deadline</th>
                <th>Requestor</th>
                <th>Action</th>
            </tr>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo $event['event_date']; ?></td>
                    <td><?php echo htmlspecialchars($event['hall_name']); ?></td>
                    <td><?php echo $event['rsvp_deadline']; ?></td>
                    <td><?php echo htmlspecialchars($event['requestor']); ?></td>
                    <td>
                        <a href="?id=<?php echo $event['id']; ?>&action=approved">Approve</a> | 
                        <a href="?id=<?php echo $event['id']; ?>&action=rejected">Reject</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No pending event requests at the moment.</p>
    <?php endif; ?>

    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
