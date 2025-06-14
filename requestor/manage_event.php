<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo "Event ID missing.";
    exit;
}

// Validate ownership
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND created_by = ? AND status = 'approved'");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Unauthorized access or event not found.";
    exit;
}

$message = "";

// Upload CSV
if (isset($_POST['upload_csv']) && isset($_FILES['guest_csv'])) {
    $file = $_FILES['guest_csv']['tmp_name'];
    $handle = fopen($file, 'r');
    fgetcsv($handle); // skip header

    while (($data = fgetcsv($handle)) !== false) {
        $capStmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE event_id = ?");
        $capStmt->execute([$event_id]);
        $current_rsvp_count = $capStmt->fetchColumn();

        if ($current_rsvp_count >= $event['rsvp_limit']) {
            $message = "RSVP limit reached. Cannot add more guests.";
            break;
        }

        $name = $data[0];
        $email = $data[1];
        $note = $data[2];
        $plus_one = $data[3] === '1' ? 1 : 0;
        $rsvp_token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO guests (name, email, event_id, note, plus_one, rsvp_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $event_id, $note, $plus_one, $rsvp_token]);

        $confirmation_url = "https://svkzone.com/capstone/guest/confirm_rsvp.php?token=$rsvp_token";
        $subject = "You're Invited! Please Confirm Your RSVP";
        $messageBody = "Hi $name,\n\nYou've been invited to the event: " . $event['title'] . ".\n\nConfirm your RSVP:\n$confirmation_url";
        $headers = "From: no-reply@eventjoin.com";
        mail($email, $subject, $messageBody, $headers);
    }
    fclose($handle);
    $message = "Guest list uploaded successfully.";
}

// Add manually
if (isset($_POST['add_guest'])) {
    $capStmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE event_id = ?");
    $capStmt->execute([$event_id]);
    $current_rsvp_count = $capStmt->fetchColumn();

    if ($current_rsvp_count >= $event['rsvp_limit']) {
        $message = "RSVP limit reached. Cannot add more guests.";
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $note = $_POST['note'];
        $plus_one = isset($_POST['plus_one']) ? 1 : 0;
        $rsvp_token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO guests (name, email, event_id, note, plus_one, rsvp_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $event_id, $note, $plus_one, $rsvp_token]);

        $eventStmt = $pdo->prepare("
            SELECT e.title, e.description, e.event_date, e.event_time, h.name AS hall_name
            FROM events e
            JOIN halls h ON e.hall_id = h.id
            WHERE e.id = ?
        ");
        $eventStmt->execute([$event_id]);
        $event = $eventStmt->fetch();

        $start = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time']));
        $end = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time'] . ' +2 hours'));

        $googleCalLink = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($event['title']) .
            "&dates=" . $start . "/" . $end .
            "&details=" . urlencode($event['description'] ?? '') .
            "&location=" . urlencode($event['hall_name']) .
            "&sf=true&output=xml";

        $confirmation_url = "https://svkzone.com/capstone/guest/confirm_rsvp.php?token=$rsvp_token";
        $subject = "You're Invited! Please Confirm Your RSVP";
        $messageBody = "Hi $name,\n\nYou've been invited to: " . $event['title'] . ".
Confirm RSVP: $confirmation_url\n\nAdd to Google Calendar:\n$googleCalLink";
        $headers = "From: no-reply@eventjoin.com";

        mail($email, $subject, $messageBody, $headers);

        $message = "Guest added and invitation sent successfully.";
    }
}

// Fetch all guests
$stmt = $pdo->prepare("SELECT id, name, email, note, plus_one, rsvp_token FROM guests WHERE event_id = ?");
$stmt->execute([$event_id]);
$guests = $stmt->fetchAll();
?>

<div class="main-content container py-5">
    <h2 class="mb-4">Manage Guests - <span class="text-primary"><?= htmlspecialchars($event['title']) ?></span></h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <a href="download_template.php" class="btn btn-outline-secondary btn-sm">📄 Download CSV Template</a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <h5>Upload Guest List (CSV)</h5>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="guest_csv" class="form-control mb-2" accept=".csv" required>
                <button type="submit" name="upload_csv" class="btn btn-primary">Upload CSV</button>
            </form>
        </div>

        <div class="col-md-6">
            <h5>Add Guest Manually</h5>
            <form method="POST">
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Note</label>
                    <textarea name="note" class="form-control"></textarea>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="plus_one" id="plus_one">
                    <label class="form-check-label" for="plus_one">Bringing +1</label>
                </div>
                <button type="submit" name="add_guest" class="btn btn-success">Add Guest</button>
            </form>
        </div>
    </div>

    <h5>Invited Guests</h5>
    <?php if (count($guests) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Note</th>
                        <th>+1</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guests as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['name']) ?></td>
                            <td><?= htmlspecialchars($g['email']) ?></td>
                            <td><?= htmlspecialchars($g['note']) ?></td>
                            <td><?= $g['plus_one'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <a href="edit_guest.php?event_id=<?= $event_id ?>&guest_id=<?= $g['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_guest.php?event_id=<?= $event_id ?>&guest_id=<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php if (!empty($g['rsvp_token'])): ?>
                                    <a href="../guest/confirm_rsvp.php?token=<?= $g['rsvp_token'] ?>" target="_blank" class="btn btn-sm btn-outline-info mt-1">RSVP Link</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No guests added yet.</p>
    <?php endif; ?>

    <a href="../dashboard.php" class="btn btn-outline-secondary mt-4">⬅ Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
