<?php
require_once '../config/conn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    echo "<div class='alert alert-danger m-4'>Invalid RSVP link.</div>";
    include '../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM guests WHERE rsvp_token = ?");
$stmt->execute([$token]);
$guest = $stmt->fetch();

if (!$guest) {
    echo "<div class='alert alert-danger m-4'>Invalid or expired RSVP token.</div>";
    include '../includes/footer.php';
    exit;
}

$already_submitted = $guest['rsvp_status'] !== null;
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $rsvp_status = $_POST['rsvp_status'];
    $rsvp_note = trim($_POST['rsvp_note'] ?? '');
    if ($rsvp_note === '') $rsvp_note = $guest['note'];
    $plus_one = isset($_POST['plus_one']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE guests SET rsvp_status = ?, note = ?, plus_one = ?, rsvp_at = NOW() WHERE id = ?");
    $stmt->execute([$rsvp_status, $rsvp_note, $plus_one, $guest['id']]);

    if (!empty($guest['email'])) {
        $to = $guest['email'];
        $subject = "Your RSVP has been received – " . $event['title'];
        
        $statusText = strtoupper($rsvp_status) === 'YES' ? 'confirmed' : 'declined';
        $plusOneText = $plus_one ? 'Yes' : 'No';
        
        $message = "
            <html>
            <head><title>RSVP Confirmation</title></head>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Thank you, {$guest['name']}!</h2>
                <p>Your RSVP for <strong>{$event['title']}</strong> has been <strong>{$statusText}</strong>.</p>
                <p><strong>Event Date:</strong> " . date('F j, Y', strtotime($event['event_date'])) . "<br>
                <strong>Time:</strong> " . date('g:i A', strtotime($event['event_time'])) . "<br>
                <strong>Location:</strong> {$event['hall_name']}</p>
                <p><strong>Bringing Guest:</strong> {$plusOneText}<br>
                <strong>Note:</strong> " . nl2br(htmlspecialchars($rsvp_note)) . "</p>
                <hr>
                <p style='font-size: 12px; color: #555;'>This is an automated message from EventJoin.</p>
            </body>
            </html>
        ";

        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: EventJoin <no-reply@svkzone.com>" . "\r\n";

        mail($to, $subject, $message, $headers);
    }


    $submitted = true;
    $already_submitted = true;
    $guest['rsvp_status'] = $rsvp_status;
    $guest['note'] = $rsvp_note;
    $guest['plus_one'] = $plus_one;
    $guest['rsvp_at'] = date('Y-m-d H:i:s');
}


$eventStmt = $pdo->prepare("
    SELECT e.title, e.description, e.event_date, e.event_time, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.id = ?
");
$eventStmt->execute([$guest['event_id']]);
$event = $eventStmt->fetch();


$start = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time']));
$end = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time'] . ' +2 hours'));
$googleLink = "https://www.google.com/calendar/render?action=TEMPLATE" .
    "&text=" . urlencode($event['title']) .
    "&dates=" . $start . "/" . $end .
    "&details=" . urlencode($event['description'] ?? '') .
    "&location=" . urlencode($event['hall_name']) .
    "&sf=true&output=xml";
?>

<div class="main-content container py-5">
    <div class="card shadow-sm p-4">
        <h2 class="mb-3">RSVP Confirmation</h2>

        <?php if ($submitted): ?>
            <div class="alert alert-success">Thank you, your RSVP has been recorded.</div>
            <a href="<?= $googleLink ?>" target="_blank" class="btn btn-outline-primary">➕ Add to Google Calendar</a>

        <?php elseif ($already_submitted): ?>
            <div class="alert alert-info mb-3">
                You already submitted your RSVP on <strong><?= date('F j, Y \a\t g:i A', strtotime($guest['rsvp_at'])) ?></strong>.
            </div>
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Status:</strong> <?= strtoupper($guest['rsvp_status']) ?></li>
                <li class="list-group-item"><strong>+1:</strong> <?= $guest['plus_one'] ? 'Yes' : 'No' ?></li>
                <li class="list-group-item"><strong>Note:</strong> <?= htmlspecialchars($guest['note'] ?? '-') ?></li>
            </ul>

        <?php else: ?>
            <p>Hello <strong><?= htmlspecialchars($guest['name']) ?></strong>,</p>
            <p>You’ve been invited to the event: <strong><?= htmlspecialchars($event['title']) ?></strong> on 
                <strong><?= date('F j, Y', strtotime($event['event_date'])) ?> @ <?= date('g:i A', strtotime($event['event_time'])) ?></strong> at 
                <strong><?= htmlspecialchars($event['hall_name']) ?></strong>.
            </p>

            <form method="POST" class="mt-4">
                <div class="mb-3">
                    <label class="form-label">Will you attend?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rsvp_status" value="yes" id="rsvp_yes" required>
                        <label class="form-check-label" for="rsvp_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rsvp_status" value="no" id="rsvp_no" required>
                        <label class="form-check-label" for="rsvp_no">No</label>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="plus_one" name="plus_one" <?= $guest['plus_one'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="plus_one">I'm bringing a guest</label>
                </div>

                <div class="mb-3">
                    <label for="rsvp_note" class="form-label">Any note or dietary preference?</label>
                    <textarea name="rsvp_note" class="form-control" rows="3"><?= htmlspecialchars($guest['note'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit RSVP</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
