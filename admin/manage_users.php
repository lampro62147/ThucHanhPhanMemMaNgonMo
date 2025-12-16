<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
   
    exit;
}
?>
<?php
include '../includes/db.php';
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>
<style>
.user-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.user-table th,
.user-table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
.user-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #555;
}
.user-table tr:last-child td {
    border-bottom: none;
}
.role-admin {
    background: #ffebee;
    color: #c62828;
    padding: 2px 8px;
    border-radius: 4px;
}
</style>
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
<main style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <h2>👥 Quản lý người dùng</h2>
    <p>Tổng số: <?= count($users) ?> người dùng</p>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Email</th>
                <th>Vai trò</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="role-admin">Admin</span>
                    <?php else: ?>
                        Người dùng
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>