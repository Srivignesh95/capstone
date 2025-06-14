<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    echo "Event ID is missing.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

$hallStmt = $pdo->query("SELECT id, name FROM halls");
$halls = $hallStmt->fetchAll();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $hall_id = $_POST['hall_id'];
    $rsvp_deadline = $_POST['rsvp_deadline'];
    $rsvp_limit = $_POST['rsvp_limit'];
    $is_public = $_POST['is_public'];
    $status = $_POST['status'];

    $banner_image = $event['banner_image'];
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
        $filename = 'event_' . $event_id . '_' . time() . '.' . $ext;
        $targetPath = '../uploads/' . $filename;
        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $targetPath)) {
            $banner_image = $filename;
        }
    }

    $update = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, hall_id = ?, rsvp_deadline = ?, rsvp_limit = ?, is_public = ?, status = ?, banner_image = ? WHERE id = ?");
    $update->execute([$title, $description, $event_date, $event_time, $hall_id, $rsvp_deadline, $rsvp_limit, $is_public, $status, $banner_image, $event_id]);

    $message = "Event updated successfully!";
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
}
?>

<div class="main-content container py-4">
    <h2>Edit Event (Venue Manager)</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($event['title']) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Hall</label>
            <select name="hall_id" class="form-select" required>
                <?php foreach ($halls as $hall): ?>
                    <option value="<?= $hall['id'] ?>" <?= $event['hall_id'] == $hall['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($hall['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date</label>
            <input type="date" name="event_date" class="form-control" value="<?= $event['event_date'] ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Time</label>
            <input type="time" name="event_time" class="form-control" value="<?= $event['event_time'] ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Deadline</label>
            <input type="date" name="rsvp_deadline" class="form-control" value="<?= $event['rsvp_deadline'] ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Limit</label>
            <input type="number" name="rsvp_limit" class="form-control" min="1" value="<?= $event['rsvp_limit'] ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Event Visibility</label>
            <select name="is_public" class="form-select" required>
                <option value="0" <?= $event['is_public'] == 0 ? 'selected' : '' ?>>Private</option>
                <option value="1" <?= $event['is_public'] == 1 ? 'selected' : '' ?>>Public</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Event Status</label>
            <select name="status" class="form-select" required>
                <option value="pending" <?= $event['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $event['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Banner Image</label><br>
            <?php if (!empty($event['banner_image'])): ?>
                <img src="../uploads/<?= htmlspecialchars($event['banner_image']) ?>" class="img-thumbnail mb-2" style="max-height: 120px;">
            <?php endif; ?>
            <input type="file" name="banner_image" class="form-control" accept="image/*">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Update Event</button>
            <a href="approved_events.php" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
