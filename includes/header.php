<?php
// This header assumes session_start() has been called on the parent page.
// It also needs $conn (database connection from config.php) for the cart count.

// Initialize cart count to 0
$cart_item_count = 0;

// Check if the user is logged in and the database connection exists
if (isset($_SESSION['user_id']) && isset($conn)) {
  // Get the user's ID and make sure it's a number for security
  $user_id = (int)$_SESSION['user_id'];

  // Create a simple SQL query to get the total quantity of items for this user
  $sql = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $user_id";

  // Run the query and check if it was successful
  $result = $conn->query($sql);
  if ($result) {
    $row = $result->fetch_assoc();
    // Get the total count. If it's null (e.g., cart is empty), use 0.
    $cart_item_count = (int)($row['total_items'] ?? 0);
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<style>
  .header {
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 12px 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    gap: 24px;
    justify-content: space-between;
  }

  .logo {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.5px;
    flex-shrink: 0;
  }

  .logo .yellow {
    color: #febd69;
    text-shadow: none;
  }

  .logo .green {
    color: #00a651;
    text-shadow: none;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
    }
  }

  @keyframes bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
      transform: translateY(0);
    }

    40% {
      transform: translateY(-3px);
    }

    60% {
      transform: translateY(-2px);
    }
  }

  .search-container {
    flex-grow: 1;
    max-width: 600px;
    position: relative;
  }

  .search-box {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid #e0e0e0;
    border-radius: 50px;
    font-size: 14px;
    background: #f8f9fa;
    transition: all 0.2s ease;
    color: #333333;
  }

  .search-box:focus {
    outline: none;
    border-color: #00a651;
    background: #ffffff;
    box-shadow: 0 0 0 2px rgba(0, 166, 81, 0.1);
  }

  .search-box::placeholder {
    color: #999999;
    font-weight: 400;
  }

  .search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #666666;
    font-size: 16px;
    pointer-events: none;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
    /* This prevents the right side from shrinking */
  }

  .user-section {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .profile-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8f9fa;
    color: #333333;
    border: 1px solid #e0e0e0;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 14px;
  }

  .profile-btn:hover {
    background: #e9ecef;
    border-color: #dcdcdc;
    color: #333333;
    text-decoration: none;
    transform: translateY(-1px);
  }

  .profile-btn i {
    font-size: 16px;
    color: #00a651;
  }

  .login-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #00a651;
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
  }

  .login-btn:hover {
    background: #008f47;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .cart-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8f9fa;
    color: #333333;
    border: 1px solid #e0e0e0;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
    /* Required for badge positioning */
    font-weight: 500;
    font-size: 14px;
  }

  .cart-btn:hover {
    background: #e9ecef;
    border-color: #dcdcdc;
    transform: translateY(-1px);
  }

  .cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ff6584;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    border: 2px solid white;
  }

  /* Category Navigation Bar */
  .category-nav {
    background: #fff;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
    position: sticky;
    top: 64px;
    /* Adjust this value based on the actual height of the main header */
    z-index: 999;
    /* Lower than main header */
    overflow-x: auto;
    /* Allows horizontal scrolling on small screens */
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
  }

  .category-nav::-webkit-scrollbar {
    display: none;
    /* Chrome, Safari, and Opera */
  }

  .category-nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    gap: 25px;
    align-items: center;
  }

  .category-link {
    color: #495057;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    white-space: nowrap;
    /* Prevents link text from wrapping */
    padding-bottom: 8px;
    border-bottom: 2px solid transparent;
    transition: color 0.2s ease, border-color 0.2s ease;
  }

  .category-link:hover,
  .category-link.active {
    color: #00a651;
    border-bottom-color: #00a651;
  }
</style>
<!-- Blinkit Style Header -->
<header class="header">
  <div class="header-container">
    <!-- Logo -->
    <a href="index.php" class="logo logo-glow" style="text-decoration: none;">
      <span class="yellow">dun</span><span class="green">zo</span>
    </a>



    <!-- Search Container -->
    <div class="search-container">
      <i class="fas fa-search search-icon"></i>
      <input type="text" class="search-box" id="searchInput" placeholder="Search for milk, bread, chips…">
    </div>

    <!-- Header Actions -->
    <div class="header-actions">
      <!-- Cart Button -->
      <a href="cart.php" class="cart-btn" title="My Cart">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
        <?php if ($cart_item_count > 0): ?>
          <span class="cart-badge"><?php echo $cart_item_count; ?></span>
        <?php endif; ?>
      </a>



      <!-- Orders Button -->
      <a href="order.php" class="profile-btn" title="My Orders">
        <i class="fas fa-box"></i>
        <span>Orders</span>
      </a>
      <!-- Product  Button -->
      <a href="product.php" class="profile-btn" title="My Products">
        <i class="fas fa-box"></i>
        <span>product</span>
      </a>
      <!-- Wishlist Button -->
      <a href="wishlist.php" class="profile-btn" title="My Wishlist">
        <i class="fas fa-heart"></i>
        <span>Wishlist</span>
      </a>
      <!-- Profile/Login Button -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php" class="profile-btn" title="My Profile">
          <i class="fas fa-user-circle"></i>
          <span>Profile</span>
        </a>
      <?php else: ?>
        <a href="login.php" class="login-btn" title="Login/Signup">
          <i class="fas fa-user"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Category Navigation -->
<?php if (!empty($category_nav_items)): ?>
  <nav class="category-nav">
    <div class="category-nav-container">
      <?php foreach ($category_nav_items as $category): ?>
        <a href="/DunzoQuick/<?= htmlspecialchars(ltrim($category['link_url'], '/')) ?>" class="category-link">
          <?= htmlspecialchars($category['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>
<?php endif; ?>