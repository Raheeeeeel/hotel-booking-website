<?php require_once 'includes/header.php'; ?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Experience Luxury & Comfort</h1>
            <p>Discover our world-class rooms and suites designed for your perfect stay</p>
            <div class="hero-buttons">
                <a href="rooms.php" class="btn">Browse Rooms</a>
                <a href="register.php" class="btn btn-outline">Register Now</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">★</div>
            <h3>Luxury Rooms</h3>
            <p>Elegantly furnished rooms with premium amenities for a comfortable stay.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">✓</div>
            <h3>Easy Booking</h3>
            <p>Book your room in minutes with our simple and secure booking system.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">♦</div>
            <h3>Best Prices</h3>
            <p>Get the best rates guaranteed with no hidden fees or charges.</p>
        </div>
    </section>

    <!-- Featured Rooms Section -->
    <section class="section">
        <h2 class="section-title">Our Rooms</h2>
        <p class="section-sub">Choose from our selection of premium rooms and suites</p>

        <div class="rooms-grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'available' LIMIT 4");
            $rooms = $stmt->fetchAll();
            foreach ($rooms as $room):
            ?>
            <div class="room-card">
                <div class="room-img-placeholder">
                    <span><?= htmlspecialchars($room['type']) ?></span>
                </div>
                <div class="room-info">
                    <div class="room-header">
                        <h3><?= htmlspecialchars($room['type']) ?> Room</h3>
                        <span class="room-badge"><?= htmlspecialchars($room['room_number']) ?></span>
                    </div>
                    <p><?= htmlspecialchars(substr($room['description'], 0, 80)) ?>...</p>
                    <div class="room-footer">
                        <div class="room-price">
                            RM <?= number_format($room['price_per_night'], 2) ?>
                            <span>/night</span>
                        </div>
                        <a href="rooms.php" class="btn btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center; margin-top:40px;">
            <a href="rooms.php" class="btn">View All Rooms</a>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>