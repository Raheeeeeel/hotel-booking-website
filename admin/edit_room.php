<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch();

if (!$room) {
    header("Location: manage_rooms.php"); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number     = trim($_POST['room_number']);
    $type            = $_POST['type'];
    $price_per_night = (float)$_POST['price_per_night'];
    $capacity        = (int)$_POST['capacity'];
    $description     = trim($_POST['description']);
    $status          = $_POST['status'];

    if (empty($room_number) || $price_per_night <= 0 || $capacity <= 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE rooms SET room_number=?, type=?, price_per_night=?, capacity=?, description=?, status=?
            WHERE id=?
        ");
        $stmt->execute([$room_number, $type, $price_per_night, $capacity, $description, $status, $id]);
        header("Location: manage_rooms.php?msg=updated"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Room — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Edit Room #<?= htmlspecialchars($room['room_number']) ?></h1>
        <a href="manage_rooms.php" class="btn btn-outline">Back to Rooms</a>
    </div>

    <div class="admin-card" style="max-width:600px;">
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Room Number *</label>
                <input type="text" name="room_number" required class="form-input"
                    value="<?= htmlspecialchars($_POST['room_number'] ?? $room['room_number']) ?>">
            </div>
            <div class="form-group">
                <label>Room Type *</label>
                <select name="type" class="form-input">
                    <?php foreach (['Standard','Deluxe','Suite','Family'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($room['type']) === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price Per Night (RM) *</label>
                <input type="number" name="price_per_night" required step="0.01" min="1" class="form-input"
                    value="<?= htmlspecialchars($_POST['price_per_night'] ?? $room['price_per_night']) ?>">
            </div>
            <div class="form-group">
                <label>Capacity (guests) *</label>
                <input type="number" name="capacity" required min="1" max="20" class="form-input"
                    value="<?= htmlspecialchars($_POST['capacity'] ?? $room['capacity']) ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" class="form-input"><?= htmlspecialchars($_POST['description'] ?? $room['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-input">
                    <option value="available"    <?= $room['status'] === 'available'    ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance"  <?= $room['status'] === 'maintenance'  ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width:100%; margin-top:8px;">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>