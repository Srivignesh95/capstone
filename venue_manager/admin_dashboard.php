<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

// Fetch counts
$pending = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'pending'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'approved'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'rejected'")->fetchColumn();
$deletions = $pdo->query("SELECT COUNT(*) FROM events WHERE delete_requested = 1")->fetchColumn();
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Venue Manager Dashboard</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">Pending Requests</h5>
                    <p class="display-6"><?= $pending ?></p>
                    <a href="pending_events.php" class="btn btn-outline-warning btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">Approved Events</h5>
                    <p class="display-6"><?= $approved ?></p>
                    <a href="approved_events.php" class="btn btn-outline-success btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger">Rejected Events</h5>
                    <p class="display-6"><?= $rejected ?></p>
                    <a href="rejected_events.php" class="btn btn-outline-danger btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">Deletion Requests</h5>
                    <p class="display-6"><?= $deletions ?></p>
                    <a href="deletion_requests.php" class="btn btn-outline-primary btn-sm">View Details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
