<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/conn.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$userId = $_SESSION['user_id'] ?? null;

// Fetch upcoming public & approved events
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.description, e.event_date, e.event_time, e.banner_image, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.is_public = 1 AND e.status = 'approved' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
");
$stmt->execute();
$events = $stmt->fetchAll();

// Preload RSVP'd event IDs for the logged-in user
$rsvpedEventIds = [];
if ($userId) {
    $rsvpStmt = $pdo->prepare("SELECT event_id FROM event_rsvps WHERE user_id = ? AND rsvp_status = 'yes'");
    $rsvpStmt->execute([$userId]);
    $rsvpedEventIds = $rsvpStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- Hero Section -->
<section class="hero">
    <h1 class="display-5 fw-bold">Welcome to EventJoin</h1>
    <p class="lead">Discover upcoming public events and join the fun!</p>
</section>

<div class="main-content flex-grow-1">
    <div class="container py-5">
        <?php if (count($events) > 0): ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <?php
                                $imagePath = !empty($event['banner_image']) ? '/capstone/assets/images/' . htmlspecialchars($event['banner_image']) : 'assets/images/630x350.png';
                                $isRSVPed = in_array($event['id'], $rsvpedEventIds);
                            ?>
                            <img src="<?= $imagePath ?>" alt="Event Image" class="img-fluid mb-3" style="border-radius: 8px; max-height: 180px; object-fit: cover; width: 100%;">
                            <h5><?= htmlspecialchars($event['title']) ?></h5>
                            <p><strong>Date:</strong> <?= $event['event_date'] ?> @ <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                            <p><strong>Hall:</strong> <?= htmlspecialchars($event['hall_name']) ?></p>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

                            <?php if (!$userId): ?>
                                <!-- Not logged in: go to login with redirect -->
                                <form method="GET" action="/capstone/login.php">
                                    <input type="hidden" name="redirect" value="/capstone/registered_user/confirm_public_rsvp.php?event_id=<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Register Now</button>
                                </form>

                            <?php elseif ($isRSVPed): ?>
                                <!-- Already registered -->
                                <button class="btn btn-secondary w-100 mt-2" disabled>Already Registered</button>

                            <?php else: ?>
                                <!-- Logged in and not registered -->
                                <form method="GET" action="/capstone/registered_user/confirm_public_rsvp.php">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Register Now</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No upcoming public events available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
