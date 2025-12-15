<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';


// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];

    $status_map = [
        'confirm' => 'confirmed',
        'ship' => 'shipped',
        'deliver' => 'delivered'
    ];

    if (isset($status_map[$action])) {
        $new_status = $status_map[$action];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success = "✅ Cập nhật trạng thái đơn hàng #{$order_id} thành công!";
    }
}

// Lấy danh sách đơn hàng
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();

$status_map = [
    'pending' => ['label' => '⏳ Chờ xác nhận', 'color' => '#f57c00'],
    'confirmed' => ['label' => '✅ Đã xác nhận', 'color' => '#388e3c'],
    'shipped' => ['label' => '🚚 Đang giao', 'color' => '#1976d2'],
    'delivered' => ['label' => '📦 Đã giao', 'color' => '#0288d1']
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <!-- 🟢 Đặt gốc cho mọi đường dẫn -->
    <base href="http://localhost:3000/">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Header giống dashboard */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding-top: 70px; /* tránh bị header che */
        }
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #ee4d2d;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
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

        /* Nội dung chính */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .order-table th,
        .order-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .order-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        .order-table tr:last-child td {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            color: white;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-right: 5px;
        }
        .btn-confirm { background: #388e3c; color: white; }
        .btn-ship { background: #1976d2; color: white; }
        .btn-deliver { background: #0288d1; color: white; }
        .btn:hover { opacity: 0.9; }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- 🟢 Header giống hệt dashboard.php -->
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
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2>📋 Quản lý đơn hàng</h2>

    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <p>Tổng số: <?= count($orders) ?> đơn hàng</p>

    <table class="order-table">
        <thead>
            <tr>
                <th>Đơn #</th>
                <th>Người dùng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Địa chỉ</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td><?= $o['user_id'] ?: 'Khách' ?></td>
                <td><?= number_format($o['total'], 0, ',', '.') ?> ₫</td>
                <td>
                    <span class="status-badge" style="background: <?= $status_map[$o['status']]['color'] ?>">
                        <?= $status_map[$o['status']]['label'] ?>
                    </span>
                </td>
                <td><?= htmlspecialchars(substr($o['address'], 0, 30)) ?>...</td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <?php if ($o['status'] === 'pending'): ?>
                            <button type="submit" name="action" value="confirm" class="btn btn-confirm">✅ Xác nhận</button>
                        <?php elseif ($o['status'] === 'confirmed'): ?>
                            <button type="submit" name="action" value="ship" class="btn btn-ship">🚚 Giao hàng</button>
                        <?php elseif ($o['status'] === 'shipped'): ?>
                            <button type="submit" name="action" value="deliver" class="btn btn-deliver">📦 Hoàn thành</button>
                        <?php else: ?>
                            <span>✔️ Đã hoàn thành</span>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>