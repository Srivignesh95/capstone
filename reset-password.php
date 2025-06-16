<?php
session_start();
require_once 'config/conn.php';

$email = $_GET['email'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        if ($stmt->rowCount() > 0) {
            $success = "Password reset successful. You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Unable to reset password. Email not found.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content container py-5">
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <h3 class="mb-3">Reset Password</h3>
        <p class="text-muted">Set your new password below.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            <div class="mt-3 text-center">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
