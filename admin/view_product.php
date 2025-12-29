<?php
session_start();
include '../config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    // Clean up known incorrect prefixes
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    return '/DUNZO/' . htmlspecialchars($path);
}

// Fetch product details from the database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// If product not found, redirect back to the products list
if (!$product) {
    $_SESSION['message'] = "Product not found.";
    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product - DUNZO Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5; /* Indigo */
            --light-gray: #f3f4f6;
            --border-color: #e5e7eb;
            --text-dark: #111827;
            --text-light: #6b7280;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --card-radius: 0.75rem;
        }

        body { 
            background-color: var(--light-gray); 
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
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

        .card { border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); }
        .card-header { background-color: #fff; border-bottom: 1px solid var(--border-color); border-top-left-radius: var(--card-radius); border-top-right-radius: var(--card-radius); }
        .card-header h4 { font-weight: 600; color: var(--text-dark); }
        
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.375rem; transition: background-color 0.2s ease; }
        .btn-primary:hover { background-color: #4338ca; border-color: #4338ca; }
        .btn-secondary { background-color: #fff; border-color: #d1d5db; color: #374151; font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.375rem; transition: background-color 0.2s ease, border-color 0.2s ease; }
        .btn-secondary:hover { background-color: #f9fafb; border-color: #d1d5db; color: #374151; }

        .product-image-lg {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 0.5rem;
            background-color: #fff;
            padding: 1rem;
            border: 1px solid var(--border-color);
        }
        .detail-item { margin-bottom: 1.5rem; }
        .detail-label { font-size: 0.875rem; font-weight: 500; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .detail-value { font-size: 1.125rem; font-weight: 500; }
        .product-name { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .product-price { font-size: 1.75rem; font-weight: 600; color: var(--primary-color); margin-bottom: 1.5rem; }
        .product-description { color: var(--text-light); line-height: 1.6; }
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
    <div class="card">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Product Details</h4>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-5">
                    <img src="<?= get_image_path($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image-lg">
                </div>
                <div class="col-md-7">
                    <h2 class="product-name"><?= htmlspecialchars($product['name']) ?></h2>
                    <p class="product-price">₹<?= number_format($product['price'], 2) ?></p>
                    
                    <div class="detail-item">
                        <p class="detail-label">Description</p>
                        <p class="product-description">
                            <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : '<span class="text-muted">No description provided.</span>' ?>
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 detail-item"><p class="detail-label">Product ID</p><p class="detail-value">#<?= $product['id'] ?></p></div>
                        <div class="col-sm-6 detail-item"><p class="detail-label">Price</p><p class="detail-value">₹<?= number_format($product['price'], 2) ?></p></div>
                        <div class="col-sm-6 detail-item">
                            <p class="detail-label">Date Added</p>
                            <p class="detail-value">
                                <?= !empty($product['created_at']) ? date('M d, Y', strtotime($product['created_at'])) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="col-sm-6 detail-item">
                            <p class="detail-label">Last Updated</p>
                            <p class="detail-value">
                                <?= !empty($product['updated_at']) ? date('M d, Y', strtotime($product['updated_at'])) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white text-end p-3">
            <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Products</a>
            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Edit Product</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>