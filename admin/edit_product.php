<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<?php
include '../includes/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die('Sản phẩm không tồn tại');
}

// Lấy thông tin hiện tại
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) die('Không tìm thấy sản phẩm');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);

    if ($name && $price > 0) {
        $image = $product['image']; // Giữ ảnh cũ nếu không upload mới
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $image = 'uploads/' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $desc, $image, $stock, $id]);
        header("Location: manage_products.php?updated=1");
        exit;
    }
}
?>
<style>
.edit-container {
    max-width: 700px;
    margin: 30px auto;
    padding: 20px;
}
.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #444;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
}
.btn {
    background: #ee4d2d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}
.btn:hover {
    background: #c63d27;
}
.preview-img {
    max-width: 150px;
    margin-top: 8px;
    border-radius: 6px;
}
</style>

<main class="edit-container">
    <h2>✏️ Sửa sản phẩm</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Giá (₫)</label>
            <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
        </div>
        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Ảnh hiện tại</label><br>
            <img src="<?= $product['image'] ?: 'assets/images/no-image.jpg' ?>" class="preview-img" alt="Ảnh">
        </div>
        <div class="form-group">
            <label>Thay ảnh mới (tuỳ chọn)</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Số lượng tồn kho</label>
            <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0">
        </div>
        <button type="submit" class="btn">Cập nhật sản phẩm</button>
        <a href="manage_products.php" style="display:inline-block; margin-left:10px;">Huỷ</a>
    </form>
</main>

<?php include '../includes/footer.php'; ?>