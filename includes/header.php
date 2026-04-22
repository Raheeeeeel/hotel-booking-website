<?php
session_start();
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel</title>
    <link rel="stylesheet" href="/hotel/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="/hotel/index.php" class="logo">Grand Hotel</a>
    <ul>
        <li><a href="/hotel/index.php">Home</a></li>
        <li><a href="/hotel/rooms.php">Rooms</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/hotel/my_booking.php">My Bookings</a></li>
            <li><a href="/hotel/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/hotel/login.php">Login</a></li>
            <li><a href="/hotel/register.php">Register</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li><a href="/hotel/admin/dashboard.php">Admin</a></li>
        <?php endif; ?>
    </ul>
</nav>