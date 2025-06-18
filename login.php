<?php
session_start();
require_once 'config/conn.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$error = '';

if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($_POST['redirect'])) {
        $_SESSION['redirect_after_login'] = $_POST['redirect'];
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['first_name'] ?? '';
        $_SESSION['profile_pic'] = $user['profile_pic'] ?? '';

        if (isset($_SESSION['redirect_after_login'])) {
            $redirectTo = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirectTo");
            exit;
        }

        if ($user['role'] === 'admin') {
            header("Location: /capstone/dashboard.php");
        } elseif ($user['role'] === 'requestor') {
            header("Location: /capstone/index.php");
        } else {
            header("Location: /capstone/index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div class="login-container">
    <div class="login-left">
        <h1>Log In to <br> EventJoin</h1>
        <p>Connect, coordinate, and engage with your events smoothly.</p>
    </div>

    <div class="login-right">
        <h2>Sign In to your Account</h2>
        <p>Welcome back! Please enter your details</p>

        <form method="POST">
            <?php if (isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
            <?php endif; ?>

            <div class="position-relative mb-3">
                <span class="form-icon">&#9993;</span>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="position-relative mb-3">
                <span class="form-icon">&#128273;</span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-signin">Sign In</button>
        </form>

        <?php if ($error): ?>
            <div class="text-danger mt-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="mt-3 text-center">
            <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a> |
            <a href="signup.php" class="text-decoration-none">Create a New Account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
