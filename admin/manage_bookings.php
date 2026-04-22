<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit;
}

// Update booking status
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $allowed = ['pending','confirmed','cancelled','completed'];
    if (in_array($_GET['status'], $allowed)) {
        $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")
            ->execute([$_GET['status'], $_GET['id']]);
    }
    header("Location: manage_bookings.php?msg=updated"); exit;
}

// Filter by status
$filter = $_GET['filter'] ?? '';
$where  = $filter ? "WHERE b.status = " . $pdo->quote($filter) : '';

$bookings = $pdo->query("
    SELECT b.*, u.name as user_name, u.email as user_email,
           r.room_number, r.type
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    $where
    ORDER BY b.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Manage Bookings</h1>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert-success">Booking status updated successfully.</div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
        <?php foreach ([''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','cancelled'=>'Cancelled','completed'=>'Completed'] as $val => $label): ?>
            <a href="manage_bookings.php?filter=<?= $val ?>"
               class="btn btn-sm <?= $filter === $val ? '' : 'btn-outline' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="admin-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['user_name']) ?></strong><br>
                            <span style="font-size:12px; color:#888;"><?= htmlspecialchars($b['user_email']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($b['type']) ?> #<?= htmlspecialchars($b['room_number']) ?></td>
                        <td><?= date('d M Y', strtotime($b['check_in'])) ?></td>
                        <td><?= date('d M Y', strtotime($b['check_out'])) ?></td>
                        <td><?= $b['guests'] ?></td>
                        <td>RM <?= number_format($b['total_price'], 2) ?></td>
                        <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                        <td class="action-btns">
                            <?php if ($b['status'] === 'pending'): ?>
                                <a href="manage_bookings.php?id=<?= $b['id'] ?>&status=confirmed" class="btn btn-sm">Confirm</a>
                                <a href="manage_bookings.php?id=<?= $b['id'] ?>&status=cancelled" class="btn btn-sm btn-danger">Cancel</a>
                            <?php elseif ($b['status'] === 'confirmed'): ?>
                                <a href="manage_bookings.php?id=<?= $b['id'] ?>&status=completed" class="btn btn-sm">Complete</a>
                                <a href="manage_bookings.php?id=<?= $b['id'] ?>&status=cancelled" class="btn btn-sm btn-danger">Cancel</a>
                            <?php else: ?>
                                <span style="color:#aaa; font-size:13px;">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>