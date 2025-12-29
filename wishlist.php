<?php
session_start();
include 'config.php'; // Use the central config file

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    // Save the intended destination to redirect back after login
    $_SESSION['redirect_to'] = 'wishlist.php';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Helper function to generate a clean, root-relative image path
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

// Get user wishlist items
$stmt_wishlist = $conn->prepare("
    SELECT p.id, p.name, p.price, p.image
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt_wishlist->bind_param("i", $user_id);
$stmt_wishlist->execute();
$wishlist_items = $stmt_wishlist->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_wishlist->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - DUNZO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .header {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo { font-size: 32px; font-weight: 700; }
        .logo .yellow { color: #febd69; }
        .logo .green { color: #00a651; }
        .back-btn {
            background: #00a651;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        .back-btn:hover {
            background: #008f47;
            color: white;
        }
        .container { max-width: 1200px; }
        .page-header {
            padding: 40px 0;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
        }
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background-color: #f8f9fa;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .product-card-body {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .product-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
            flex-grow: 1;
        }
        .price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #00a651;
            margin-bottom: 15px;
        }
        .product-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .no-items {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
        }
        .no-items i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1056;
            opacity: 0;
            transition: all 0.4s ease;
            transform: translateY(20px);
        }
        .toast-notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <span class="yellow">dun</span><span class="green">zo</span>
            </div>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </header>

    <div class="container mt-4">
        <div class="page-header">
            <h1><i class="fas fa-heart text-danger"></i> My Wishlist</h1>
            <p>Your collection of favorite items.</p>
        </div>

        <?php if (!empty($wishlist_items)): ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="product-card" data-product-id="<?= $item['id'] ?>">
                        <img src="<?= get_image_path($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="product-card-body">
                            <h4 class="product-name"><?= htmlspecialchars($item['name']) ?></h4>
                            <p class="price">₹<?= number_format($item['price'], 2) ?></p>
                            <div class="product-actions">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-wishlist-btn w-50">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                                <button type="button" class="btn btn-sm btn-success add-to-cart-btn w-50">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-items">
                <i class="fas fa-heart-broken"></i>
                <h2>Your wishlist is empty.</h2>
                <p class="text-muted">Looks like you haven’t added anything to your wishlist yet.</p>
                <a href="product.php" class="btn btn-primary mt-3">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification`;
            toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> <span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 3000);
        }

        document.querySelectorAll('.remove-wishlist-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productCard = this.closest('.product-card');
                const productId = productCard.dataset.productId;

                fetch('wishlist_toggle.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.action === 'removed') {
                        showToast('Item removed from wishlist.');
                        productCard.style.transition = 'opacity 0.5s ease';
                        productCard.style.opacity = '0';
                        setTimeout(() => {
                            productCard.remove();
                            if (document.querySelectorAll('.product-card').length === 0) {
                                location.reload(); // Reload to show the "empty" message
                            }
                        }, 500);
                    } else {
                        showToast(data.message || 'Failed to remove item.', 'error');
                    }
                });
            });
        });

        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productCard = this.closest('.product-card');
                const productId = productCard.dataset.productId;
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `add_to_cart=1&product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Item added to cart!');
                    } else {
                        showToast(data.message || 'An error occurred.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>