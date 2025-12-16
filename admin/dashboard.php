<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- 🟢 Giữ nguyên base để fix header -->
    <base href="http://localhost:3000/">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #333;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        .admin-card h3 {
            margin-top: 15px;
            font-size: 1.2rem;
            color: #ee4d2d;
        }
        .icon {
            font-size: 2.5rem;
            color: #ee4d2d;
        }

        /* Header */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            background-color: #ee4d2d;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            font-size: 1.8rem;
        }
        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 5px 10px;
            border-radius: 4px;
        }
        header nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        .header-user {
            display: inline-block;
            margin-left: 15px;
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <h1>🛒 Shoppee Clone</h1>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="cart.php">Giỏ hàng (<?php
            $count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
            echo $count;
        ?>)</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="header-user">Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="admin/dashboard.php">Admin</a>
            <a href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a>
        <?php endif; ?>
    </nav>
</header>

<main class="admin-container">
    <h2>🎛️ Bảng điều khiển Admin</h2>
    <p>Chào mừng bạn quay trở lại! Quản lý cửa hàng của bạn tại đây.</p>

    <div class="admin-grid">
        <!-- ✅ SỬA LINK Ở ĐÂY -->
        <a href="/admin/manage_products.php" class="admin-card">
            <div class="icon">📦</div>
            <h3>Quản lý sản phẩm</h3>
        </a>
        <a href="/admin/manage_users.php" class="admin-card">
            <div class="icon">👥</div>
            <h3>Quản lý người dùng</h3>
        </a>
        <a href="/admin/manage_orders.php" class="admin-card">
            <div class="icon">📋</div>
            <h3>Quản lý đơn hàng</h3>
        </a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>