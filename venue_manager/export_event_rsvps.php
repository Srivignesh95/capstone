<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo "Event ID missing.";
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=event_rsvps_' . $event_id . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Event Title', 'Event Date', 'Requester Name', 'Guest Name', 'Guest Email', 'RSVP Status', '+1', 'Note', 'RSVP At']);

$eventStmt = $pdo->prepare("
    SELECT e.title, e.event_date, u.name AS requester_name
    FROM events e
    JOIN users u ON e.created_by = u.id
    WHERE e.id = ?
");
$eventStmt->execute([$event_id]);
$event = $eventStmt->fetch();

if (!$event) {
    fputcsv($output, ['No event found']);
    exit;
}

$guestStmt = $pdo->prepare("
    SELECT name, email, rsvp_status, plus_one, note, rsvp_at
    FROM guests
    WHERE event_id = ?
");
$guestStmt->execute([$event_id]);
$guests = $guestStmt->fetchAll();

if (count($guests) === 0) {
    fputcsv($output, ['No RSVPs recorded for this event']);
    exit;
}

$firstRow = true;

foreach ($guests as $g) {
    fputcsv($output, [
        $firstRow ? $event['title'] : '',
        $firstRow ? $event['event_date'] : '',
        $firstRow ? $event['requester_name'] : '',
        $g['name'],
        $g['email'],
        strtoupper($g['rsvp_status']),
        $g['plus_one'] ? 'Yes' : 'No',
        $g['note'],
        $g['rsvp_at']
    ]);

    $firstRow = false;
}

fclose($output);
exit;
