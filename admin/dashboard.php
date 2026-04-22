<?php
session_start();
require_once '../includes/db.php';

// Admin guard — non-admins get redirected
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

// Fetch stats
$total_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$total_rooms    = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$total_revenue  = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status != 'cancelled'")->fetchColumn();
$pending        = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

// Recent bookings
$recent = $pdo->query("
    SELECT b.*, u.name as user_name, r.room_number, r.type
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.created_at DESC
    LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Grand Hotel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Dashboard</h1>
        <span>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">Total Guests</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_rooms ?></div>
            <div class="stat-label">Total Rooms</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_bookings ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card highlight">
            <div class="stat-value">RM <?= number_format($total_revenue ?? 0, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card warn">
            <div class="stat-value"><?= $pending ?></div>
            <div class="stat-label">Pending Bookings</div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Bookings</h2>
            <a href="manage_bookings.php" class="btn btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $b): ?>
                    <tr>
                        <td><?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td><?= htmlspecialchars($b['type']) ?> #<?= htmlspecialchars($b['room_number']) ?></td>
                        <td><?= date('d M Y', strtotime($b['check_in'])) ?></td>
                        <td><?= date('d M Y', strtotime($b['check_out'])) ?></td>
                        <td>RM <?= number_format($b['total_price'], 2) ?></td>
                        <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>