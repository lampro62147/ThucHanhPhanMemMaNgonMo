<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
// Chỉ khởi động session nếu chưa active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppee Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>🛒 Shoppee Clone</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="cart.php">Giỏ hàng (<?php
                $count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                echo $count;
            ?>)</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
            <?php endif; ?>
        </nav>
    </header>