<?php
session_start();
require_once '../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_manager') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';

$pending = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'pending'")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'approved'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'rejected'")->fetchColumn();
$deletions = $pdo->query("SELECT COUNT(*) FROM events WHERE delete_requested = 1")->fetchColumn();
?>

<div class="main-content container py-4">
    <h2 class="mb-4">Venue Manager Dashboard</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">Pending Requests</h5>
                    <p class="display-6"><?= $pending ?></p>
                    <a href="pending_events.php" class="btn btn-outline-warning btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">Approved Events</h5>
                    <p class="display-6"><?= $approved ?></p>
                    <a href="approved_events.php" class="btn btn-outline-success btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger">Rejected Events</h5>
                    <p class="display-6"><?= $rejected ?></p>
                    <a href="rejected_events.php" class="btn btn-outline-danger btn-sm">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">Deletion Requests</h5>
                    <p class="display-6"><?= $deletions ?></p>
                    <a href="deletion_requests.php" class="btn btn-outline-primary btn-sm">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-info">Event Statistics</h5>
                    <p class="display-6"><i class="bi bi-bar-chart-line-fill"></i></p>
                    <a href="event_statistics.php" class="btn btn-outline-info btn-sm">View Stats</a>
                </div>
            </div>
        </div>

    </div>
    <div class="mt-5">
        <h3 class="mb-3">User Management</h3>

        <?php
        $userStmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id DESC");
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['role'] ?></td>
                            <td>
                                <form method="POST" action="user_actions.php" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <a href="edit_user.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary mb-1">Edit</a>
                                    <?php if ($user['role'] !== 'venue_manager'): ?>
                                        <button type="submit" name="action" value="make_admin" class="btn btn-sm btn-success mb-1" onclick="return confirm('Promote this user to Admin?')">Make Admin</button>
                                    <?php endif; ?>

                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
