<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/conn.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$userId = $_SESSION['user_id'] ?? null;
$view = $_GET['view'] ?? 'grid';
$sort = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'oldest':
        $orderBy = 'e.event_date ASC';
        break;
    case 'az':
        $orderBy = 'e.title ASC';
        break;
    case 'za':
        $orderBy = 'e.title DESC';
        break;
    case 'newest':
    default:
        $orderBy = 'e.event_date DESC';
        break;
}

// Fetch events
$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.description, e.event_date, e.event_time, e.banner_image, h.name AS hall_name
    FROM events e
    JOIN halls h ON e.hall_id = h.id
    WHERE e.is_public = 1 AND e.status = 'approved' AND e.event_date >= CURDATE()
    ORDER BY $orderBy
");
$stmt->execute();
$events = $stmt->fetchAll();

// Fetch RSVP status
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

<form method="GET" class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <input type="hidden" name="view" value="grid">
        <label class="me-2 fw-bold">Sort By:</label>
        <select name="sort" onchange="this.form.submit()" class="form-select d-inline w-auto">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Date (Oldest First)</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Date (Newest First)</option>
            <option value="az" <?= $sort === 'az' ? 'selected' : '' ?>>A–Z</option>
            <option value="za" <?= $sort === 'za' ? 'selected' : '' ?>>Z–A</option>
        </select>
    </div>
    <div>
        <a href="?view=grid" class="btn btn-outline-primary <?= $view === 'grid' ? 'active' : '' ?>">Grid View</a>
        <a href="?view=calendar" class="btn btn-outline-secondary <?= $view === 'calendar' ? 'active' : '' ?>">Calendar View</a>
    </div>
</form>

<div class="main-content container py-4">
    <?php if ($view === 'grid'): ?>
        <?php if (count($events) > 0): ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="event-card">
                            <?php
                                $imagePath = !empty($event['banner_image']) 
                                    ? '/capstone/assets/images/' . htmlspecialchars($event['banner_image']) 
                                    : 'assets/images/630x350.png';
                                $isRSVPed = in_array($event['id'], $rsvpedEventIds);
                            ?>
                            <img src="<?= $imagePath ?>" class="img-fluid mb-2" style="border-radius: 8px; height: 170px; width: 100%; object-fit: cover;" alt="Event Banner">
                            <h5><?= htmlspecialchars($event['title']) ?></h5>
                            <p><strong>Date:</strong> <?= $event['event_date'] ?> @ <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                            <p><strong>Hall:</strong> <?= htmlspecialchars($event['hall_name']) ?></p>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

                            <?php if (!$userId): ?>
                                <form method="GET" action="/capstone/login.php">
                                    <input type="hidden" name="redirect" value="/capstone/registered_user/confirm_public_rsvp.php?event_id=<?= $event['id'] ?>">
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Register Now</button>
                                </form>
                            <?php elseif ($isRSVPed): ?>
                                <button class="btn btn-secondary w-100 mt-2" disabled>Already Registered</button>
                            <?php else: ?>
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
            <p class="text-muted text-center">No upcoming public events available at the moment.</p>
        <?php endif; ?>
    <?php else: ?>
        <!-- Calendar View -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        <div id="calendar"></div>

        <script>
                const rsvpedEvents = <?= json_encode(array_map('intval', $rsvpedEventIds)) ?>;
                document.addEventListener('DOMContentLoaded', function () {
                    const calendarEl = document.getElementById('calendar');
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        height: 'auto',
                        events: <?= json_encode(array_map(function ($event) {
                            return [
                                'id' => $event['id'],
                                'title' => $event['title'],
                                'start' => $event['event_date'],
                                'description' => nl2br(htmlspecialchars($event['description'])),
                                'time' => date('g:i A', strtotime($event['event_time'])),
                                'hall' => $event['hall_name'],
                            ];
                        }, $events)) ?>,
                        eventClick: function (info) {
                            const e = info.event.extendedProps;
                            const eventId = parseInt(info.event.id);
                            const isRSVPed = rsvpedEvents.includes(eventId);

                            const registerButton = isRSVPed
                                ? `<button class="btn btn-secondary w-100" disabled>Already Registered</button>`
                                : `<form method="GET" action="/capstone/registered_user/confirm_public_rsvp.php">
                                    <input type="hidden" name="event_id" value="${eventId}">
                                    <button type="submit" class="btn btn-primary w-100">Register Now</button>
                                </form>`;

                            const modalContent = `
                                <h5>${info.event.title}</h5>
                                <p><strong>Date:</strong> ${info.event.start.toISOString().split('T')[0]}</p>
                                <p><strong>Time:</strong> ${e.time}</p>
                                <p><strong>Location:</strong> ${e.hall}</p>
                                <p>${e.description}</p>
                                <div class="mt-3">${registerButton}</div>
                            `;

                            document.getElementById('eventModalBody').innerHTML = modalContent;
                            new bootstrap.Modal(document.getElementById('eventModal')).show();
                        }
                    });
                    calendar.render();
                });

        </script>
    <?php endif; ?>
</div>
<!-- Modal for Event Details -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="eventModalBody">
        <!-- Filled dynamically by JS -->
      </div>
    </div>
  </div>
</div>


<?php include 'includes/footer.php'; ?>
