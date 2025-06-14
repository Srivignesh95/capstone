<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$user_id = $_SESSION['user_id'];

// Fetch upcoming RSVP'd events + check if user is the creator
$stmt = $pdo->prepare("
    SELECT 
        e.id, e.title, e.event_date, e.event_time, e.description, e.created_by,
        e.banner_image,e.status, h.name AS hall_name
    FROM event_rsvps r
    JOIN events e ON r.event_id = e.id
    JOIN halls h ON e.hall_id = h.id
    WHERE r.user_id = ? AND r.rsvp_status = 'yes' AND e.event_date >= CURDATE()

    UNION

    SELECT 
        e.id, e.title, e.event_date, e.event_time, e.description, e.created_by,
        e.banner_image,e.status, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.created_by = ? AND e.event_date >= CURDATE()

    ORDER BY event_date ASC
");

$stmt->execute([$user_id, $user_id]);
$events = $stmt->fetchAll();

?>

<div class="main-content">
    <section class="hero">
        <h1 class="display-6 fw-bold">Upcoming Events You've RSVP’d To</h1>
        <p class="lead">Keep track of your confirmed upcoming events.</p>
    </section>

    <div class="container py-4">
        <?php if (count($events) > 0): ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <?php
                                $localPath = '../assets/images/' . $event['banner_image']; // Actual location
                                $imageUrl = '/capstone/assets/images/' . htmlspecialchars($event['banner_image']); // For browser

                                $imagePath = (!empty($event['banner_image']) && file_exists($localPath))
                                    ? $imageUrl
                                    : '/capstone/assets/images/630x350.png'; // fallback for browser
                            ?>

                            <img src="<?= $imagePath ?>" class="img-fluid mb-2" style="border-radius: 8px; height: 170px; width: 100%; object-fit: cover;" alt="Event Banner">


                            <h5><?= htmlspecialchars($event['title']) ?></h5>
                            <p><strong>Date:</strong> <?= $event['event_date'] ?> @ <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($event['hall_name']) ?></p>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

                            <?php if ($event['created_by'] == $user_id): ?>
                                <?php if ($event['status'] === 'approved'): ?>
                                    <a href="manage_event.php?event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Manage Guests</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">You haven’t RSVP’d to any upcoming events yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
