<?php
require_once '../config/conn.php';

$event_id = $_GET['event_id'] ?? null;
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? null;
    $note = $_POST['note'];
    $plus_one = isset($_POST['plus_one']) ? 1 : 0;
    $event_id = $_POST['event_id'];

    $stmt = $pdo->prepare("
        INSERT INTO guests (name, email, event_id, note, plus_one) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $event_id, $note, $plus_one]);
    $submitted = true;
}

// Fetch event title
$title = "";
if ($event_id) {
    $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    $title = $event ? $event['title'] : "Unknown Event";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RSVP - <?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>RSVP to: <?php echo htmlspecialchars($title); ?></h2>

    <?php if ($submitted): ?>
        <p style="color:green;">Thank you! Your RSVP has been recorded.</p>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

            <label>Name:</label><br>
            <input type="text" name="name" required><br><br>

            <label>Email (optional):</label><br>
            <input type="email" name="email"><br><br>

            <label>Note (e.g. meal preference):</label><br>
            <textarea name="note" rows="3"></textarea><br><br>

            <label>
                <input type="checkbox" name="plus_one"> Bringing a guest?
            </label><br><br>

            <button type="submit">Submit RSVP</button>
        </form>
    <?php endif; ?>
</body>
</html>
