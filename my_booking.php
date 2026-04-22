<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $stmt = $pdo->prepare("
        UPDATE bookings SET status = 'cancelled'
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$_GET['cancel'], $_SESSION['user_id']]);
    header("Location: my_booking.php?msg=cancelled");
    exit;
}

// Fetch user bookings
$stmt = $pdo->prepare("
    SELECT b.*, r.room_number, r.type, r.price_per_night
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<main style="max-width:900px; margin:50px auto; padding:0 20px;">
    <h2 style="color:#1a1a2e; margin-bottom:24px;">My Bookings</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
        <div class="alert-success" style="margin-bottom:20px;">Booking cancelled successfully.</div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div style="text-align:center; background:white; padding:60px; border-radius:12px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.07);">
            <p style="font-size:18px; color:#888; margin-bottom:20px;">You have no bookings yet.</p>
            <a href="rooms.php" class="btn">Browse Rooms</a>
        </div>
    <?php else: ?>
        <div style="display:grid; gap:16px;">
            <?php foreach ($bookings as $b):
                $nights = (new DateTime($b['check_in']))->diff(new DateTime($b['check_out']))->days;
                $statusColors = [
                    'pending'   => ['bg'=>'#fef3c7','text'=>'#92400e'],
                    'confirmed' => ['bg'=>'#d1fae5','text'=>'#065f46'],
                    'cancelled' => ['bg'=>'#fee2e2','text'=>'#991b1b'],
                    'completed' => ['bg'=>'#e0e7ff','text'=>'#3730a3'],
                ];
                $sc = $statusColors[$b['status']] ?? ['bg'=>'#f3f4f6','text'=>'#374151'];
            ?>
            <div style="background:white; border-radius:12px; padding:24px;
                        box-shadow:0 2px 8px rgba(0,0,0,0.07);">
                <div style="display:flex; justify-content:space-between; align-items:start; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h3 style="color:#1a1a2e; margin-bottom:4px;">
                            <?= htmlspecialchars($b['type']) ?> Room — #<?= htmlspecialchars($b['room_number']) ?>
                        </h3>
                        <p style="color:#888; font-size:14px;">
                            Booking #<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?>
                            &nbsp;·&nbsp;
                            <?= date('d M Y', strtotime($b['created_at'])) ?>
                        </p>
                    </div>
                    <span style="background:<?= $sc['bg'] ?>; color:<?= $sc['text'] ?>;
                                 padding:4px 14px; border-radius:20px; font-size:13px; font-weight:600;">
                        <?= ucfirst($b['status']) ?>
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px,1fr));
                            gap:12px; margin-top:16px; padding-top:16px; border-top:1px solid #f5f5f5;">
                    <div>
                        <p style="color:#888; font-size:12px; margin-bottom:2px;">Check-in</p>
                        <p style="font-weight:600;"><?= date('d M Y', strtotime($b['check_in'])) ?></p>
                    </div>
                    <div>
                        <p style="color:#888; font-size:12px; margin-bottom:2px;">Check-out</p>
                        <p style="font-weight:600;"><?= date('d M Y', strtotime($b['check_out'])) ?></p>
                    </div>
                    <div>
                        <p style="color:#888; font-size:12px; margin-bottom:2px;">Duration</p>
                        <p style="font-weight:600;"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></p>
                    </div>
                    <div>
                        <p style="color:#888; font-size:12px; margin-bottom:2px;">Guests</p>
                        <p style="font-weight:600;"><?= $b['guests'] ?></p>
                    </div>
                    <div>
                        <p style="color:#888; font-size:12px; margin-bottom:2px;">Total</p>
                        <p style="font-weight:600; color:#e0b97a;">RM <?= number_format($b['total_price'], 2) ?></p>
                    </div>
                </div>

                <?php if ($b['status'] === 'pending'): ?>
                <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f5f5f5;">
                    <a href="my_booking.php?cancel=<?= $b['id'] ?>"
                       onclick="return confirm('Are you sure you want to cancel this booking?')"
                       style="color:#dc2626; font-size:14px; text-decoration:none; font-weight:500;">
                        Cancel Booking
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>