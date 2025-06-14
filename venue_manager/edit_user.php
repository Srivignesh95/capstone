<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo "User ID missing.";
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $updateStmt->execute([$name, $email, $role, $user_id]);

    $message = "User updated successfully!";
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content container py-4">
    <h2>Edit User</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mt-2">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="requestor" <?= $user['role'] === 'requestor' ? 'selected' : '' ?>>Requestor</option>
                <option value="venue_manager" <?= $user['role'] === 'venue_manager' ? 'selected' : '' ?>>Venue Manager</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
