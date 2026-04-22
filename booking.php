<?php
require_once 'includes/header.php';

// Must be logged in to book
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get room ID from URL
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

// Fetch room details
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    echo "<main style='text-align:center; padding:80px;'>
            <h2>Room not found or unavailable.</h2>
            <a href='rooms.php' class='btn' style='margin-top:20px;'>Back to Rooms</a>
          </main>";
    require_once 'includes/footer.php';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in  = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests    = (int)$_POST['guests'];

    // Validate dates
    $in  = new DateTime($check_in);
    $out = new DateTime($check_out);
    $today = new DateTime(date('Y-m-d'));

    if ($in < $today) {
        $error = "Check-in date cannot be in the past.";
    } elseif ($out <= $in) {
        $error = "Check-out date must be after check-in date.";
    } elseif ($guests < 1 || $guests > $room['capacity']) {
        $error = "Number of guests must be between 1 and {$room['capacity']}.";
    } else {
        // Check availability — look for overlapping bookings
        $stmt = $pdo->prepare("
            SELECT id FROM bookings
            WHERE room_id = ?
            AND status NOT IN ('cancelled')
            AND (check_in < ? AND check_out > ?)
        ");
        $stmt->execute([$room_id, $check_out, $check_in]);

        if ($stmt->fetch()) {
            $error = "Sorry, this room is already booked for the selected dates. Please choose different dates.";
        } else {
            // Calculate total price
            $nights      = $in->diff($out)->days;
            $total_price = $nights * $room['price_per_night'];

            // Save booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $room_id,
                $check_in,
                $check_out,
                $guests,
                $total_price
            ]);

            $booking_id = $pdo->lastInsertId();
            header("Location: confirmation.php?booking_id=" . $booking_id);
            exit;
        }
    }
}

// Default dates
$default_checkin  = date('Y-m-d', strtotime('+1 day'));
$default_checkout = date('Y-m-d', strtotime('+2 days'));
?>

<main style="max-width:800px; margin:50px auto; padding:0 20px;">
    <h2 style="color:#1a1a2e; margin-bottom:24px;">Book Your Room</h2>

    <!-- Room Summary Card -->
    <div style="background:white; border-radius:12px; padding:24px; margin-bottom:28px;
                box-shadow:0 2px 8px rgba(0,0,0,0.07); display:flex; gap:24px; align-items:center;">
        <div style="background:linear-gradient(135deg,#1a1a2e,#0f3460); width:100px; height:80px;
                    border-radius:8px; display:flex; align-items:center; justify-content:center;">
            <span style="color:#e0b97a; font-weight:bold;"><?= htmlspecialchars($room['type']) ?></span>
        </div>
        <div>
            <h3 style="color:#1a1a2e;"><?= htmlspecialchars($room['type']) ?> Room — #<?= htmlspecialchars($room['room_number']) ?></h3>
            <p style="color:#666; font-size:14px; margin:6px 0;"><?= htmlspecialchars($room['description']) ?></p>
            <p style="color:#e0b97a; font-weight:bold; font-size:18px;">
                RM <?= number_format($room['price_per_night'], 2) ?> / night
            </p>
        </div>
    </div>

    <!-- Booking Form -->
    <div style="background:white; border-radius:12px; padding:32px; box-shadow:0 2px 8px rgba(0,0,0,0.07);">
        <h3 style="margin-bottom:24px; color:#1a1a2e;">Select Your Dates</h3>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:500;">Check-in Date</label>
                    <input type="date" name="check_in" id="check_in" required
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        value="<?= $_POST['check_in'] ?? $default_checkin ?>"
                        style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:500;">Check-out Date</label>
                    <input type="date" name="check_out" id="check_out" required
                        min="<?= date('Y-m-d', strtotime('+2 days')) ?>"
                        value="<?= $_POST['check_out'] ?? $default_checkout ?>"
                        style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">
                    Number of Guests (Max: <?= $room['capacity'] ?>)
                </label>
                <input type="number" name="guests" required
                    min="1" max="<?= $room['capacity'] ?>" value="<?= $_POST['guests'] ?? 1 ?>"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
            </div>

            <!-- Price Summary -->
            <div id="price_summary" style="background:#f9f9f9; border-radius:8px; padding:16px; margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#666;">Price per night</span>
                    <span>RM <?= number_format($room['price_per_night'], 2) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#666;">Number of nights</span>
                    <span id="nights_count">1</span>
                </div>
                <hr style="border:none; border-top:1px solid #eee; margin:12px 0;">
                <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:18px;">
                    <span>Total</span>
                    <span id="total_price" style="color:#e0b97a;">
                        RM <?= number_format($room['price_per_night'], 2) ?>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn" style="width:100%; padding:14px; font-size:16px;">
                Confirm Booking
            </button>
        </form>
    </div>
</main>

<!-- Live price calculator -->
<script>
const pricePerNight = <?= $room['price_per_night'] ?>;

function updatePrice() {
    const checkIn  = new Date(document.getElementById('check_in').value);
    const checkOut = new Date(document.getElementById('check_out').value);

    if (checkIn && checkOut && checkOut > checkIn) {
        const nights = Math.round((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        const total  = nights * pricePerNight;
        document.getElementById('nights_count').textContent = nights;
        document.getElementById('total_price').textContent  = 'RM ' + total.toFixed(2);
    }
}

document.getElementById('check_in').addEventListener('change', function() {
    // Auto set checkout to at least 1 day after checkin
    const checkIn  = new Date(this.value);
    const minOut   = new Date(checkIn);
    minOut.setDate(minOut.getDate() + 1);
    const minOutStr = minOut.toISOString().split('T')[0];
    document.getElementById('check_out').min   = minOutStr;
    document.getElementById('check_out').value = minOutStr;
    updatePrice();
});

document.getElementById('check_out').addEventListener('change', updatePrice);
window.addEventListener('load', updatePrice);
</script>

<?php require_once 'includes/footer.php'; ?>