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
$sort = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'az':
        $orderBy = 'title ASC';
        break;
    case 'za':
        $orderBy = 'title DESC';
        break;
    case 'oldest':
        $orderBy = 'event_date ASC';
        break;
    default:
        $orderBy = 'event_date DESC';
        break;
}
$query = "
    SELECT 
        e.id, e.title, e.event_date, e.event_time, e.description, e.created_by,
        e.banner_image, h.name AS hall_name
    FROM event_rsvps r
    JOIN events e ON r.event_id = e.id
    JOIN halls h ON e.hall_id = h.id
    WHERE r.user_id = :uid AND r.rsvp_status = 'yes' AND e.event_date < CURDATE()

    UNION

    SELECT 
        e.id, e.title, e.event_date, e.event_time, e.description, e.created_by,
        e.banner_image, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.created_by = :uid2 AND e.event_date < CURDATE()

    ORDER BY $orderBy
";

$stmt = $pdo->prepare($query);
$stmt->execute(['uid' => $user_id, 'uid2' => $user_id]);
$events = $stmt->fetchAll();
?>

<div class="main-content">
    <section class="hero">
        <h1 class="display-6 fw-bold">Past Events</h1>
        <p class="lead">View all events you’ve attended or organized in the past.</p>
    </section>
    <form method="GET" class="mb-4 text-end">
        <label class="me-2 fw-bold">Sort By:</label>
        <select name="sort" onchange="this.form.submit()" class="form-select d-inline w-auto">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Date (Newest First)</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Date (Oldest First)</option>
            <option value="az" <?= ($_GET['sort'] ?? '') === 'az' ? 'selected' : '' ?>>A–Z</option>
            <option value="za" <?= ($_GET['sort'] ?? '') === 'za' ? 'selected' : '' ?>>Z–A</option>
        </select>
    </form>

    <div class="container py-4">
        <?php if (count($events) > 0): ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <?php
                                $localPath = '../assets/images/' . $event['banner_image'];
                                $imageUrl = '/capstone/assets/images/' . htmlspecialchars($event['banner_image']);

                                $imagePath = (!empty($event['banner_image']) && file_exists($localPath))
                                    ? $imageUrl
                                    : '/capstone/assets/images/630x350.png';
                            ?>

                            <img src="<?= $imagePath ?>" class="img-fluid mb-2" style="border-radius: 8px; height: 170px; width: 100%; object-fit: cover;" alt="Event Banner">

                            <h5><?= htmlspecialchars($event['title']) ?></h5>
                            <p><strong>Date:</strong> <?= $event['event_date'] ?> @ <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($event['hall_name']) ?></p>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">You have no past events to display.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
