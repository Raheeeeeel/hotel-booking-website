<?php require_once 'includes/header.php'; ?>

<main>
    <section class="section">
        <h2 class="section-title">All Rooms</h2>
        <p class="section-sub">Find the perfect room for your stay</p>

        <!-- Search & Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="Standard"  <?= ($_GET['type'] ?? '') === 'Standard'  ? 'selected' : '' ?>>Standard</option>
                    <option value="Deluxe"    <?= ($_GET['type'] ?? '') === 'Deluxe'    ? 'selected' : '' ?>>Deluxe</option>
                    <option value="Suite"     <?= ($_GET['type'] ?? '') === 'Suite'     ? 'selected' : '' ?>>Suite</option>
                    <option value="Family"    <?= ($_GET['type'] ?? '') === 'Family'    ? 'selected' : '' ?>>Family</option>
                </select>

                <select name="capacity" class="filter-select">
                    <option value="">Any Guests</option>
                    <option value="1" <?= ($_GET['capacity'] ?? '') === '1' ? 'selected' : '' ?>>1 Guest</option>
                    <option value="2" <?= ($_GET['capacity'] ?? '') === '2' ? 'selected' : '' ?>>2 Guests</option>
                    <option value="3" <?= ($_GET['capacity'] ?? '') === '3' ? 'selected' : '' ?>>3 Guests</option>
                    <option value="5" <?= ($_GET['capacity'] ?? '') === '5' ? 'selected' : '' ?>>5+ Guests</option>
                </select>

                <select name="price" class="filter-select">
                    <option value="">Any Price</option>
                    <option value="low"  <?= ($_GET['price'] ?? '') === 'low'  ? 'selected' : '' ?>>Under RM200</option>
                    <option value="mid"  <?= ($_GET['price'] ?? '') === 'mid'  ? 'selected' : '' ?>>RM200 - RM400</option>
                    <option value="high" <?= ($_GET['price'] ?? '') === 'high' ? 'selected' : '' ?>>Above RM400</option>
                </select>

                <button type="submit" class="btn btn-sm">Search</button>
                <a href="rooms.php" class="btn btn-sm btn-outline">Reset</a>
            </form>
        </div>

        <?php
        // Build query based on filters
        $where = ["status = 'available'"];
        $params = [];

        if (!empty($_GET['type'])) {
            $where[] = "type = ?";
            $params[] = $_GET['type'];
        }
        if (!empty($_GET['capacity'])) {
            $where[] = "capacity >= ?";
            $params[] = $_GET['capacity'];
        }
        if (!empty($_GET['price'])) {
            if ($_GET['price'] === 'low')  { $where[] = "price_per_night < 200";  }
            if ($_GET['price'] === 'mid')  { $where[] = "price_per_night BETWEEN 200 AND 400"; }
            if ($_GET['price'] === 'high') { $where[] = "price_per_night > 400";  }
        }

        $sql  = "SELECT * FROM rooms WHERE " . implode(" AND ", $where) . " ORDER BY price_per_night ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rooms = $stmt->fetchAll();
        ?>

        <?php if (empty($rooms)): ?>
            <div style="text-align:center; padding:60px; color:#888;">
                <p style="font-size:18px;">No rooms found matching your search.</p>
                <a href="rooms.php" class="btn" style="margin-top:16px;">Show All Rooms</a>
            </div>
        <?php else: ?>
        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <div class="room-img-placeholder">
                    <span><?= htmlspecialchars($room['type']) ?></span>
                </div>
                <div class="room-info">
                    <div class="room-header">
                        <h3><?= htmlspecialchars($room['type']) ?> Room</h3>
                        <span class="room-badge">Room <?= htmlspecialchars($room['room_number']) ?></span>
                    </div>
                    <p style="color:#666; font-size:14px; margin:8px 0 12px;">
                        <?= htmlspecialchars($room['description']) ?>
                    </p>
                    <p style="font-size:13px; color:#888; margin-bottom:16px;">
                        Max guests: <?= $room['capacity'] ?>
                    </p>
                    <div class="room-footer">
                        <div class="room-price">
                            RM <?= number_format($room['price_per_night'], 2) ?>
                            <span>/night</span>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="booking.php?room_id=<?= $room['id'] ?>" class="btn btn-sm">Book Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-sm">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>