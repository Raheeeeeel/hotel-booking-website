<?php
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// Fetch booking with room and user details
$stmt = $pdo->prepare("
    SELECT b.*, r.room_number, r.type, r.price_per_night, r.description,
           u.name as user_name, u.email as user_email, u.phone as user_phone
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN rooms u ON b.room_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "<main style='text-align:cemter; padding:80px;'>
            <h2>Booking not found.</h2>
            <a href='index.php' class='btn' style='margin-top:20px;'>Go Home</a>
        </main>";
    require_once 'includes/footer.php';
    exit;
}

$nights = (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
?>

<main style="max-width:680px; margin:50px auto; padding:0 20px;"> 

    <!--Success Banner-->
    <div style="background:#d1fae5; border-radius:12px; padding:28px; text-align:center; margin-bottom:28px;">
        <h2 style="color:#065f46; font-size:26px; margin-bottom:8px;">Booking Confirmed!</h2>
        <p style="color:#047857;">Your reservation has been successfully placed.</p>
    </div>

    <!-- Booking Details -->
    <div style="background:white; border-radius:12px; padding:32px; box-shadow:0 2px 8px rgba(0,0,0,0.07);">
        <h3 style="color:#1a1a2e; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid #eee;">
            Booking Details
        </h3>

        <div style="display:grid; gap:14px;">
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Booking ID</span>
                <span style="font-weight:600;">#<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Guest Name</span>
                <span style="font-weight:600;"><?= htmlspecialchars($booking['user_name']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Email</span>
                <span style="font-weight:600;"><?= htmlspecialchars($booking['user_email']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Room</span>
                <span style="font-weight:600;">
                    <?= htmlspecialchars($booking['type']) ?> — Room #<?= htmlspecialchars($booking['room_number']) ?>
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Check-in</span>
                <span style="font-weight:600;"><?= date('D, d M Y', strtotime($booking['check_in'])) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Check-out</span>
                <span style="font-weight:600;"><?= date('D, d M Y', strtotime($booking['check_out'])) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Duration</span>
                <span style="font-weight:600;"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Guests</span>
                <span style="font-weight:600;"><?= $booking['guests'] ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f5f5f5;">
                <span style="color:#888;">Status</span>
                <span style="background:#fef3c7; color:#92400e; padding:3px 12px;
                             border-radius:20px; font-size:13px; font-weight:600;">
                    <?= ucfirst($booking['status']) ?>
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:14px 0;">
                <span style="font-weight:bold; font-size:18px;">Total Amount</span>
                <span style="font-weight:bold; font-size:22px; color:#e0b97a;">
                    RM <?= number_format($booking['total_price'], 2) ?>
                </span>
            </div>
        </div>
    </div>

    <!--Action Buttons-->
    <div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap;">
        <a href="my_booking.php" class="btn" style="flex:1; text-align:center;">My Bookings</a>
        <a href="room.php" class="btn btn-outline" style="flex:1; text-align:center;">Book Another Room</a>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>