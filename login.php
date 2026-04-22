<?php
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {
        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Correct — start session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin']  = $user['is_admin'];

            // Redirect admin to dashboard, users to home
            if ($user['is_admin']) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Incorrect email or password. Please try again.";
        }
    }
}
?>

<main style="max-width:480px; margin:60px auto; padding:0 20px;">
    <div style="background:white; padding:40px; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
        <h2 style="margin-bottom:24px; color:#1a1a2e;">Welcome Back</h2>

        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin-bottom:16px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Email Address</label>
                <input type="email" name="email" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Password</label>
                <input type="password" name="password" required
                    style="width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:15px;">
            </div>

            <button type="submit" class="btn" style="width:100%; padding:12px;">Login</button>
        </form>

        <p style="text-align:center; margin-top:20px; color:#666;">
            Don't have an account? <a href="register.php" style="color:#e0b97a;">Register here</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>