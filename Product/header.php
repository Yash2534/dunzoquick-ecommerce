<style>
.header {
      background: #fff;
      box-shadow: 0 2px 8px #eee;
      padding: 18px 0;
    }

    .header-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 30px;
      padding: 0 32px;
    }

    .logo {
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .logo .yellow {
      color: #ffd600;
    }

    .logo .green {
      color: #00a651;
    }

    .search-form {
      display: flex;
      flex-grow: 1; /* Allow it to take up available space */
      max-width: 500px;
      border: 1px solid #e0e0e0;
      border-radius: 50px;
      overflow: hidden;
      background: #f7f8fa;
    }

    .search-form input {
      border: none;
      outline: none;
      padding: 10px 20px;
      font-size: 0.95rem;
      width: 100%;
      background: transparent;
      font-family: 'Poppins', sans-serif;
    }

    .search-form button {
      background: transparent;
      border: none;
      color: #555;
      padding: 0 15px;
      cursor: pointer;
      font-size: 1.1rem;
      transition: color 0.2s;
    }
    .search-form button:hover {
      color: #00a651;
    }

    .nav-links {
      display: flex;
      gap: 28px;
    }

    .nav-link {
      color: #222;
      text-decoration: none;
      font-weight: 500;
      font-size: 1rem;
      transition: color 0.2s;
    }

    .nav-link:hover {
      color: #00a651;
    }

    .nav-btn {
      text-decoration: none;
      transition: color 0.2s;
    }

    .cart-btn {
      background: #00a651;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 18px;
      font-weight: 500;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: background 0.2s;
      position: relative; /* Needed for badge positioning */
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: #ff5252; /* A bright color for visibility */
      color: white;
      border-radius: 50%;
      width: 22px;
      height: 22px;
      font-size: 12px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cart-btn:hover {
      background: #008f47;
    }

    /* Dropdown for Categories */
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-btn {
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      background: none;
      border: none;
      padding: 0;
      /* Inherit font styles from nav-link */
      font-family: inherit; 
    }

    .dropdown-btn i {
      font-size: 0.7em;
      transition: transform 0.2s ease;
      margin-top: 2px;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      min-width: 180px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1);
      z-index: 10;
      border-radius: 8px;
      padding: 8px 0;
      margin-top: 10px;
      border: 1px solid #eee;
      text-align: left;
    }

    .dropdown-content a {
      color: #333;
      padding: 10px 16px;
      text-decoration: none;
      display: block;
      font-size: 0.95rem;
      font-weight: 400; /* Reset from nav-link bold */
    }

    .dropdown-content a:hover {
      background-color: #f7f8fa;
      color: #00a651;
    }

    .dropdown-content.show {
      display: block;
    }
    .dropdown-btn.active i {
      transform: rotate(180deg);
    }

    .search-results-heading {
      max-width: 1200px;
      margin: 0 auto 20px;
      padding: 0 32px;
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
    }
       @media (max-width: 700px) {
      .header-container {
        flex-direction: column;
        gap: 12px;
        padding: 0 10px;
      }

      .product-grid {
        gap: 16px;
        padding: 0 16px;
      }
    }
    </style>
  
  <!-- Header -->
  <header class="header">
    <div class="header-container">
      <div class="logo">
        <span class="yellow">dun</span><span class="green">zo</span>
      </div>
      <!-- Search Form -->
      <form action="/DUNZO/product.php" method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search_query ?? '') ?>" aria-label="Search products">
        <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
      </form>
      <nav class="nav-links">
        <a href="/DUNZO/index.php" class="nav-link">Home</a>
        <div class="dropdown">
          <button class="nav-link dropdown-btn">
            Categories <i class="fas fa-chevron-down"></i>
          </button>
          <div class="dropdown-content">
            <?php
              // Fetch categories dynamically from the database
              $category_result = $conn->query("SELECT name, link_url FROM categories ORDER BY sort_order, name ASC");
              if ($category_result && $category_result->num_rows > 0) {
                while($category = $category_result->fetch_assoc()) {
                  echo '<a href="' . htmlspecialchars($category['link_url']) . '">' . htmlspecialchars($category['name']) . '</a>';
                }
              } else {
                echo '<a href="#" style="color: #999; cursor: default;">No categories</a>';
              }
            ?>
          </div>
        </div>
      </nav>
      <!-- Wishlist Button -->
      <a href="/DUNZO/wishlist.php" class="nav-link nav-btn" title="My Wishlist">
        <i class="fas fa-heart"></i>
        <span>Wishlist</span>
      </a>

      <?php
        // Fetch initial cart count for the logged-in user
        $cart_count = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $count_result = $stmt->get_result();
            if ($count_result) {
                $cart_count = (int)($count_result->fetch_assoc()['total_items'] ?? 0);
            }
            $stmt->close();
        }
      ?>
      <a href="/DUNZO/cart.php" class="cart-btn">
        <i class="fas fa-shopping-cart"></i> Cart
        <span class="cart-badge" style="<?= $cart_count > 0 ? 'display: flex;' : 'display: none;' ?>"><?= $cart_count ?></span>
      </a>
    </div>
  </header>
  <script>
    /* --- Category Dropdown on Click --- */
    const dropdownBtn = document.querySelector('.dropdown-btn');

    if (dropdownBtn) {
      dropdownBtn.addEventListener('click', function (event) {
        // This stops the click from immediately being caught by the 'window' listener,
        // which would otherwise close the dropdown instantly.
        event.stopPropagation();
        
        const dropdownContent = this.nextElementSibling;
        const isCurrentlyOpen = dropdownContent.classList.contains('show');

        // Close all dropdowns first to handle cases where other menus might be open.
        closeAllDropdowns();

        // If the clicked dropdown was closed, open it. A second click will just close it.
        if (!isCurrentlyOpen) {
            dropdownContent.classList.add('show');
            this.classList.add('active');
        }
      });
    }

    // Add a listener to the whole window to close the dropdown when clicking anywhere else.
    window.addEventListener('click', () => closeAllDropdowns());

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-content.show').forEach(d => d.classList.remove('show'));
        document.querySelectorAll('.dropdown-btn.active').forEach(b => b.classList.remove('active'));
    }
</script>