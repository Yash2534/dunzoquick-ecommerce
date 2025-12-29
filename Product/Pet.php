<?php
session_start();
require_once '../config.php';

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
        if (empty(trim((string)$db_path))) {
            return $default_image;
        }
        $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');

    // Prepend 'Image/' if it's missing
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    
    return '/DUNZO/' . htmlspecialchars($path);
}

// Fetch Pet Care products from the database
$products = [];
$sql = "SELECT id, name, price, image, sub_category, unit FROM products WHERE category = 'Pet Care' ORDER BY name ASC";
if ($result = $conn->query($sql)) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dunzo Pet Care | Product Listing with Filters</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="product.css">
  <link rel="stylesheet" href="page.css">
  <style>
    .back-btn {
  display: inline-block;
  margin: 18px 0 0 18px;
  background: #eee;
  color: #333;
  padding: 6px 14px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #ddd;
}
    /* Toast Notification Styles */
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
        font-weight: 500;
        z-index: 1050;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.4s cubic-bezier(0.215, 0.610, 0.355, 1);
    }
    .toast-notification.show {
        opacity: 1;
        transform: translateY(0);
    }
    .toast-notification.toast-success { background: linear-gradient(135deg, #28a745, #20c997); }
    .toast-notification.toast-danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    .toast-notification.toast-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
  </style>
</head>
<body>

    <?php include 'header.php'; ?>

 <a href="/DUNZO/index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>

  <!-- Offers -->
  <ul class="grocery-categories">
    <li onclick="filterCategory('all', this)" class="active">🍿 All</li>
    <li onclick="filterCategory('food', this)">🍖 Pet Food</li>
    <li onclick="filterCategory('toys', this)">🧸 Toys</li>
    <li onclick="filterCategory('care', this)">🧼 Grooming</li>
    <li onclick="filterCategory('treats', this)">🍪 Treats</li>
  </ul>


    <!-- Product Grid -->
    <section class="product-grid" style="flex: 1; padding: 20px;" id="productGrid">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <div class="product-card" data-product-id="<?= $product['id'] ?>" data-category="<?= htmlspecialchars($product['sub_category'] ?? 'all') ?>">
            <img src="<?= get_image_path($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
            <p class="price">₹<?= number_format($product['price'], 2) ?></p>
            <button class="add-to-cart-btn">Add to Cart</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No pet care products found. Please check back later!</p>
      <?php endif; ?>
    </section>
  </div>


 <script>
    // ✅ Add-to-cart handling (Modern fetch-based)
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const card = btn.closest('.product-card');
        const productId = card.dataset.productId;
        const qty = 1; // Default to 1

        const formData = new FormData();
        formData.append('add_to_cart', '1');
        formData.append('product_id', productId);
        formData.append('quantity', qty);

        fetch('../add_to_cart.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (response.status === 401) {
              showToast('Please log in to add items to your cart.', 'warning');
              setTimeout(() => { window.location.href = '../login.php'; }, 1500);
              return Promise.reject('User not logged in');
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              showToast(data.message || 'Item added to cart!');
              const cartBadge = document.querySelector('.cart-badge');
              if (cartBadge) {
                if (data.cart_count > 0) {
                  cartBadge.textContent = data.cart_count;
                  cartBadge.style.display = 'flex';
                } else {
                  cartBadge.style.display = 'none';
                }
              }
            } else {
              showToast(data.message || 'Failed to add item.', 'danger');
            }
          })
          .catch(error => {
            if (error !== 'User not logged in') {
              console.error('Error:', error);
              showToast('An error occurred. Please try again.', 'danger');
            }
          });
      });
    });

    function filterCategory(category, element) {
      const products = document.querySelectorAll(".product-card");
      products.forEach(product => {
        const productCat = product.getAttribute("data-category").toLowerCase();
        if (category === "all" || productCat === category) {
          product.style.display = ""; // Revert to default display
        } else {
          product.style.display = "none";
        }
      });

      // Update active class on filter buttons
      document.querySelectorAll('.grocery-categories li').forEach(li => li.classList.remove('active'));
      if (element) {
        element.classList.add('active');
      }
    }

    /**
     * Displays a toast notification at the bottom-right of the screen.
     * @param {string} message The message to display.
     * @param {string} type The type of toast ('success', 'danger', 'warning').
     */
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast-notification toast-${type}`;
      toast.textContent = message;
      
      document.body.appendChild(toast);
      
      // Animate in
      setTimeout(() => {
        toast.classList.add('show');
      }, 100);

      // Animate out and remove after 3 seconds
      setTimeout(() => {
        toast.classList.remove('show');
        // Remove the element from the DOM after the transition ends
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
      }, 3000);
    }
  </script>

</body>
</html>