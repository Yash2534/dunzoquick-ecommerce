<?php
session_start();
include '../config.php'; // Connect to the database
 
// Helper function to generate a clean, root-relative image path.
// This ensures consistency across the application (copied from cart.php).
function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    // Clean up known incorrect prefixes and leading slashes
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');

    // Prepend 'Image/' if it's not already part of a sub-directory like 'Image/' or 'PICTURE/'
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    
    return '/DUNZO/' . htmlspecialchars($path);
}
// --- Handle Delete Action ---
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id_to_delete = (int)$_GET['delete'];

    // Recommended: Also delete the associated image file from the server
    $stmt_img = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt_img->bind_param("i", $product_id_to_delete);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    if ($row = $result_img->fetch_assoc()) {
        $image_file = $row['image'];
        // Make sure the file exists and is not a directory before deleting
        if ($image_file && file_exists('../Image/' . basename($image_file))) {
            @unlink('../Image/' . basename($image_file));
        }
    }
    $stmt_img->close();

    // Delete the product record from the database
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->bind_param("i", $product_id_to_delete);
    if ($stmt_delete->execute()) {
        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        $_SESSION['message'] = "Error: Could not delete product.";
    }
    $stmt_delete->close();
    header("Location: products.php");
    exit();
}

// --- Fetch All Products ---
// The original query caused a fatal error because the 'products' table in your database
// is missing columns like 'store_id', 'category', and 'created_at'.
// This updated query fetches only the columns that actually exist and orders by ID
// to show the most recently added products first.
$sql = "SELECT
            p.id, p.name, p.price, p.image
        FROM 
            products p
        ORDER BY 
            p.id DESC";
$result = $conn->query($sql);
$products = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - DUNZO Admin</title>
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
            --danger-color: #ef4444;
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

        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); font-weight: 500; padding: 0.6rem 1.2rem; border-radius: 0.375rem; transition: background-color 0.2s ease; }
        .btn-primary:hover { background-color: #4338ca; border-color: #4338ca; }

        .table { border-collapse: separate; border-spacing: 0; }
        .table thead { color: var(--text-light); font-weight: 500; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table th { border-bottom: 2px solid var(--border-color); border-top: none; padding-top: 1rem; padding-bottom: 1rem; background: #fff; }
        .table td { border-top: 1px solid var(--border-color); }
        .table tbody tr:hover { background-color: #f9fafb; }
        
        .product-img { width: 48px; height: 48px; object-fit: cover; border-radius: 0.5rem; background-color: #f3f4f6; }
        
        .action-icons a { margin: 0 6px; color: var(--text-light); text-decoration: none; transition: color 0.2s ease; font-size: 1rem; }
        .action-icons a:hover { color: var(--primary-color); }
        .action-icons a.delete:hover { color: var(--danger-color); }

        .alert-success { background-color: #dcfce7; border-color: #bbf7d0; color: #15803d; }
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
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-box-open me-2"></i>Manage Products</h4>
            <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New Product</a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><strong>#<?= $product['id'] ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= get_image_path($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img me-3">
                                            <span class="fw-bold"><?= htmlspecialchars($product['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>₹<?= number_format($product['price'], 2) ?></td>
                                    <td class="text-end action-icons">
                                        <a href="view_product.php?id=<?= $product['id'] ?>" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="products.php?delete=<?= $product['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to permanently delete this product?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <h5 class="text-muted">No products found.</h5>
                                    <p class="text-muted mb-0">Get started by adding your first product.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>