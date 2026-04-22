<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php"); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number     = trim($_POST['room_number']);
    $type            = $_POST['type'];
    $price_per_night = (float)$_POST['price_per_night'];
    $capacity        = (int)$_POST['capacity'];
    $description     = trim($_POST['description']);
    $status          = $_POST['status'];

    if (empty($room_number) || empty($type) || $price_per_night <= 0 || $capacity <= 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        // Check duplicate room number
        $check = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
        $check->execute([$room_number]);
        if ($check->fetch()) {
            $error = "Room number already exists.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO rooms (room_number, type, price_per_night, capacity, description, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$room_number, $type, $price_per_night, $capacity, $description, $status]);
            header("Location: manage_rooms.php?msg=added"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room — Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Add New Room</h1>
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
                    value="<?= htmlspecialchars($_POST['room_number'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Room Type *</label>
                <select name="type" class="form-input">
                    <?php foreach (['Standard','Deluxe','Suite','Family'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($_POST['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price Per Night (RM) *</label>
                <input type="number" name="price_per_night" required step="0.01" min="1" class="form-input"
                    value="<?= htmlspecialchars($_POST['price_per_night'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Capacity (guests) *</label>
                <input type="number" name="capacity" required min="1" max="20" class="form-input"
                    value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" class="form-input"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-input">
                    <option value="available">Available</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width:100%; margin-top:8px;">Add Room</button>
        </form>
    </div>
</div>
</body>
</html>