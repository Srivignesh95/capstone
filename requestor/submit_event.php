<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$hallStmt = $pdo->query("SELECT id, name FROM halls");
$halls = $hallStmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $hall_id = $_POST['hall_id'];
    $rsvp_deadline = $_POST['rsvp_deadline'];
    $rsvp_limit = $_POST['rsvp_limit'];
    $is_public = $_POST['is_public'] ?? 0;

    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, hall_id, created_by, status, rsvp_deadline, rsvp_limit, is_public)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");

    $stmt->execute([
        $title, $description, $event_date, $event_time,
        $hall_id, $_SESSION['user_id'], $rsvp_deadline, $rsvp_limit, $is_public
    ]);

    $message = "Event request submitted successfully!";
    $userStmt = $pdo->prepare("SELECT email, first_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch();

    $to = $user['email'];
    $firstName = $user['first_name'] ?? 'User';
    $subject = "Event Request Submitted - Awaiting Approval";
    $statusLink = "https://svkzone.com/capstone/requestor/my_events.php";

    $body = "Hello $firstName,

    Thank you for submitting your event request titled \"$title\".

    Your request is currently awaiting review by the Venue Manager.

    You will receive another email once it's approved or rejected.

    You can also track the status of your event here:
    $statusLink

    Best regards,
    EventJoin Team";

    $headers = "From: no-reply@eventjoin.com\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    mail($to, $subject, $body, $headers);

}
?>

<div class="main-content container py-5">
    <h2 class="mb-4">Submit a New Event Request</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date</label>
            <input type="date" name="event_date" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Time</label>
            <input type="time" name="event_time" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Deadline</label>
            <input type="date" name="rsvp_deadline" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Limit</label>
            <input type="number" name="rsvp_limit" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Hall</label>
            <select name="hall_id" class="form-select" required>
                <option value="">-- Select Hall --</option>
                <?php foreach ($halls as $hall): ?>
                    <option value="<?= $hall['id'] ?>"><?= htmlspecialchars($hall['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Event Visibility</label>
            <select name="is_public" class="form-select" required>
                <option value="0">Private (invite only)</option>
                <option value="1">Public (visible on homepage)</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="/capstone/registered_user/my_events.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
