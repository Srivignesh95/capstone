<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT e.title, COUNT(g.id) AS total_rsvps, SUM(g.plus_one) AS total_plus_ones
    FROM events e
    LEFT JOIN guests g ON e.id = g.event_id
    GROUP BY e.id
    ORDER BY e.event_date DESC
");
$stats = $stmt->fetchAll();
?>

<h2>RSVP Stats Overview</h2>

<table border="1" cellpadding="6">
    <tr>
        <th>Event</th>
        <th>Total RSVPs</th>
        <th>Total +1s</th>
        <th>Combined Headcount</th>
    </tr>
    <?php foreach ($stats as $s): ?>
        <tr>
            <td><?php echo htmlspecialchars($s['title']); ?></td>
            <td><?php echo $s['total_rsvps']; ?></td>
            <td><?php echo $s['total_plus_ones']; ?></td>
            <td><strong><?php echo $s['total_rsvps'] + $s['total_plus_ones']; ?></strong></td>
        </tr>
    <?php endforeach; ?>
</table>

<p><a href="../dashboard.php">Back to Dashboard</a></p>
