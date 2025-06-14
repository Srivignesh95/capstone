<?php
require_once '../config/conn.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$event_id = $_GET['event_id'] ?? null;
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? null;
    $note = $_POST['note'];
    $plus_one = isset($_POST['plus_one']) ? 1 : 0;
    $event_id = $_POST['event_id'];

    $stmt = $pdo->prepare("
        INSERT INTO guests (name, email, event_id, note, plus_one) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $event_id, $note, $plus_one]);
    $submitted = true;
}

// Fetch event title
$title = "";
if ($event_id) {
    $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    $title = $event ? $event['title'] : "Unknown Event";
}
?>

<div class="main-content container py-4">
    <h2 class="mb-4">RSVP for: <?= htmlspecialchars($title) ?></h2>

    <?php if ($submitted): ?>
        <div class="alert alert-success">
            Thank you! Your RSVP has been recorded.
        </div>
    <?php else: ?>
        <form method="POST" class="row g-3">
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email (optional)</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Note / Dietary Preferences</label>
                <textarea name="note" class="form-control" rows="3" placeholder="Optional..."></textarea>
            </div>

            <div class="col-md-12">
                <label class="form-check-label">
                    <input type="checkbox" name="plus_one" class="form-check-input"> Bringing a guest?
                </label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Submit RSVP</button>
                <a href="../index.php" class="btn btn-secondary ms-2">Back</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
