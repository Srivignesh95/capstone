<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/capstone/config/conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EventJoin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/capstone/assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="index.php">EventJoin</a>

    <div class="ms-auto d-flex align-items-center gap-3">
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $stmt = $pdo->prepare("SELECT first_name, profile_pic FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userData = $stmt->fetch();

                $profilePic = $userData['profile_pic'] ?? '';
                $profilePicPath = '/capstone/assets/images/profile_pic/' . $profilePic;
                $defaultPic = '/capstone/assets/images/avatar.png';

                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/capstone/assets/images/profile_pic/' . $profilePic;
                $finalPic = (!empty($profilePic) && file_exists($absolutePath)) ? $profilePicPath : $defaultPic;

                $displayName = $userData['first_name'] ?? 'User';
            ?>

            <span class="text-white">Hi! <?= htmlspecialchars($displayName) ?></span>
            <img src="<?= htmlspecialchars($finalPic) ?>" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
        <?php else: ?>
            <a href="/capstone/login.php" class="btn btn-light btn-sm">Login</a>
        <?php endif; ?>
    </div>
</nav>
