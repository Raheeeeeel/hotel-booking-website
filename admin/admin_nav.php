<nav class="admin-nav">
    <div class="admin-logo">Grand Hotel</div>
    <ul>
        <li><a href="dashboard.php"        class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php'        ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="manage_rooms.php"     class="<?= basename($_SERVER['PHP_SELF']) === 'manage_rooms.php'     ? 'active' : '' ?>">Manage Rooms</a></li>
        <li><a href="manage_bookings.php"  class="<?= basename($_SERVER['PHP_SELF']) === 'manage_bookings.php'  ? 'active' : '' ?>">Manage Bookings</a></li>
        <li><a href="manage_users.php"     class="<?= basename($_SERVER['PHP_SELF']) === 'manage_users.php'     ? 'active' : '' ?>">Manage Users</a></li>
        <li><a href="../index.php">View Website</a></li>
        <li><a href="../logout.php" style="color:#f87171;">Logout</a></li>
    </ul>
</nav>