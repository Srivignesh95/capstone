<?php
session_start();
require_once 'config/conn.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Compose full name
    $name = $first_name . ' ' . $last_name;

    // Validation
    if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email is already registered.";
    }

    // Insert into DB if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, name, email, password, role) VALUES (?, ?, ?, ?, ?, 'requestor')");
        $insert->execute([$first_name, $last_name, $name, $email, $hashedPassword]);

        $success = "Account created successfully! <a href='login.php'>Login here</a>.";
    }
}
?>

<?php include 'includes/header.php'; 
include 'includes/sidebar.php'; ?>
<div class="main-content container py-5">
    <h2>Sign Up</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Sign Up</button>
            <a href="login.php" class="btn btn-link">Already have an account?</a>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
