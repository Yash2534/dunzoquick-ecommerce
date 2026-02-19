<?php
include 'auth_check.php';
include 'db_connect.php';

// Fetch current admin details for header
$admin_id = $_SESSION['admin_id'] ?? 0;
$current_admin = null;
if ($admin_id > 0) {
    $stmt_admin = $conn->prepare("SELECT full_name, profile_photo FROM users WHERE id = ?");
    $stmt_admin->bind_param("i", $admin_id);
    $stmt_admin->execute();
    $current_admin = $stmt_admin->get_result()->fetch_assoc();
    $stmt_admin->close();
}

function get_avatar($photo, $name) {
    if ($photo && file_exists('../' . $photo)) {
        return '../' . htmlspecialchars($photo);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&rounded=true';
}

// Handle Stock Updates
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $action = $_POST['action_type'];

    if ($product_id > 0 && $quantity > 0) {
        if ($action === 'add') {
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $quantity, $product_id);
                if ($stmt->execute()) {
                    $msg = '<div class="alert alert-success">Stock added successfully.</div>';
                } else {
                    $msg = '<div class="alert alert-danger">Failed to add stock.</div>';
                }
                $stmt->close();
            }
        } elseif ($action === 'remove') {
            // Check current stock first
            $stmt_check = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("i", $product_id);
                $stmt_check->execute();
                $res = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if ($res && $res['stock_quantity'] >= $quantity) {
                    $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("ii", $quantity, $product_id);
                        if ($stmt->execute()) {
                            $msg = '<div class="alert alert-success">Stock removed successfully.</div>';
                        } else {
                            $msg = '<div class="alert alert-danger">Failed to remove stock.</div>';
                        }
                        $stmt->close();
                    }
                } else {
                    $msg = '<div class="alert alert-danger">Insufficient stock to remove.</div>';
                }
            }
        }
    }
}

// Fetch Products
$products = [];
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f4f7fe;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #1a202c;
            --text-muted: #718096;
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --border-color: #e2e8f0;
            --danger: #e74c3c;
            --success: #1abc9c;
            --warning: #f1c40f;
            --info: #3498db;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); color: var(--text-color); display: flex; }
        
        .sidebar { width: 260px; background-color: var(--sidebar-bg); height: 100vh; position: fixed; left: 0; top: 0; padding: 20px; display: flex; flex-direction: column; border-right: 1px solid var(--border-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 15px; color: var(--text-muted); font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: all 0.2s ease; text-decoration: none; }
        .sidebar-menu li a:hover { background-color: var(--primary-light); color: var(--primary); }
        .sidebar-menu li a i { font-size: 18px; width: 20px; margin-right: 15px; text-align: center; }

        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 30px; }

        .panel { background-color: var(--card-bg); padding: 25px; border-radius: var(--border-radius); box-shadow: var(--shadow); }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
        .panel-header h3 { font-size: 18px; font-weight: 600; }
        
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        .data-table th { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
        .data-table tbody tr:hover { background-color: #f9fafb; }
        
        .product-info { display: flex; align-items: center; gap: 15px; }
        .product-info img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color); }
        .product-name { font-weight: 500; color: var(--text-color); }
        .product-id { font-size: 12px; color: var(--text-muted); }

        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background-color: rgba(26, 188, 156, 0.15); color: var(--success); }
        .badge-warning { background-color: rgba(241, 196, 15, 0.15); color: var(--warning); }
        .badge-danger { background-color: rgba(231, 76, 60, 0.15); color: var(--danger); }

        .stock-form { display: flex; align-items: center; gap: 8px; }
        .stock-input { width: 70px; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; text-align: center; font-family: inherit; }
        .btn-stock { width: 32px; height: 32px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; color: white; }
        .btn-add { background-color: var(--success); }
        .btn-add:hover { background-color: #16a085; }
        .btn-remove { background-color: var(--danger); }
        .btn-remove:hover { background-color: #c0392b; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background-color: rgba(26, 188, 156, 0.15); color: var(--success); border: 1px solid rgba(26, 188, 156, 0.3); }
        .alert-danger { background-color: rgba(231, 76, 60, 0.15); color: var(--danger); border: 1px solid rgba(231, 76, 60, 0.3); }

        /* Styles for header */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background-color: var(--card-bg); padding: 15px 25px; border-radius: var(--border-radius); box-shadow: var(--shadow); }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .user-profile-dropdown { position: relative; }
        .user-profile { display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 5px; border-radius: 8px; transition: background-color 0.2s ease; }
        .user-profile:hover { background-color: var(--bg-color); }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile .user-details h4 { font-size: 15px; font-weight: 600; margin: 0; line-height: 1.2; }
        .user-profile .user-details p { font-size: 13px; color: var(--text-muted); margin: 0; }
        .user-profile .fa-chevron-down { font-size: 12px; color: var(--text-muted); transition: transform 0.2s ease; }
        .user-profile-dropdown.open .user-profile .fa-chevron-down { transform: rotate(180deg); }
        .dropdown-menu { position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card-bg); border-radius: var(--border-radius); box-shadow: 0 8px 25px rgba(0,0,0,0.1); width: 220px; z-index: 1000; border: 1px solid var(--border-color); overflow: hidden; display: none; animation: fadeInDropdown 0.2s ease-out; }
        .user-profile-dropdown.open .dropdown-menu { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; font-size: 14px; color: var(--text-color); transition: background-color 0.2s ease; }
        .dropdown-item i { width: 16px; text-align: center; color: var(--text-muted); }
        .dropdown-item:hover { background-color: var(--primary-light); }
        .dropdown-item.logout { color: var(--danger); }
        .dropdown-item.logout:hover { background-color: rgba(229, 62, 62, 0.1); }
        .dropdown-item.logout i { color: var(--danger); }
        @keyframes fadeInDropdown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header.php'; ?>

        <?= $msg ?>

        <div class="panel">
            <div class="panel-header">
                <h3>Product Stock Management</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Current Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                    <img src="<?= !empty($p['image']) ? '../Image/' . htmlspecialchars($p['image']) : 'https://via.placeholder.com/50' ?>" alt="Product">
                                        <div>
                                            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="product-id">ID: <?= $p['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>₹<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php 
                                        $stockClass = 'badge-success';
                                        if ($p['stock_quantity'] == 0) $stockClass = 'badge-danger';
                                        elseif ($p['stock_quantity'] < 10) $stockClass = 'badge-warning';
                                    ?>
                                    <span class="badge <?= $stockClass ?>">
                                        <?= $p['stock_quantity'] ?> Units
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="stock-form">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="number" name="quantity" value="1" min="1" class="stock-input" required>
                                        <button type="submit" name="update_stock" value="1" onclick="this.form.action_type.value='add'" class="btn-stock btn-add" title="Add Stock">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="submit" name="update_stock" value="1" onclick="this.form.action_type.value='remove'" class="btn-stock btn-remove" title="Remove Stock">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="hidden" name="action_type" value="add">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px;">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<script>
    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.user-profile-dropdown');
        if (dropdown && dropdown.classList.contains('open') && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });
</script>
</body>
</html>