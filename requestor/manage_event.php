<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor') {
    header("Location: ../login.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    echo "Event ID missing.";
    exit;
}

// Verify event ownership
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND created_by = ? AND status = 'approved'");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    echo "Unauthorized access or event not found.";
    exit;
}

$message = "";

// Upload CSV
if (isset($_POST['upload_csv']) && isset($_FILES['guest_csv'])) {
    $file = $_FILES['guest_csv']['tmp_name'];
    $handle = fopen($file, 'r');
    fgetcsv($handle); // skip header

    while (($data = fgetcsv($handle)) !== false) {
        // Check current RSVP count
        $capStmt = $pdo->prepare("SELECT COUNT(*) as count FROM guests WHERE event_id = ?");
        $capStmt->execute([$event_id]);
        $current_rsvp_count = $capStmt->fetch()['count'];

        if ($current_rsvp_count >= $event['rsvp_limit']) {
            $message = "RSVP limit reached. Cannot add more guests.";
            $skip_insert = true;
        } else {
            $skip_insert = false;
        }
        $name = $data[0];
        $email = $data[1];
        $note = $data[2];
        $plus_one = $data[3] === '1' ? 1 : 0;

        $rsvp_token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO guests (name, email, event_id, note, plus_one, rsvp_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $event_id, $note, $plus_one, $rsvp_token]);

        $confirmation_url = "http://localhost/capstone/guest/confirm_rsvp.php?token=$rsvp_token";
        $subject = "You're Invited! Please Confirm Your RSVP";
        $messageBody = "Hi $name,\n\nYou've been invited to the event: " . $event['title'] . ".\n\nPlease confirm your RSVP by clicking the link below:\n$confirmation_url\n\nThank you!";
        $headers = "From: no-reply@eventjoin.com";

        mail($email, $subject, $messageBody, $headers);
    }
    fclose($handle);
    $message = "Guest list uploaded successfully.";
}

// Add Guest Manually
if (isset($_POST['add_guest'])) {
    // Check how many guests are already invited
    $capStmt = $pdo->prepare("SELECT COUNT(*) as count FROM guests WHERE event_id = ?");
    $capStmt->execute([$event_id]);
    $current_rsvp_count = $capStmt->fetch()['count'];

    if ($current_rsvp_count >= $event['rsvp_limit']) {
        $message = "RSVP limit reached. Cannot add more guests.";
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $note = $_POST['note'];
        $plus_one = isset($_POST['plus_one']) ? 1 : 0;

        $rsvp_token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO guests (name, email, event_id, note, plus_one, rsvp_token) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $event_id, $note, $plus_one, $rsvp_token]);
        $eventStmt = $pdo->prepare("
            SELECT e.title, e.description, e.event_date, e.event_time, h.name AS hall_name
            FROM events e
            JOIN halls h ON e.hall_id = h.id
            WHERE e.id = ?
        ");
        $eventStmt->execute([$event_id]);
        $event = $eventStmt->fetch();

        // Format start and end times for Google Calendar
        $start = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time']));
        $end = date('Ymd\THis', strtotime($event['event_date'] . ' ' . $event['event_time'] . ' +2 hours')); // default duration

        $googleCalLink = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($event['title']) .
            "&dates=" . $start . "/" . $end .
            "&details=" . urlencode($event['description'] ?? '') .
            "&location=" . urlencode($event['hall_name']) .
            "&sf=true&output=xml";



        // Send RSVP Email
        $confirmation_url = "http://localhost/capstone/guest/confirm_rsvp.php?token=$rsvp_token";
        $to = $email;
        $subject = "You're Invited! Please Confirm Your RSVP";
        $messageBody = "Hi $name,\n\nYou've been invited to the event: " . $event['title'] . ".
        Please confirm your RSVP by clicking the link below:\n$confirmation_url

        After confirming, you can also add this event to your Google Calendar:\n$googleCalLink

        Thank you!";
        $headers = "From: no-reply@eventjoin.com";

        mail($to, $subject, $messageBody, $headers);

        $message = "Guest added and invitation sent successfully.";

    }
}
        // Fetch existing guests
        $stmt = $pdo->prepare("SELECT id, name, email, note, plus_one, rsvp_token FROM guests WHERE event_id = ?");

        $stmt->execute([$event_id]);
        $guests = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Guests - <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Manage Guests for "<?php echo htmlspecialchars($event['title']); ?>"</h2>
    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3>Download CSV Template</h3>
    <a href="download_template.php" target="_blank">Download Guest Template</a>

    <h3>Upload Guest List (CSV)</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="guest_csv" accept=".csv" required>
        <button type="submit" name="upload_csv">Upload CSV</button>
    </form>

    <h3>Add Guest Manually</h3>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Note:</label><br>
        <textarea name="note" rows="3"></textarea><br><br>

        <label><input type="checkbox" name="plus_one"> Bringing +1</label><br><br>

        <button type="submit" name="add_guest">Add Guest</button>
    </form>

    <h3>Invited Guests</h3>
    <?php if (count($guests) > 0): ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Note</th>
                <th>+1</th>
                <th>Action</th>
            </tr>
            <?php foreach ($guests as $g): ?>
                <tr>
                    <td><?php echo htmlspecialchars($g['name']); ?></td>
                    <td><?php echo htmlspecialchars($g['email']); ?></td>
                    <td><?php echo htmlspecialchars($g['note']); ?></td>
                    <td><?php echo $g['plus_one'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="edit_guest.php?event_id=<?php echo $event_id; ?>&guest_id=<?php echo $g['id']; ?>">Edit</a> |
                        <a href="delete_guest.php?event_id=<?php echo $event_id; ?>&guest_id=<?php echo $g['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php if (!empty($g['rsvp_token'])): ?>
                            <small><a href="../guest/confirm_rsvp.php?token=<?php echo $g['rsvp_token']; ?>" target="_blank">RSVP Link</a></small>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No guests added yet.</p>
    <?php endif; ?>

    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
