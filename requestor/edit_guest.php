<?php
session_start();
require_once '../config/conn.php';

$event_id = $_GET['event_id'] ?? null;
$guest_id = $_GET['guest_id'] ?? null;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requestor' || !$event_id || !$guest_id) {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ? AND event_id = ?");
$stmt->execute([$guest_id, $event_id]);
$guest = $stmt->fetch();

if (!$guest) {
    echo "Guest not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $note = $_POST['note'];
    $plus_one = isset($_POST['plus_one']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE guests SET name=?, email=?, note=?, plus_one=? WHERE id=? AND event_id=?");
    $stmt->execute([$name, $email, $note, $plus_one, $guest_id, $event_id]);

    header("Location: manage_event.php?event_id=$event_id");
    exit;
}
?>

<h2>Edit Guest</h2>
<form method="POST">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($guest['name']); ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($guest['email']); ?>"><br><br>

    <label>Note:</label><br>
    <textarea name="note"><?php echo htmlspecialchars($guest['note']); ?></textarea><br><br>

    <label><input type="checkbox" name="plus_one" <?php echo $guest['plus_one'] ? 'checked' : ''; ?>> Bringing +1</label><br><br>

    <button type="submit">Update</button>
</form>
