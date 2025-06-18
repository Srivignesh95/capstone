
<footer class="text-center text-muted py-4 border-top">
    &copy; <?php echo date('Y'); ?> EventJoin. All rights reserved.
</footer>
<script>
    window.rsvpedEvents = <?= json_encode(array_map('intval', $rsvpedEventIds)) ?>;
    window.calendarEvents = <?= json_encode(array_map(function ($event) {
        return [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['event_date'],
            'description' => nl2br(htmlspecialchars($event['description'])),
            'time' => date('g:i A', strtotime($event['event_time'])),
            'hall' => $event['hall_name'],
        ];
    }, $events)) ?>;
</script>
<script src="/capstone/assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.js'></script>

</body>
</html>
