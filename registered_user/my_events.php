<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <section class="container py-5">
        <h2 class="mb-4">Welcome, <?php echo $_SESSION['username']; ?>!</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Submit New Event</h5>
                        <p class="card-text">Create a request to host a new private or public event.</p>
                        <a href="/capstone/requestor/submit_event.php" class="btn btn-primary w-100">Submit Event</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Manage My Events</h5>
                        <p class="card-text">Edit, delete, or invite guests to your existing events.</p>
                        <a href="/capstone/requestor/my_events.php" class="btn btn-primary w-100">View My Events</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
