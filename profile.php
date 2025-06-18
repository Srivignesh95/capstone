<?php
session_start();
require_once 'config/conn.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $country = $_POST['country'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $profile_pic_filename = $user['profile_pic']; 


    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        $target_path = 'assets/images/profile_pic/' . $new_filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
            $profile_pic_filename = $new_filename;
        }
    }

    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, country = ?, bio = ?, profile_pic = ?
        WHERE id = ?
    ");
    $updateStmt->execute([
        $first_name, $last_name, $phone, $address, $city, $country, $bio, $profile_pic_filename, $userId
    ]);

    $message = "Profile updated successfully.";

    // Refresh
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}
?>

<div class="main-content container py-5">
    <h2>My Profile</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3 mt-4">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email (readonly)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Country</label>
            <input type="text" class="form-control" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst($user['role'])) ?>" readonly>
        </div>

        <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Profile Picture</label><br>
            <?php if (!empty($user['profile_pic']) && file_exists("assets/images/profile_pic/" . $user['profile_pic'])): ?>
                <img src="assets/images/profile_pic/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="img-thumbnail mb-2" style="max-height: 120px;">
            <?php else: ?>
                <p class="text-muted">No picture uploaded.</p>
            <?php endif; ?>
            <input type="file" class="form-control" name="profile_pic" accept="image/*">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
