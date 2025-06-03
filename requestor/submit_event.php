<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

// Fetch halls for the dropdown
$hallStmt = $pdo->query("SELECT id, name FROM halls");
$halls = $hallStmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $hall_id = $_POST['hall_id'];
    $rsvp_deadline = $_POST['rsvp_deadline'];
    $rsvp_limit = $_POST['rsvp_limit'];
    $is_public = $_POST['is_public'] ?? 0;

    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, hall_id, created_by, status, rsvp_deadline, rsvp_limit, is_public)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");

    $stmt->execute([
        $title, $description, $event_date, $event_time,
        $hall_id, $_SESSION['user_id'], $rsvp_deadline, $rsvp_limit, $is_public
    ]);

    $message = "Event request submitted successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Event Request</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Submit a New Event Request</h2>

    <?php if ($message): ?>
        <p style="color:green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required></textarea><br><br>

        <label>Date:</label><br>
        <input type="date" name="event_date" required><br><br>

        <label>Time:</label><br>
        <input type="time" name="event_time" required><br><br>

        <label>Hall:</label><br>
        <select name="hall_id" required>
            <option value="">-- Select Hall --</option>
            <?php foreach ($halls as $hall): ?>
                <option value="<?php echo $hall['id']; ?>"><?php echo $hall['name']; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>RSVP Deadline:</label><br>
        <input type="date" name="rsvp_deadline" required><br><br>

        <label>RSVP Limit:</label><br>
        <input type="number" name="rsvp_limit" min="1" required><br><br>
        <label>Event Visibility:</label><br>
        <select name="is_public" required>
            <option value="0">Private (invite only)</option>
            <option value="1">Public (visible on homepage)</option>
        </select><br><br>

        <button type="submit">Submit Request</button>
    </form>

    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
