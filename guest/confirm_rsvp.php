<?php
require_once '../config/conn.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    echo "Invalid link.";
    exit;
}

// Fetch guest using token
$stmt = $pdo->prepare("SELECT * FROM guests WHERE rsvp_token = ?");
$stmt->execute([$token]);
$guest = $stmt->fetch();

if (!$guest) {
    echo "Invalid or expired RSVP link.";
    exit;
}

$already_submitted = $guest['rsvp_status'] !== null;
$submitted = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $rsvp_status = $_POST['rsvp_status'];
    $rsvp_note = trim($_POST['rsvp_note'] ?? '');
    if ($rsvp_note === '') {
        $rsvp_note = $guest['note'];
    }
    $plus_one = isset($_POST['plus_one']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE guests
        SET rsvp_status = ?, note = ?, plus_one = ?, rsvp_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$rsvp_status, $rsvp_note, $plus_one, $guest['id']]);

    $submitted = true;
    $already_submitted = true;
    $guest['rsvp_status'] = $rsvp_status;
    $guest['rsvp_note'] = $rsvp_note;
    $guest['plus_one'] = $plus_one;
    $guest['rsvp_at'] = date('Y-m-d H:i:s');
}

// Fetch the event details using event_id
$eventStmt = $pdo->prepare("
    SELECT e.title, e.description, e.event_date, e.event_time, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.id = ?
");
$eventStmt->execute([$guest['event_id']]);
$event = $eventStmt->fetch();

$start = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time']));
$end = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time'] . ' +2 hours')); // assume 2-hour duration

$googleLink = "https://www.google.com/calendar/render?action=TEMPLATE" .
    "&text=" . urlencode($event['title']) .
    "&dates=" . $start . "/" . $end .
    "&details=" . urlencode($event['description'] ?? '') .
    "&location=" . urlencode($event['hall_name']) .
    "&sf=true&output=xml";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm RSVP</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>RSVP Confirmation</h2>

    <?php if ($submitted): ?>
        <p style="color:green;">Thank you, your RSVP has been recorded.</p>
        <p>
            <a href="<?php echo $googleLink; ?>" target="_blank">➕ Add to Google Calendar</a>
        </p>

    <?php elseif ($already_submitted): ?>
        <p style="color:blue;">You have already submitted your RSVP on <strong><?php echo date('F j, Y \a\t g:i A', strtotime($guest['rsvp_at'])); ?></strong>.</p>
        <p>Status: <strong><?php echo strtoupper($guest['rsvp_status']); ?></strong></p>
        <p>+1: <?php echo $guest['plus_one'] ? 'Yes' : 'No'; ?></p>
        <p>Note: <?php echo htmlspecialchars($guest['note'] ?? '-'); ?></p>

    <?php else: ?>
        <p>Hello <?php echo htmlspecialchars($guest['name']); ?>,</p>
        <p>You’ve been invited to an event. Please confirm your attendance below.</p>

        <form method="POST">
            <label>Will you attend?</label><br>
            <label><input type="radio" name="rsvp_status" value="yes" required> Yes</label>
            <label><input type="radio" name="rsvp_status" value="no" required> No</label><br><br>

            <label><input type="checkbox" name="plus_one" <?php echo $guest['plus_one'] ? 'checked' : ''; ?>> I’m bringing a guest</label><br><br>

            <label>Any note or dietary preference?</label><br>
            <textarea name="rsvp_note" rows="3"><?php echo htmlspecialchars($guest['rsvp_note'] ?? ''); ?></textarea><br><br>

            <button type="submit">Submit RSVP</button>
        </form>
    <?php endif; ?>
</body>
</html>
