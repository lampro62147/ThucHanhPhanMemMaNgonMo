<?php
include 'includes/header.php';
include 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('Không có mã đơn hàng');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    die('Đơn hàng không tồn tại');
}

// Mapping trạng thái
$status_map = [
    'pending' => ['label' => '⏳ Chờ xác nhận', 'color' => '#f57c00'],
    'confirmed' => ['label' => '✅ Đã xác nhận', 'color' => '#388e3c'],
    'shipped' => ['label' => '🚚 Đang giao', 'color' => '#1976d2'],
    'delivered' => ['label' => '📦 Đã giao', 'color' => '#0288d1']
];

$current_status = $status_map[$order['status']] ?? ['label' => 'Không xác định', 'color' => '#9e9e9e'];

// Lấy chi tiết sản phẩm
$stmt = $pdo->prepare("SELECT p.name, oi.quantity, oi.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>
<style>
.order-tracking-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    font-family: Arial, sans-serif;
}
.order-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}
.order-header h1 {
    color: #ee4d2d;
    font-size: 2rem;
    margin-bottom: 10px;
}
.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    color: white;
    margin-top: 10px;
}
.address-box {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #ee4d2d;
}
.address-box strong {
    color: #333;
}
.items-list {
    margin: 20px 0;
}
.item-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}
.item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 15px;
}
.item-info {
    flex: 1;
}
.item-name {
    font-weight: bold;
    margin-bottom: 5px;
}
.item-price {
    color: #ee4d2d;
    font-weight: bold;
}
.item-qty {
    color: #666;
    font-size: 0.9rem;
}
.total-box {
    background: #fafafa;
    padding: 15px;
    border-radius: 8px;
    text-align: right;
    font-size: 1.2rem;
    font-weight: bold;
    color: #ee4d2d;
    margin-top: 20px;
}
.back-btn {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 20px;
    transition: background 0.3s;
}
.back-btn:hover {
    background: #5a6268;
}
</style>

<main class="order-tracking-container">
    <div class="order-header">
        <h1>📦 Theo dõi đơn hàng #<?= $id ?></h1>
        <div class="status-badge" style="background: <?= $current_status['color'] ?>;">
            <?= $current_status['label'] ?>
        </div>
    </div>

    <div class="address-box">
        <strong>Địa chỉ giao hàng:</strong><br>
        <?= htmlspecialchars($order['address']) ?>
    </div>

    <h3>Chi tiết sản phẩm:</h3>
    <div class="items-list">
        <?php foreach ($items as $item): ?>
        <div class="item-row">
            <img src="assets/images/no-image.jpg" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
            <div class="item-info">
                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="item-qty">Số lượng: <?= $item['quantity'] ?></div>
            </div>
            <div class="item-price"><?= number_format($item['price'], 0, ',', '.') ?> ₫</div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="total-box">
        Tổng tiền: <?= number_format($order['total'], 0, ',', '.') ?> ₫
    </div>

    <a href="index.php" class="back-btn">← Quay lại trang chủ</a>
</main>

<?php include 'includes/footer.php'; ?>