<?php
session_start();
require_once '../config.php';

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DunzoQuick/Image/no-image.png';
        if (empty(trim((string)$db_path))) {
            return $default_image;
        }
        $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');
    // 2. Prepend 'Image/' if it's missing.
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    return '/DunzoQuick/' . htmlspecialchars($path);
}

// Fetch electronics products from the database
$products = [];
$sql = "SELECT id, name, price, image, sub_category, unit FROM products WHERE category = 'Electronics' ORDER BY name ASC";
if ($result = $conn->query($sql)) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dunzo Electronics | Fast Delivery of Gadgets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="product.css">
  <link rel="stylesheet" href="page.css">
</head>
<style>
/* Flash Sale Section Styles */
.flash-sale {
  background: linear-gradient(135deg, #ffe6f0, #ff80ab); /* Brighter, vibrant pink gradient */
  padding: 40px 20px;
  margin: 30px auto;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  position: relative;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.flash-sale:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.flash-sale::before {
  content: "";
  position: absolute;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.2), transparent 70%);
  top: -50%;
  left: -50%;
  transform: rotate(45deg);
  animation: shine 6s linear infinite;
  pointer-events: none;
}

@keyframes shine {
  0% { transform: rotate(45deg) translateX(0); }
  100% { transform: rotate(45deg) translateX(50%); }
}

.flash-sale-content h2 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #d81b60; /* Deep pink for headline */
  margin-bottom: 12px;
  letter-spacing: 1px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

.flash-sale-content p {
  font-size: 1.1rem;
  color: #5d4037; /* Darker brownish grey for paragraph */
  margin-bottom: 25px;
  line-height: 1.6;
}

.shop-now-btn {
  background: linear-gradient(90deg, #f50057, #c51162); /* Gradient pink button */
  color: #fff;
  padding: 14px 30px;
  border: none;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 6px 15px rgba(0,0,0,0.2);
  transition: all 0.4s ease;
  text-transform: uppercase;
}

.shop-now-btn:hover {
  background: linear-gradient(90deg, #c51162, #880e4f);
  transform: scale(1.05);
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
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
  </style>
<body>

    <?php include 'header.php'; ?>
  <a href="/DunzoQuick/index.php" class="back-btn">&larr; Back to Home</a>


  <!-- Category Filter -->
  <ul class="grocery-categories">
    <li onclick="filterCategory('all', this)" class="active">📦 All</li>
    <li onclick="filterCategory('charger', this)">🔌 Chargers</li>
    <li onclick="filterCategory('earphones', this)">🎧 Earphones</li>
    <li onclick="filterCategory('cable', this)">🔗 Cables</li>
    <li onclick="filterCategory('powerbank', this)">🔋 Power Banks</li>
  </ul>

   <!-- Flash Sale Section -->



  <!-- Product Grid -->
<section class="product-grid">
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
        <p>No electronic products found. Please check back later!</p>
      <?php endif; ?>
  </section>



  <!-- JavaScript -->
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