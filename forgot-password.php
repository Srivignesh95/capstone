<?php
session_start();
require_once 'config/conn.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Check if the email exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // In production: generate token, save it in DB, and email the user
        $message = "If this email is registered, you'll receive reset instructions.";
        // Placeholder: echo link (simulate token handling)
        $resetLink = "reset-password.php?email=" . urlencode($email);
        $message .= "<br><a href='$resetLink'>Reset Password</a>"; // for testing only
    } else {
        $error = "No user found with that email address.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content container py-5">
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <h3 class="mb-3">Forgot Password</h3>
        <p class="text-muted">Enter your registered email to receive reset instructions.</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            <div class="mt-3 text-center">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
