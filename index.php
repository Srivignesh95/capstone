<?php
require_once 'config/conn.php';

// Fetch public & approved events
$stmt = $pdo->query("
    SELECT e.id, e.title, e.description, e.event_date, e.event_time, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.is_public = 1 AND e.status = 'approved'
    ORDER BY e.event_date ASC
");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>EventJoin - Public Events</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Welcome to EventJoin</h1>
	<h2>Webhook is working</h2>
    <p>Discover upcoming public events!</p>

    <?php if (count($events) > 0): ?>
        <div style="padding: 10px;">
            <?php foreach ($events as $event): ?>
                <div style="border: 1px solid #ccc; margin-bottom: 12px; padding: 12px;">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><strong>Date:</strong> <?php echo $event['event_date']; ?> @ <?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                    <p><strong>Hall:</strong> <?php echo htmlspecialchars($event['hall_name']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

                    <form method="GET" action="login.php">
                        <input type="hidden" name="redirect" value="registered_user/confirm_public_rsvp.php?event_id=<?php echo $event['id']; ?>">
                        <button type="submit">RSVP Now</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No public events are currently available.</p>
    <?php endif; ?>
</body>
</html>
