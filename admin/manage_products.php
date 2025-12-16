<?php
session_start();
// Bảo vệ trang admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);

    if ($name && $price > 0) {
        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $image = 'uploads/' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $desc, $image, $stock]);
        $success = "✅ Thêm sản phẩm thành công!";
    }
}

// Lấy danh sách sản phẩm
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body {
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .btn {
            background: #ee4d2d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #c63d27;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }
        .product-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-info h4 {
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        .actions {
            margin-top: 10px;
        }
        .actions a, .actions button {
            margin-right: 10px;
            text-decoration: none;
            font-size: 0.95rem;
        }
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
    <main class="container">
    <a href="dashboard.php" style="
        display: inline-block;
        margin-bottom: 20px;
        padding: 8px 16px;
        background: #f1f1f1;
        color: #333;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        border: 1px solid #ddd;
    ">&larr; Quay lại Dashboard</a>
    <div class="container">
        <h2>📦 Quản lý sản phẩm</h2>

        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Form thêm sản phẩm -->
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Giá (₫)</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Số lượng tồn kho</label>
                    <input type="number" name="stock" value="10" min="0">
                </div>
                <button type="submit" class="btn">Thêm sản phẩm</button>
            </form>
        </div>

        <!-- Danh sách sản phẩm -->
        <h3>📋 Danh sách sản phẩm (<?= count($products) ?> sản phẩm)</h3>

        <?php if (!empty($products)): ?>
            <div class="product-list">
                <?php foreach ($products as $p): ?>
                <div class="product-item">
                    <?php
                    // Lấy đường dẫn ảnh từ CSDL
                    $image_url = $p['image'] ?? '';

                    // Nếu có ảnh, chuyển thành URL tuyệt đối từ root web
                    if ($image_url) {
                        // Loại bỏ các dấu / thừa ở đầu
                        $image_url = '/' . ltrim($image_url, '/');
                    }

                    // Kiểm tra nếu ảnh tồn tại trên server (dùng DOCUMENT_ROOT)
                    $full_path = $_SERVER['DOCUMENT_ROOT'] . $image_url;

                    // Nếu file tồn tại -> dùng ảnh thật, ngược lại -> dùng ảnh mặc định
                    if ($image_url && file_exists($full_path)) {
                        $display_image = $image_url;
                    } else {
                        $display_image = 'assets/images/no-image.jpg';
                    }
                    ?>
                    <img src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <div class="product-info">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <p><strong><?= number_format($p['price'], 0, ',', '.') ?> ₫</strong></p>
                        <p>📦 Tồn kho: <?= $p['stock'] ?></p>
                        <div class="actions">
                            <a href="edit_product.php?id=<?= $p['id'] ?>" style="color:#1976d2;">✏️ Sửa</a>
                            <form method="POST" style="display:inline;" action="delete_product.php" onsubmit="return confirm('Xác nhận xóa sản phẩm này?')">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" style="background:none; border:none; color:#d32f2f; cursor:pointer;">🗑️ Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Chưa có sản phẩm nào. Hãy thêm sản phẩm đầu tiên!</p>
        <?php endif; ?>
    </div>
</body>
</html>