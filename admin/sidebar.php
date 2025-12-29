<div class="sidebar">
    <div class="logo">
        <span class="yellow">dun</span><span class="green">zo</span>
    </div>
    <ul class="sidebar-menu">
        <?php $activePage = basename($_SERVER['PHP_SELF']); ?>
        <li><a href="dashboard.php" class="<?= ($activePage == 'dashboard.php') ? 'active' : '' ?>"><i class="fas fa-tachometer-alt fa-fw"></i> <span>Dashboard</span></a></li>
        <li><a href="orders.php" class="<?= ($activePage == 'orders.php') ? 'active' : '' ?>"><i class="fas fa-shopping-bag fa-fw"></i> <span>Orders</span></a></li>
        <li><a href="products.php" class="<?= in_array($activePage, ['products.php', 'add_product.php', 'edit_product.php', 'view_product.php']) ? 'active' : '' ?>"><i class="fas fa-box-open fa-fw"></i> <span>Products</span></a></li>
        <li><a href="coupons.php" class="<?= ($activePage == 'coupons.php') ? 'active' : '' ?>"><i class="fas fa-tags fa-fw"></i> <span>Coupons</span></a></li>
        <li><a href="User.php" class="<?= in_array($activePage, ['User.php', 'view_user.php', 'add_user.php', 'edit_user.php']) ? 'active' : '' ?>"><i class="fas fa-users fa-fw"></i> <span>Customers</span></a></li>
        <li><a href="analytics.php" class="<?= ($activePage == 'analytics.php') ? 'active' : '' ?>"><i class="fas fa-chart-bar fa-fw"></i> <span>Analytics</span></a></li>
        <li><a href="#"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
    </ul>
    <ul class="sidebar-menu">
        <li><a href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> <span>Logout</span></a></li>
    </ul>
</div>