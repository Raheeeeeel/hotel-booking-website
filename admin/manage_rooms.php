<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit;
}

// Delete room
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_rooms.php?msg=deleted"); exit;
}

// Toggle status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE rooms SET status = IF(status='available','maintenance','available') WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: manage_rooms.php?msg=updated"); exit;
}

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Manage Rooms</h1>
        <a href="add_room.php" class="btn">+ Add New Room</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert-success">
            <?= $_GET['msg'] === 'deleted' ? 'Room deleted.' : 'Room updated.' ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Room No.</th>
                        <th>Type</th>
                        <th>Price/Night</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($room['room_number']) ?></strong></td>
                        <td><?= htmlspecialchars($room['type']) ?></td>
                        <td>RM <?= number_format($room['price_per_night'], 2) ?></td>
                        <td><?= $room['capacity'] ?> guests</td>
                        <td>
                            <span class="badge badge-<?= $room['status'] === 'available' ? 'confirmed' : 'cancelled' ?>">
                                <?= ucfirst($room['status']) ?>
                            </span>
                        </td>
                        <td class="action-btns">
                            <a href="edit_room.php?id=<?= $room['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="manage_rooms.php?toggle=<?= $room['id'] ?>" class="btn btn-sm btn-outline">
                                <?= $room['status'] === 'available' ? 'Set Maintenance' : 'Set Available' ?>
                            </a>
                            <a href="manage_rooms.php?delete=<?= $room['id'] ?>"
                               onclick="return confirm('Delete this room? This cannot be undone.')"
                               class="btn btn-sm btn-danger">Delete</a>
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