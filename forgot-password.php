<?php
session_start();
require_once 'config/conn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate reset link (for now, with email query param — ideally use a token system)
        $resetLink = "http://svkzone.com/capstone/reset-password.php?email=" . urlencode($email);

        $subject = "EventJoin - Reset Your Password";
        $message = "Hi " . htmlspecialchars($user['name']) . ",\n\n";
        $message .= "Click the link below to reset your password:\n";
        $message .= $resetLink . "\n\nIf you didn’t request a password reset, you can ignore this email.";

        $headers = "From: no-reply@eventjoin.com";

        if (mail($email, $subject, $message, $headers)) {
            $success = "A reset link has been sent to your email.";
        } else {
            $error = "Failed to send reset email. Please try again later.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content container py-5">
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <h3 class="mb-3">Forgot Password</h3>
        <p class="text-muted">Enter your email and we’ll send a password reset link.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
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
