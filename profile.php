<?php
session_start();
include 'config.php'; // Use the central config file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';


// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])) {
    $upload_dir = 'uploads/profile_photos/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES['profile_photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($file['type'], $allowed_types) && $file['error'] == 0) {
        $filename = $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database with new photo path
            $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $filepath, $user_id);
            $stmt->execute();
            $message = "Profile photo updated successfully!";
            // No need to re-fetch user, just update the array for immediate display
            // But a full refresh is safer in case other data changes.
            header("Location: profile.php"); // Redirect to show changes and prevent resubmission
            exit();
        } else {
            $error = "Failed to upload file";
        }
    } else {
        $error = "Invalid file type or upload error";
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get all user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>My Profile - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #333;
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

        .logo {
            font-size: 32px;
            font-weight: 700;
        }

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
            text-decoration: none;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .profile-photo {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00a651;
        }

        .profile-photo .upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #00a651;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .profile-photo .upload-btn:hover {
            background: #008f47;
        }

        .profile-info h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .profile-info p {
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .profile-info .email {
            font-weight: 500;
            color: #00a651;
        }

        .profile-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section h2 i {
            color: #00a651;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #666;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #c6f6d5;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .no-orders, .no-items {
            text-align: center;
            color: #666;
            padding: 40px 20px;
        }

        .no-orders i, .no-items i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }

        .upload-form {
            display: none;
        }

        .orders-summary {
            text-align: center;
            padding: 20px;
        }

        .orders-summary p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #666;
        }

        .view-orders-btn {
            background: #00a651;
            border-color: #00a651;
        }

        .view-orders-btn:hover {
            background: #008f47;
            border-color: #008f47;
        }

        /* Wishlist Section */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
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
        
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }
        .order-list-item:hover {
            background-color: #f1f3f5;
        }

        .order-id {
            font-weight: 600;
            color: #333;
        }

        .order-total {
            color: #00a651;
        }

        .logo-sm { font-size: 24px; font-weight: 700; }
        .logo-sm .yellow { color: #febd69; }
        .logo-sm .green { color: #00a651; }

        @media print {
            body * { visibility: hidden; }
            .modal-backdrop { display: none !important; }
            #invoiceModal .modal-content, #invoiceModal .modal-content * { visibility: visible; }
            #invoiceModal { position: absolute; left: 0; top: 0; overflow: visible !important; }
        }

        .product-card img {
            width: 100%;
            height: 180px;
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

        .product-card .product-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
            flex-grow: 1; /* Pushes price and actions down */
        }

        .product-card .price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #00a651;
            margin-bottom: 15px;
        }

        .product-card .product-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1056; /* Higher than bootstrap modal backdrop */
            opacity: 1;
            transition: opacity 0.5s ease, transform 0.5s ease;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .profile-sections {
                grid-template-columns: 1fr;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .container {
                margin: 20px auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-photo">
                <?php if ($user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
                <?php else: ?>
                    <img src="https://via.placeholder.com/120x120/00a651/ffffff?text=<?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>" alt="Profile Photo">
                <?php endif; ?>
                <button class="upload-btn" onclick="document.getElementById('photoUpload').click()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>

            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p>Member since: <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>

            <div class="ms-auto mt-3 mt-md-0">
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Profile Sections -->
        <div class="profile-sections">
            <!-- Personal Information -->
            <div class="section">
                <h2><i class="fas fa-user"></i> Personal Information</h2>
                
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['mobile'] ?? 'Not provided'); ?></span>
                </div>
                
            
                
                <div class="info-item">
                    <span class="info-label">Account Created</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>

            <!-- Saved Orders -->
            <div class="section">
                <h2><i class="fas fa-shopping-bag"></i> My Orders</h2>

                <?php if (!empty($orders)): ?>
                    <div class="orders-summary">
                        <p>You have placed <strong><?= count($orders) ?></strong> order(s) with us.</p>
                        <a href="order.php" class="btn btn-primary view-orders-btn">
                            <i class="fas fa-receipt"></i> View My Orders
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <p>No orders yet</p>
                        <a href="product.php" class="btn btn-primary mt-3 view-orders-btn">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hidden file upload form -->
        <form id="uploadForm" class="upload-form" method="POST" enctype="multipart/form-data">
            <input type="file" id="photoUpload" name="profile_photo" accept="image/*" onchange="this.form.submit()">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form when file is selected
        document.getElementById('photoUpload').addEventListener('change', function() {
            if (this.files.length > 0) {
                this.form.submit();
            }
        });

        // Toast notification function
        function showToast(message, type = 'success') {
          const existingToast = document.querySelector('.toast-notification');
          if (existingToast) {
            existingToast.remove();
          }

          const toast = document.createElement('div');
          toast.className = `toast-notification`;
          toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
          toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
          `;
          document.body.appendChild(toast);

          setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.addEventListener('transitionend', () => toast.remove());
          }, 3000);
        }

        // Add to Cart logic
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productCard = this.closest('.product-card');
                const productId = productCard.dataset.productId;
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                this.disabled = true;

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1); // Default quantity
                formData.append('add_to_cart', '1'); // Action identifier

                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Item added to cart!');
                        // Update cart badge in header
                        const cartBadge = document.querySelector('.header .cart-badge');
                        if (cartBadge) {
                            if (data.cart_count > 0) {
                                cartBadge.style.display = 'flex';
                                cartBadge.textContent = data.cart_count;
                            } else {
                                cartBadge.style.display = 'none';
                            }
                        }
                    } else {
                        showToast(data.message || 'An error occurred.', 'error');
                    }
                })
                .catch(error => showToast('An error occurred.', 'error'))
                .finally(() => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
