<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <section class="container py-5">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</h2>
        <p class="mb-4">Your role: <strong class="text-capitalize"><?php echo $_SESSION['role']; ?></strong></p>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Submit New Event</h5>
                        <p class="card-text">Create a request to host a new private or public event.</p>
                        <a href="submit_event.php" class="btn btn-primary w-100">Submit Event</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Manage My Events</h5>
                        <p class="card-text">Edit, delete, or invite guests to your existing events.</p>
                        <a href="my_events.php" class="btn btn-primary w-100">View My Events</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Send RSVP Links</h5>
                        <p class="card-text">Invite guests and manage their RSVP responses.</p>
                        <a href="invite_guests.php" class="btn btn-primary w-100">Invite Guests</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
