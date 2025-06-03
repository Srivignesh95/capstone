<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - EventJoin</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <h2>Welcome to EventJoin Dashboard</h2>
    <p>Your role: <strong><?php echo ucfirst($role); ?></strong></p>

    <?php if ($role === 'venue_manager'): ?>
        <ul>
            <li><a href="#">Manage All Events</a></li>
            <li><a href="#">Approve/Reject Event Requests</a></li>
            <li><a href="#">View RSVP Statistics</a></li>
        </ul>

    <?php elseif ($role === 'requestor'): ?>
        <ul>
            <li><a href="#">Submit New Event Request</a></li>
            <li><a href="#">Manage My Events</a></li>
            <li><a href="#">Send RSVP Links</a></li>
        </ul>

    <?php elseif ($role === 'registered_user'): ?>
        <ul>
            <li><a href="#">Browse Public Events</a></li>
            <li><a href="#">My RSVP History</a></li>
        </ul>

    <?php else: ?>
        <p>Invalid role detected.</p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
