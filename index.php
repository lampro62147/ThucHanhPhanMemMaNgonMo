<?php
include 'includes/header.php';
include 'includes/db.php';

// Xử lý tìm kiếm
$search = $_GET['search'] ?? '';

// Xử lý sort
$sort = $_GET['sort'] ?? 'newest'; // Mặc định mới nhất
$order_by = '';

switch ($sort) {
    case 'price_asc':
        $order_by = 'price ASC';
        $sort_text = 'Giá: Thấp đến Cao';
        break;
    case 'price_desc':
        $order_by = 'price DESC';
        $sort_text = 'Giá: Cao đến Thấp';
        break;
    case 'name_asc':
        $order_by = 'name ASC';
        $sort_text = 'Tên: A-Z';
        break;
    case 'name_desc':
        $order_by = 'name DESC';
        $sort_text = 'Tên: Z-A';
        break;
    default:
        $order_by = 'id DESC';
        $sort_text = 'Mới nhất';
}

// Query sản phẩm với tìm kiếm và sort
$sql = "SELECT * FROM products";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE name LIKE ?";
    $params[] = "%$search%";
}

if ($order_by) {
    $sql .= " ORDER BY $order_by";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<main>
    <h2>Sản phẩm nổi bật</h2>
    
    <!-- Thêm form tìm kiếm -->
    <div class="search-container">
        <form method="get" class="search-form">
            <input type="text" 
                   name="search" 
                   placeholder="Tìm kiếm sản phẩm..." 
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="clear-search">Xóa tìm kiếm</a>
            <?php endif; ?>
        </form>
        
        <!-- Hiển thị kết quả tìm kiếm -->
        <?php if (!empty($search)): ?>
            <div class="search-results">
                <p>Tìm thấy <?= count($products) ?> sản phẩm cho từ khóa: 
                    <strong>"<?= htmlspecialchars($search) ?>"</strong>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Thêm dropdown sort -->
    <div class="sort-container">
        <form method="get" class="sort-form">
            <!-- Giữ lại tham số tìm kiếm -->
            <?php if (!empty($search)): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>
            
            <label for="sort">Sắp xếp:</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên: A-Z</option>
                <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Tên: Z-A</option>
            </select>
        </form>
        <div class="current-sort">
            <small>Đang xem: <?= htmlspecialchars($sort_text) ?></small>
        </div>
    </div>
    
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