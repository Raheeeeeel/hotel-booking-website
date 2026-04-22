<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit;
}

$users = $pdo->query("
    SELECT u.*, COUNT(b.id) as total_bookings
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Manage Users</h1>
        <span style="color:#888;"><?= count($users) ?> total users</span>
    </div>

    <div class="admin-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Bookings</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $u['is_admin'] ? 'badge-confirmed' : 'badge-pending' ?>">
                                <?= $u['is_admin'] ? 'Admin' : 'Guest' ?>
                            </span>
                        </td>
                        <td><?= $u['total_bookings'] ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>