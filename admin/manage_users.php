<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

// --- XỬ LÝ XÓA NGƯỜI DÙNG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = (int)$_POST['user_id'];
    // Không cho xóa chính admin đang đăng nhập
    if ($user_id !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        $message = "✅ Xóa người dùng thành công!";
    } else {
        $error = "❌ Không thể xóa chính tài khoản admin đang đăng nhập!";
    }
}

// --- XỬ LÝ CẬP NHẬT NGƯỜI DÙNG ---
$update_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';

    if ($username && $email) {
        // Kiểm tra email có bị trùng không (trừ chính người dùng này)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = "❌ Email này đã được sử dụng bởi người dùng khác!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $user_id]);
            $update_success = "✅ Cập nhật thông tin thành công!";
            // Sau khi cập nhật, quay lại danh sách
            header("Location: manage_users.php?updated=1");
            exit;
        }
    } else {
        $error = "❌ Vui lòng nhập đầy đủ thông tin!";
    }
}

// --- CHẾ ĐỘ SỬA: LẤY DỮ LIỆU NGƯỜI DÙNG ---
$edit_mode = false;
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
    if ($edit_user) {
        $edit_mode = true;
    }
}

// --- LẤY DANH SÁCH NGƯỜI DÙNG (nếu không ở chế độ sửa) ---
if (!$edit_mode) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="webtoken" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Sửa người dùng' : 'Quản lý người dùng' ?></title>
    <base href="http://localhost:3000/">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        header {
            background-color: #ee4d2d; color: white; padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        header h1 { font-size: 1.8rem; }
        header nav a {
            color: white; text-decoration: none; margin-left: 15px;
            padding: 5px 10px; border-radius: 4px;
        }
        header nav a:hover { background: rgba(255,255,255,0.2); }
        .header-user { display: inline-block; margin-left: 15px; }

        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
        .alert-error { background: #ffebee; color: #c62f2f; }

        .user-table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .user-table th, .user-table td {
            padding: 14px; text-align: left; border-bottom: 1px solid #eee;
        }
        .user-table th { background: #f8f9fa; font-weight: 600; color: #555; }
        .user-table tr:last-child td { border-bottom: none; }
        .role-admin { background: #ffebee; color: #c62828; padding: 2px 8px; border-radius: 4px; }

        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-edit { background: #1976d2; color: white; }
        .btn-delete { background: #d32f2f; color: white; }
        .btn:hover { opacity: 0.9; }

        .form-section {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .back-btn {
            display: inline-block; margin-top: 10px; color: #ee4d2d;
            text-decoration: none; font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>🛒 Shoppee Clone</h1>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="cart.php">Giỏ hàng (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="header-user">Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="admin/dashboard.php">Admin</a>
            <a href="logout.php">Đăng xuất</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">✅ Cập nhật thông tin thành công!</div>
    <?php endif; ?>

    <?php if ($edit_mode): ?>
        <!-- FORM SỬA NGƯỜI DÙNG -->
        <h2>✏️ Sửa thông tin người dùng</h2>
        <div class="form-section">
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Vai trò</label>
                    <select name="role" class="form-control">
                        <option value="user" <?= $edit_user['role'] === 'user' ? 'selected' : '' ?>>Người dùng</option>
                        <option value="admin" <?= $edit_user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn btn-edit">Lưu thay đổi</button>
                <a href="manage_users.php" class="back-btn">← Hủy và quay lại</a>
            </form>
        </div>
    <?php else: ?>
        <!-- DANH SÁCH NGƯỜI DÙNG -->
        <h2>👥 Quản lý người dùng</h2>
        <p>Tổng số: <?= count($users) ?> người dùng</p>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
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
                    <!-- Trong phần hiển thị danh sách người dùng -->
                    <td>
                        <a href="/admin/manage_users.php?edit=<?= $u['id'] ?>" class="btn btn-edit">✏️ Sửa</a>
                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa người dùng này?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-delete">🗑️ Xóa</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#999;">(Không thể xóa)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>