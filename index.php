<?php
include 'includes/header.php';
include 'includes/db.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<main>
    <h2>Sản phẩm nổi bật</h2>
    <div class="product-grid">
        <?php if ($products): ?>
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <?php
                $image_url = $p['image'] ?? '';
                if ($image_url) {
                    $image_url = '/' . ltrim($image_url, '/');
                }
                $full_path = $_SERVER['DOCUMENT_ROOT'] . $image_url;

                if ($image_url && file_exists($full_path)) {
                    $display_image = $image_url;
                } else {
                    $display_image = 'assets/images/no-image.jpg';
                }
                ?>
                <img src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <p class="price"><?= number_format($p['price'], 0, ',', '.') ?> ₫</p>
                <a href="product.php?id=<?= $p['id'] ?>" class="btn">Xem chi tiết</a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có sản phẩm nào.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>