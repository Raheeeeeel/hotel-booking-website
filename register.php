<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "This email is already registered. Please login.";
        } else {
            // Hash password and save user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed, $phone]);

            // Auto login after register
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin'] = 0;

            header("Location: index.php");
            exit;
        }
    }
}
?>

<main style="max-width:480px; margin:60px auto; padding:0 20px;">
    <div style="background:white; padding:40px; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
        <h2 style="margin-bottom:24px; color:#1a1a2e;">Create Account</h2>

        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin-bottom:16px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Full Name *</label>
                <input type="text" name="name" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Email Address *</label>
                <input type="email" name="email" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Phone Number</label>
                <input type="text" name="phone"
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;"
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Password *</label>
                <input type="password" name="password" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Confirm Password *</label>
                <input type="password" name="confirm_password" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
            </div>

            <button type="submit" class="btn" style="width:100%; padding:12px;">Create Account</button>
        </form>

        <p style="text-align:center; margin-top:20px; color:#666;">
            Already have an account? <a href="login.php" style="color:#e0b97a;">Login here</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>