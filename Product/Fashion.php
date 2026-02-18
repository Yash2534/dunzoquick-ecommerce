<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Fashion Store | Full Category Listing</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php
  // Use include_once to prevent errors if config is already included in the header.
  // Using __DIR__ makes the path more reliable.
  include_once __DIR__ . '/../config.php';

  // Helper function to generate a clean, root-relative image path
  function get_image_path($db_path) {
      $default_image = '/DunzoQuick/Image/no-image.png';
        if (empty(trim((string)$db_path))) {
            return $default_image;
        }
        $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
      $path = ltrim($path, '/');
     
      if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
          $path = 'Image/' . $path;
      }
      
      return '/DunzoQuick/' . htmlspecialchars($path);
  }

  // Fetch all Fashion products from the database
  $category = 'Fashion';

  $stmt = $conn->prepare("SELECT id, name, price, image, unit, sub_category FROM products WHERE category = ? ORDER BY name ASC");
  $stmt->bind_param("s", $category);
  $stmt->execute();
  $result = $stmt->get_result();
  $products = $result->fetch_all(MYSQLI_ASSOC);
  ?>
  <link rel="stylesheet" href="product.css">
  <link rel="stylesheet" href="page.css">
  <style>
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
    /* Banner styles */
  .fashion-banner {
    background: linear-gradient(135deg, #f0f4c3, #dce775);
    /* Light yellow-green gradient */
    border-radius: 16px;
    padding: 25px 35px;
    margin: 30px auto;
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #cddc39;
    /* A soft yellow-green border */
  }

  .fashion-banner .banner-text {
    max-width: 55%;
    z-index: 2;
    text-align: left;
  }

  .fashion-banner h2 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #558b2f;
    /* Dark olive green for contrast */
    margin-bottom: 10px;
    line-height: 1.3;
  }

  .fashion-banner p {
    font-size: 1.1rem;
    color: #689f38;
    margin-bottom: 25px;
  }

  .fashion-banner .cta-button {
    background-color: #1b5e20;
    /* Dark blue */
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    display: inline-block;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(27, 94, 32, 0.2);
  }

  .fashion-banner .cta-button:hover {
    background-color: #00008B;
    box-shadow: 0 6px 20px rgba(27, 94, 32, 0.3);
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
  </style>
</head>

<body>
    <?php include 'header.php'; ?>
  <a href="/DunzoQuick/index.php" class="back-btn">&larr; Back to Home</a>





  <section class="product-grid" id="productGrid">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <div class="product-card" data-product-id="<?= $product['id'] ?>" data-category="<?= htmlspecialchars(strtolower($product['sub_category'] ?? 'all')) ?>">
            <img src="<?= get_image_path($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
            <p class="price">₹<?= number_format($product['price'], 2) ?></p>
            <button class="add-to-cart-btn">Add to Cart</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No fashion products found. Please check back later!</p>
      <?php endif; ?>
  </section>


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
        document.querySelectorAll(".product-card").forEach(product => {
          const productCat = product.getAttribute("data-category").toLowerCase();
          if (category === "all" || productCat === category) {
            product.style.display = ""; // Revert to default display
          } else {
            product.style.display = "none";
          }
        });
        document.querySelectorAll('.grocery-categories li').forEach(button => button.classList.remove('active'));
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
        setTimeout(() => {
          toast.classList.add('show');
        }, 100);
        setTimeout(() => {
          toast.classList.remove('show');
          toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        }, 3000);
      }
    </script>
</body>


</html>