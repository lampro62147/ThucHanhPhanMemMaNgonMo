 <?php
include 'includes/header.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';

    $address = trim($_POST['address'] ?? '');
    if (!$address) {
        $error = "Vui lòng nhập địa chỉ giao hàng.";
    } else {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, address) VALUES (?, ?, ?)");
            // Giả sử user_id = 1 (chưa có hệ thống đăng nhập)
            $stmt->execute([1, $total, $address]);
            $order_id = $pdo->lastInsertId();

            foreach ($_SESSION['cart'] as $id => $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $id, $item['quantity'], $item['price']]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: order-tracking.php?id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollback();
            $error = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
        }
    }
}
?>
<main>
    <h2>Thanh toán</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Địa chỉ giao hàng (bắt buộc):<br>
            <textarea name="address" rows="4" cols="50" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </label><br><br>
        <button type="submit" class="btn">Xác nhận đặt hàng</button>
    </form>
</main>
<?php include 'includes/footer.php'; ?>