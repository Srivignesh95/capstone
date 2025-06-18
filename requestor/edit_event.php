<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) ) {
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

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND created_by = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    echo "Unauthorized access or event not found.";
    exit;
}

$isApproved = $event['status'] === 'approved';


$hallStmt = $pdo->query("SELECT id, name FROM halls");
$halls = $hallStmt->fetchAll();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $banner_image = $event['banner_image']; 

    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
        $filename = 'event_' . $event_id . '_' . time() . '.jpg'; 
        $uploadDir = '../assets/images/';
        $targetPath = $uploadDir . $filename;
    
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        $tmpPath = $_FILES['banner_image']['tmp_name'];

        [$originalWidth, $originalHeight] = getimagesize($tmpPath);
        $dstWidth = 320;
        $dstHeight = 170;
    
        $dstImage = imagecreatetruecolor($dstWidth, $dstHeight);
    
        $mime = mime_content_type($tmpPath);
        switch ($mime) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($tmpPath);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($tmpPath);
                break;
            default:
                $message = "Unsupported image format. Please upload JPG, PNG, or WEBP.";
                $srcImage = null;
        }
    
        if ($srcImage) {
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $dstWidth, $dstHeight, $originalWidth, $originalHeight);
            imagejpeg($dstImage, $targetPath, 90); 
            imagedestroy($dstImage);
            imagedestroy($srcImage);
            $banner_image = $filename;
        }
    }
    

    if ($isApproved) {
        $update = $pdo->prepare("UPDATE events SET title = ?, description = ?, banner_image = ? WHERE id = ? AND created_by = ?");
        $update->execute([$title, $description, $banner_image, $event_id, $_SESSION['user_id']]);
    } else {
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $hall_id = $_POST['hall_id'];
        $rsvp_deadline = $_POST['rsvp_deadline'];
        $rsvp_limit = $_POST['rsvp_limit'];
        $is_public = $_POST['is_public'];

        $update = $pdo->prepare("
            UPDATE events 
            SET title = ?, description = ?, event_date = ?, event_time = ?, hall_id = ?, 
                rsvp_deadline = ?, rsvp_limit = ?, is_public = ?, banner_image = ?
            WHERE id = ? AND created_by = ?
        ");
        $update->execute([
            $title, $description, $event_date, $event_time, $hall_id,
            $rsvp_deadline, $rsvp_limit, $is_public, $banner_image,
            $event_id, $_SESSION['user_id']
        ]);
    }

    $message = "Event updated successfully!";
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch();
}
?>

<div class="main-content container py-4">
    <h2>Edit Event</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3 mt-3">
        <!-- Title -->
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($event['title']) ?>">
        </div>

        <!-- Hall -->
        <div class="col-md-6">
            <label class="form-label">Hall</label>
            <?php if ($isApproved): ?>
                <?php
                    $hallName = '';
                    foreach ($halls as $hall) {
                        if ($hall['id'] == $event['hall_id']) {
                            $hallName = $hall['name'];
                            break;
                        }
                    }
                ?>
                <input type="text" class="form-control bg-light border" value="<?= htmlspecialchars($hallName) ?>" readonly>
            <?php else: ?>
                <select name="hall_id" class="form-select" required>
                    <option value="">-- Select Hall --</option>
                    <?php foreach ($halls as $hall): ?>
                        <option value="<?= $hall['id'] ?>" <?= $event['hall_id'] == $hall['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($hall['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date</label>
            <?php if ($isApproved): ?>
                <input type="text" class="form-control bg-light border" value="<?= $event['event_date'] ?>" readonly>
            <?php else: ?>
                <input type="date" name="event_date" class="form-control" value="<?= $event['event_date'] ?>" required>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Time</label>
            <?php if ($isApproved): ?>
                <input type="text" class="form-control bg-light border" value="<?= $event['event_time'] ?>" readonly>
            <?php else: ?>
                <input type="time" name="event_time" class="form-control" value="<?= $event['event_time'] ?>" required>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Deadline</label>
            <?php if ($isApproved): ?>
                <input type="text" class="form-control bg-light border" value="<?= $event['rsvp_deadline'] ?>" readonly>
            <?php else: ?>
                <input type="date" name="rsvp_deadline" class="form-control" value="<?= $event['rsvp_deadline'] ?>" required>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">RSVP Limit</label>
            <?php if ($isApproved): ?>
                <input type="text" class="form-control bg-light border" value="<?= $event['rsvp_limit'] ?>" readonly>
            <?php else: ?>
                <input type="number" name="rsvp_limit" class="form-control" min="1" value="<?= $event['rsvp_limit'] ?>" required>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Event Visibility</label>
            <?php if ($isApproved): ?>
                <input type="text" class="form-control bg-light border" value="<?= $event['is_public'] ? 'Public' : 'Private' ?>" readonly>
            <?php else: ?>
                <select name="is_public" class="form-select" required>
                    <option value="0" <?= $event['is_public'] == 0 ? 'selected' : '' ?>>Private</option>
                    <option value="1" <?= $event['is_public'] == 1 ? 'selected' : '' ?>>Public</option>
                </select>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Banner Image</label><br>
            <?php if (!empty($event['banner_image']) && file_exists("../assets/images/" . $event['banner_image'])): ?>
                <img src="../assets/images/<?= htmlspecialchars($event['banner_image']) ?>" alt="Banner" class="img-thumbnail mb-2" style="max-height: 120px;">
            <?php else: ?>
                <p class="text-muted">No banner uploaded.</p>
            <?php endif; ?>
            <input type="file" name="banner_image" class="form-control" accept="image/*">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Update Event</button>
            <a href="my_events.php" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
