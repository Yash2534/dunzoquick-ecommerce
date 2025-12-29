<?php
session_start();
include 'config.php';

// --- Search Logic ---
// Get the search query from the URL, if it exists.
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Wishlist Logic ---
// Fetch user's wishlist if logged in. This helps us mark products that are already wishlisted.
$wishlist_items = [];
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $wishlist_stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
  $wishlist_stmt->bind_param('i', $user_id);
  $wishlist_stmt->execute();
  $wishlist_result = $wishlist_stmt->get_result();
  while ($row = $wishlist_result->fetch_assoc()) {
    $wishlist_items[] = $row['product_id'];
  }
  $wishlist_stmt->close();
}

// --- Pagination Logic ---
$products_per_page = 10; // Set how many products to show per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $products_per_page;

// --- Count Total Products for Pagination ---
$count_sql = "SELECT COUNT(id) as total FROM products";
if (!empty($search_query)) {
    $count_sql .= " WHERE name LIKE ?";
}
$count_stmt = $conn->prepare($count_sql);
if (!empty($search_query)) {
    $search_term_for_like_count = "%" . $search_query . "%";
    $count_stmt->bind_param('s', $search_term_for_like_count);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_products = $count_result['total'] ?? 0;
$total_pages = ceil($total_products / $products_per_page);
$count_stmt->close();
?>
<?php
// Helper function to generate a clean, root-relative image path
function get_image_path($db_path)
{
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title> DUNZO- Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Fonts & Font Awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .wishlist-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: transparent;
      /* No background color, as requested */
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1.4rem;
      /* Made icon slightly larger */
      color: #878787;
      /* Flipkart-style grey for the icon */
      transition: all 0.2s ease-in-out;
      z-index: 10;
    }

    .wishlist-btn:hover {
      background: rgba(0, 0, 0, 0.08);
      /* Subtle circle background on hover */
      transform: scale(1.1);
    }

    .wishlist-btn.active {
      color: #ff6161;
      /* Flipkart-style red for the icon */
      background: transparent;
      /* Ensure no background when active */
    }

    .wishlist-btn.active:hover {
      background: rgba(255, 97, 97, 0.1);
      /* Faint red background on hover when active */
      color: #ff6161;
      /* Keep the icon red */
    }

    /* Animation for when the button becomes active */
    .wishlist-btn.popping .fa-heart {
      animation: heart-pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes heart-pop {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.5);
      }

      100% {
        transform: scale(1);
      }
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
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.4s cubic-bezier(0.215, 0.610, 0.355, 1);
    }

    .toast-notification.show {
      opacity: 1;
      transform: translateY(0);
    }

    .toast-notification.toast-success {
      background: linear-gradient(135deg, #28a745, #20c997);
    }

    .toast-notification.toast-danger {
      background: linear-gradient(135deg, #dc3545, #c82333);
    }

    .toast-notification.toast-warning {
      background: linear-gradient(135deg, #ffc107, #e0a800);
    }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: #f7f8fa;
      margin: 0;
      padding: 0;
    }


    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 24px;
      padding: 0 32px;
      margin: 30px auto;
      max-width: 1200px;
    }

    .product-card {
      /* flex property is replaced by the grid layout */
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      padding: 15px;
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      /* margin-bottom is handled by grid gap */
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
    }

    .product-card img {
      max-width: 100%;
      height: 120px;
      object-fit: contain;
      margin-bottom: 10px;
    }

    .product-card h4 {
      margin: 10px 0 5px;
      font-size: 1rem;
      font-weight: 600;
      /* Set a fixed height and use ellipsis to handle long titles, ensuring uniform card height. */
      height: 48px;
      /* Accommodates two lines of text */
      line-height: 1.5;
      overflow: hidden;
      display: -webkit-box;
    }

    .price {
      font-weight: bold;
      color: #e65100;
      margin: 5px 0 15px;
    }

    .product-actions {
      margin-top: auto;
      /* This pushes the actions to the bottom of the card */
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      width: 100%;
    }



    .product-card .quantity-input {
      width: 48px;
      height: 36px;
      padding: 0;
      border: none;
      border-left: 1px solid #ddd;
      border-right: 1px solid #ddd;
      background: white;
      text-align: center;
      font-weight: 600;
      font-size: 1rem;
      color: #333;
    }

    .product-card .quantity-input::-webkit-outer-spin-button,
    .product-card .quantity-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    .product-card .add-to-cart-btn {
      background: #f0fff4;
      /* A very light, fresh green */
      color: #047857;
      /* A deep, readable green */
      border: 2px solid transparent;
      /* No border by default for a cleaner look */
      padding: 8px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      /* Slightly bolder for better readability */
      transition: all 0.2s ease-in-out;
      width: 100%;
    }

    .product-card .add-to-cart-btn:hover {
      background: #d1fae5;
      /* A slightly more saturated green on hover */
      border-color: #a7f3d0;
      /* Show a soft border on hover */
      transform: translateY(-2px);
      /* Add a subtle lift effect */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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

    /* Pagination Styles */
    .pagination-container {
      display: flex;
      justify-content: center;
      justify-content: space-between;
      align-items: center;
      padding: 30px 32px;
      margin: 20px auto;
      max-width: 1200px;
    }
    .pagination-btn {
      padding: 10px 20px;
      border: 1px solid #ddd;
      background: white;
      color: #333;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    .pagination-btn:hover { background: #f0f0f0; }
    .pagination-btn.disabled { pointer-events: none; background: #f8f9fa; color: #aaa; border-color: #eee; }
    .page-info { font-weight: 600; color: #555; }

    /* The media query for 500px is also not needed for the grid itself */
  </style>
</head>
<?php include 'product/header.php'; ?>

<body>

  <?php
  // Check for a feedback message from the session to display as a toast notification
  if (isset($_SESSION['message'])) {
    // Use json_encode to safely pass the strings to JavaScript
    $message = json_encode($_SESSION['message']);
    $message_type = json_encode($_SESSION['message_type'] ?? 'success');

    // Echo a script that will call the toast function once the page is loaded
    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast($message, $message_type); });</script>";

    // Clear the message from the session so it doesn't show on refresh
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
  }
  ?>

  <div style="padding: 18px 18px 0 18px; display: flex; justify-content: space-between; align-items: center; max-width: 1244px; margin: 0 auto;">
    <a href="Index.php" class="back-btn">&larr; Back to Home</a>
   </div>
  <br /><br />

  <?php if (!empty($search_query)): ?>
    <h2 class="search-results-heading">
      Showing results for: "<?= htmlspecialchars($search_query) ?>"
    </h2>
  <?php endif; ?>

  <!-- Product Grid -->
  <section class="product-grid">
    <?php
    // --- Product Fetching Logic ---
    // Base SQL query
    $sql = "SELECT id, name, price, image FROM products";

    // If a search query is provided, add a WHERE clause
    if (!empty($search_query)) {
      $sql .= " WHERE name LIKE ?";
      $search_term_for_like = "%" . $search_query . "%";
    }

    // Add ORDER BY clause
    // If searching, order by name for relevance. Otherwise, shuffle.
    if (!empty($search_query)) {
      $sql .= " ORDER BY name ASC";
    } else {
      $sql .= " ORDER BY RAND()";
    }

    // Add LIMIT and OFFSET for pagination
    $sql .= " LIMIT ? OFFSET ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $types = '';
    $params = [];
    if (!empty($search_query)) {
      $params[] = &$search_term_for_like;
      $types .= 's';
    }
    $params[] = &$products_per_page;
    $params[] = &$offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      while ($product = $result->fetch_assoc()) {
    ?>
        <?php $is_in_wishlist = in_array($product['id'], $wishlist_items); ?>
        <div class="product-card" data-product-id="<?= $product['id'] ?>">
          <button class="wishlist-btn <?= $is_in_wishlist ? 'active' : '' ?>" title="<?= $is_in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
            <i class="fa-heart <?= $is_in_wishlist ? 'fas' : 'far' ?>"></i>
          </button>
          <img src="<?= get_image_path($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
          <p class="price">₹<?= number_format($product['price'], 2) ?></p>
          <div class="product-actions">
            <button type="button" class="add-to-cart-btn">
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
          </div>
        </div>
    <?php
      }
    } else {
      if (!empty($search_query)) {
        echo "<p style='width: 100%; text-align: center; grid-column: 1 / -1;'>No products found matching your search for &quot;" . htmlspecialchars($search_query) . "&quot;.</p>";
      } else {
        echo "<p style='width: 100%; text-align: center; grid-column: 1 / -1;'>No products found.</p>";
      }
    }
    ?>
  </section>

  <!-- Pagination Controls -->
  <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <?php
            $query_params = !empty($search_query) ? 'search=' . urlencode($search_query) . '&' : '';
        ?>
        <a href="?<?= $query_params ?>page=<?= $current_page - 1 ?>" 
           class="pagination-btn <?= ($current_page <= 1) ? 'disabled' : '' ?>">
           &larr; Previous
        </a>

        <span class="page-info">Page <?= $current_page ?> of <?= $total_pages ?></span>

        <a href="?<?= $query_params ?>page=<?= $current_page + 1 ?>" 
           class="pagination-btn <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
           Next &rarr;
        </a>
    </div>
  <?php endif; ?>

  <?php include 'includes/footer.php'; ?>


  <script>
    /* --- Add to Cart Logic (AJAX) --- */
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const card = btn.closest('.product-card');
        const productId = card.dataset.productId;
        const qty = 1; // Default to 1 for product cards

        const formData = new FormData();
        formData.append('add_to_cart', '1');
        formData.append('product_id', productId);
        formData.append('quantity', qty);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (response.status === 401) {
              showToast('Please log in to add items to your cart.', 'warning');
              setTimeout(() => {
                window.location.href = 'login.php';
              }, 1500);
              return Promise.reject('User not logged in');
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              showToast(data.message || 'Item added to cart!');
              // Update cart badge in the header
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
              showToast(data.message || 'An error occurred.', 'danger');
            }
          })
          .catch(error => {
            if (error !== 'User not logged in') {
              console.error('Error:', error);
              showToast('An error occurred.', 'danger');
            }
          });
      });
    });

    /* --- Wishlist Toggle Logic --- */
    document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        <?php if (!isset($_SESSION['user_id'])): ?>
          alert('Please log in to manage your wishlist.');
          window.location.href = 'login.php';
          return;
        <?php endif; ?>

        const card = btn.closest('.product-card');
        const productId = card.dataset.productId;
        const productName = card.querySelector('.product-name').textContent;
        const icon = btn.querySelector('i.fa-heart');

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('wishlist_toggle.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              const isActive = btn.classList.toggle('active');
              icon.classList.toggle('far');
              icon.classList.toggle('fas');
              btn.title = isActive ? 'Remove from Wishlist' : 'Add to Wishlist';

              // Add a 'pop' animation when adding to wishlist
              if (isActive) {
                btn.classList.add('popping');
                // Remove the class after the animation finishes
                btn.addEventListener('animationend', () => {
                  btn.classList.remove('popping');
                }, {
                  once: true
                });
              }
              updateWishlist(productId, productName, isActive);
            } else {
              alert(data.message || 'An error occurred. Please try again.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating your wishlist.');
          });
      });
    });

    function updateWishlist(productId, productName, isAdding) {
      let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');

      if (isAdding) {
        wishlist.push({
          id: productId,
          name: productName
        });
        showToast(`${productName} added to wishlist!`);
      } else {
        wishlist = wishlist.filter(item => item.id !== productId);
        showToast(`${productName} removed from wishlist!`);
      }

      localStorage.setItem('wishlist', JSON.stringify(wishlist));
      updateWishlistCount();
    }

    function updateWishlistCount() {
      let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
      const wishlistCount = wishlist.length;
      const wishlistBadge = document.querySelector('.wishlist-badge'); // Ensure this element exists in your header

      if (wishlistBadge) {
        wishlistBadge.textContent = wishlistCount;
        wishlistBadge.style.display = wishlistCount > 0 ? 'flex' : 'none';
      }
    }

    // Call this function on page load to reflect the correct count
    document.addEventListener('DOMContentLoaded', updateWishlistCount);

    // Initial wishlist loading (example)
    window.onload = function() {
      let wishlist = localStorage.getItem('wishlist');
      if (wishlist) {
        wishlist = JSON.parse(wishlist);
        console.log("Wishlist items:", wishlist);
        // Here you could re-apply the 'active' class to the correct heart icons
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
        toast.addEventListener('transitionend', () => toast.remove(), {
          once: true
        });
      }, 3000);
    }
  </script>
</body>

</html>