<?php
session_start();
include '../config.php'; // Connect to the database

function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    return '/DUNZO/' . htmlspecialchars($path);
}

$product = null;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category']);
    $sub_category = trim($_POST['sub_category']);
    $description = trim($_POST['description']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    $stock = (int)$_POST['stock'];
    $unit = trim($_POST['unit']);
    $current_image = $_POST['current_image'];

    // Basic validation
    if (empty($name) || $price <= 0 || empty($category) || empty($unit)) {
        $error_message = "Product name, price, category, and unit are required.";
    } else {
        $image_path = $current_image; // Default to current image

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../Image/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (in_array($file_type, $allowed_types)) {
                // Create a unique filename to avoid conflicts
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('prod_', true) . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = $new_filename; // Use the new image path
                    // Optional: Delete the old image if it's not a placeholder
                    if (!empty($current_image) && file_exists($upload_dir . basename($current_image))) {
                        unlink($upload_dir . basename($current_image));
                    }
                } else {
                    $error_message = "Failed to move uploaded file.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        }

        if (empty($error_message)) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ?, sub_category = ?, description = ?, image = ?, stock = ?, stock_quantity = ?, unit = ? WHERE id = ?");
            $stmt->bind_param("sdssssiisi", $name, $price, $category, $sub_category, $description, $image_path, $stock, $stock_quantity, $unit, $product_id);
            
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Product updated successfully!";
                header("Location: view_product.php?id=" . $product_id);
                exit();
            } else {
                $error_message = "Database update failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error_message = "Product not found.";
    }
    $stmt->close();
} else {
    $error_message = "No product ID specified.";
}

$categories = [];
$result_cats = $conn->query("SELECT name FROM categories ORDER BY name ASC");
if ($result_cats) {
    while ($row_cat = $result_cats->fetch_assoc()) {
        $categories[] = $row_cat['name'];
    }
    $result_cats->free();
} else {
    // Append to error message in case another error is already present
    $error_message .= " Could not fetch categories from the database.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - DUNZO Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .current-img-preview {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 5px;
            margin-top: 10px;
        }
        .navbar {
            background-color: white !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border-bottom: 1px solid var(--border-color);
        }
        .navbar-brand strong { color: var(--text-dark); }
        .navbar .nav-link { color: var(--text-light); font-weight: 500; }
        .navbar .nav-link.active, .navbar .nav-link:hover { color: var(--primary-color); }
        .btn-outline-light { border-color: #d1d5db; color: #374151; }
        .btn-outline-light:hover { background-color: #f9fafb; color: #374151; }

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><strong>DUNZO Admin</strong></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
            </ul>
            <a href="../index.php" target="_blank" class="btn btn-outline-light me-2">View Site</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <?php if ($product): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Product</h4>
            <a href="view_product.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to View</a>
        </div>
        <div class="card-body p-4">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image']) ?>">

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="" disabled>Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= (($product['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sub_category" class="form-label">Sub-Category</label>
                            <input type="text" class="form-control" id="sub_category" name="sub_category" value="<?= htmlspecialchars($product['sub_category'] ?? '') ?>">
                            <div class="form-text">Optional. E.g., 'shampoo', 'juice', 'chips'.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity'] ?? '0') ?>" required>
                            </div>
                             <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Total Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($product['stock'] ?? '100') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?= htmlspecialchars($product['unit'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <p class="form-text">Upload a new image to replace the current one.</p>
                            <input class="form-control" type="file" id="image" name="image">
                            <div class="mt-3">
                                <label class="form-label">Current Image:</label><br>
                                <img src="<?= get_image_path($product['image']) ?>" alt="Current Image" class="current-img-preview">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-danger text-center">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Error</h4>
        <p><?= htmlspecialchars($error_message) ?></p>
        <a href="products.php" class="btn btn-secondary">Return to Products List</a>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>