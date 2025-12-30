<?php
session_start();
include 'config.php';

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DUNZO/Image/no-image.png';
    if (empty(trim((string)$db_path))) {
        return $default_image;
    }
    // 1. Clean up known incorrect prefixes
    $path = preg_replace('#^(\.\./|/DUNZO/|Product/)#', '', (string)$db_path);
    $path = ltrim($path, '/');

    // 2. Based on your file structure `C:\xampp\htdocs\DUNZO\Image`, all images
    // should be inside the 'Image' directory. This code ensures that.
    if (strpos($path, 'Image/') !== 0 && strpos($path, 'PICTURE/') !== 0) {
        $path = 'Image/' . $path;
    }
    
    return '/DUNZO/' . htmlspecialchars($path);
}

// Get location data from session
$delivery_time = $_SESSION['delivery_time'] ?? "24 minutes";
$full_location = $_SESSION['location'] ?? "Rajkot South Taluka, Rajkot, Gujarat, India";
$short_location = $_SESSION['short_location'] ?? "Rajkot South";

// Clean up location display
if (isset($_SESSION['location']) && !empty($_SESSION['location'])) {
    // Use the short location if available, otherwise create one
    if (empty($short_location) || $short_location === "Rajkot South") {
        $clean_location = preg_replace('/(Gujarat|India|\b\d{5,6}\b)/i', '', $full_location);
        $clean_location = trim(preg_replace('/\s*,\s*/', ', ', $clean_location));
        $clean_location = trim(preg_replace('/,+/', ',', $clean_location));

        $parts = explode(',', $clean_location);
        $short_location = trim($parts[0]);

        // Update session with short location
        $_SESSION['short_location'] = $short_location;
    }
} else {
    // Default values if no location is set
    $short_location = "Choose Location";
    $delivery_time = "24 minutes";
}

// Check if location was just updated
$location_updated = isset($_GET['location']) && $_GET['location'] === 'updated';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DUNZO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Use new CSS file -->
    <link rel="stylesheet" href="index.css">
   </head>

<body>
    <?php include 'includes/header.php'; ?>

    <?php if ($location_updated): ?>
    <!-- Location Update Notification -->
    <div class="location-toast" id="locationToast">
      <span class="icon">📍</span>
      <div class="content">
        <p class="title">Location Updated</p>
        <p class="subtitle">Delivering to <strong><?= htmlspecialchars($short_location) ?></strong></p>
      </div>
      <button class="close-btn" onclick="closeToast()">✖</button>
    </div>
    <?php endif; ?>

    <!-- Perfect Hero Banner -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Delivering in <span class="highlight">10 Minutes</span>!</h1>
                <p>Your favorite items, delivered lightning fast with love 💚</p>

                <!-- Stats Section -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">10M+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Cities Served</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Support</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="hero-buttons">
                    <a href="product.php" class="btn btn-primary">
                        <i class="fas fa-shopping-basket"></i>
                        Start Shopping
                    </a>
                    <a href="order.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i>
                        My Orders
                    </a>
                 
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="btn btn-tertiary">
                            <i class="fas fa-user"></i>
                            My Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>


        </div>
    </section>

    <!-- Categories Grid -->
    <section class="categories-banner">
        <h2>Shop by Categories</h2>
        <div class="category-cards">
            <?php
            $categories = [
                ["title" => "Fresh Grocery Delivered Fast", "desc" => "Fruits, vegetables, staples & more", "link" => "product/Grocery.php", "img" => "Image/PICTURE/Grocery.jpg"],
                ["title" => "Pharmacy at your doorstep!", "desc" => "Cough syrups, pain relief sprays & more", "link" => "product/Pharmacy.php", "img" => "Image/PICTURE/Pharmacy.jpg"],
                ["title" => "Snacks for every craving", "desc" => "Chips, chocolates, biscuits & more", "link" => "product/SnackZone.php", "img" => "Image/PICTURE/Snacks.jpg"],
                ["title" => "Cool down with Beverages", "desc" => "Juices, soft drinks, water & more", "link" => "product/CoolSips.php", "img" => "Image/PICTURE/Drinks.jpg"],
                ["title" => "Electronics Delivered Fast", "desc" => "Chargers, earphones, cables & more", "link" => "product/Electronics.php", "img" => "Image/PICTURE/Electronics.jpeg"],
                ["title" => "Pet Care supplies in minutes", "desc" => "Food, treats, toys & more", "link" => "product/Pet.php", "img" => "Image/PICTURE/Pet Care.jpg"],
                ["title" => "Fresh Bakery on Demand", "desc" => "Bread, cakes, cookies & more", "link" => "product/Bakery.php", "img" => "Image/PICTURE/Bakery.jpg"],
                ["title" => "Personal Care Essentials", "desc" => "Shampoo, soap, toothpaste & more", "link" => "product/Cosmetic.php", "img" => "Image/PICTURE/Personal Care.jpg"],
            ];
            foreach ($categories as $cat) {
                echo "<div class='category-card'>
                        <div class='text'>
                            <h3>{$cat['title']}</h3>
                            <p>{$cat['desc']}</p>
                            <a href='{$cat['link']}' class='order-now-btn'>Order Now</a>
                        </div>
                        <img src='{$cat['img']}' alt='Category Image'>
                    </div>";
            }
            ?>
        </div>
    </section>

    <!-- Featured Products Carousel -->
    <section class="featured-section">
        <h2>Freshness at Your Fingertips</h2>
        <p>Farm-picked fruits, crisp veggies, pantry staples & more delivered lightning fast!</p>
        <div class="carousel-wrapper">
            <div class="carousel-track" id="carouselTrack">
                <?php
                // Fetch featured products dynamically from the database
                $featured_products = [];
                $sql = "SELECT p.id, p.name, p.price, p.image, p.category, c.link_url 
                        FROM products p 
                        LEFT JOIN categories c ON p.category = c.name 
                        ORDER BY RAND() 
                        LIMIT 10";
                
                if ($result = $conn->query($sql)) {
                    $featured_products = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                }

                // Duplicate the items for a seamless loop effect in the carousel
                if (!empty($featured_products)) {
                    $featured_products = array_merge($featured_products, $featured_products);
                }

                if (!empty($featured_products)) {
                    foreach ($featured_products as $item) {
                        $image_path = get_image_path($item['image']);
                        $product_link = !empty($item['link_url']) ? $item['link_url'] : 'product.php';
                        $description = htmlspecialchars($item['category']) . ' · ₹' . number_format($item['price'], 2);
                        
                        echo "<div class='product-card'>
                                <img src='{$image_path}' alt='" . htmlspecialchars($item['name']) . "'>
                                <h4>" . htmlspecialchars($item['name']) . "</h4>
                                <p>{$description}</p>
                                <a href='{$product_link}'>Add to Cart</a>
                            </div>";
                    }
                } else {
                    echo "<p>No featured products available at the moment.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    

    <!-- Stores Near You -->
    <section class="nearby-stores">
        <h2>🛒 Stores Near You</h2>
        <div class="store-list">
            <?php
            $stores = [
    ["img" => "Image/PICTURE/71.jpg", "name" => "Fresh Mart", "type" => "Groceries & Essentials", "status" => "✔ Open", "distance" => "1.2 km", "link" => "product/Grocery.php"],
    ["img" => "Image/PICTURE/72.jpg", "name" => "Daily Needs", "type" => "Pharmacy & Health", "status" => "🟢 Open 24 Hours", "distance" => "0.8 km", "link" => "product/Pharmacy.php"],
    ["img" => "Image/PICTURE/73.jpg", "name" => "Snacky Station", "type" => "Snacks, Beverages", "status" => "✔ Open", "distance" => "2.0 km", "link" => "product/SnackZone.php"],
    ["img" => "Image/PICTURE/74.jpeg", "name" => "Tech Express", "type" => "Mobiles & Gadgets", "status" => "✔ Open", "distance" => "3.5 km", "link" => "product/Electronics.php"],
    ["img" => "Image/PICTURE/75.jpeg", "name" => "Green Basket", "type" => "Organic & Fresh", "status" => "❌ Closed", "distance" => "2.8 km", "link" => "store.php?id=5", "closed" => true],
];

        
            foreach ($stores as $store) {
                echo "<div class='store-card'>
                        <img src='{$store['img']}' alt='{$store['name']}'>
                        <div class='store-info'>
                            <h3>{$store['name']}</h3>
                            <p>{$store['type']}</p>
                            <span class='status " . (isset($store['closed']) ? "closed" : "open") . "'>{$store['status']}</span>
                            <span class='distance'>{$store['distance']} away</span>";
                if (isset($store['closed'])) {
                    echo "<a href='{$store['link']}' class='store-btn disabled' disabled>Closed</a>";
                } else {
                    echo "<a href='{$store['link']}' class='store-btn'>View Store</a>";
                }
                echo "</div></div>";
            }
            ?>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>


    <script src="searchPlaceholder.js"></script>
    <script>
        // Search placeholder animation
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            const placeholders = [
                'Search for groceries, medicines, electronics...',
                'Looking for fresh vegetables?',
                'Need medicines? Search here...',
                'Find electronics and gadgets...',
                'Search for snacks and beverages...'
            ];

            let currentIndex = 0;

            function updatePlaceholder() {
                searchInput.placeholder = placeholders[currentIndex];
                currentIndex = (currentIndex + 1) % placeholders.length;
            }

            // Update placeholder every 3 seconds
            setInterval(updatePlaceholder, 3000);
        }

        // Live Location Tracker Functions
        function getCurrentLocation() {
            const statusDiv = document.getElementById('locationStatus');
            const mapDiv = document.getElementById('locationMap');

            if (!navigator.geolocation) {
                statusDiv.innerHTML = '<p style="color: red;">Geolocation is not supported by this browser.</p>';
                return;
            }

            statusDiv.innerHTML = '<p style="color: #0ea86f;"><i class="fas fa-spinner fa-spin"></i> Getting your location...</p>';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    // Update status
                    statusDiv.innerHTML = '<p style="color: #0ea86f;"><i class="fas fa-check-circle"></i> Location found!</p>';

                    // Show coordinates
                    statusDiv.innerHTML += `<p style="font-size: 0.9rem; color: #666;">Lat: ${latitude.toFixed(6)}, Long: ${longitude.toFixed(6)}</p>`;

                    // Create simple map display
                    mapDiv.innerHTML = `
                        <div style="background: #f0f0f0; padding: 15px; border-radius: 8px; margin-top: 10px; text-align: center;">
                            <i class="fas fa-map-marker-alt" style="color: #ff4757; font-size: 2rem;"></i>
                            <p style="margin: 10px 0; font-weight: bold;">Your Current Location</p>
                            <button onclick="confirmLocation(${latitude}, ${longitude})" style="background: #0ea86f; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer;">
                                Use This Location
                            </button>
                        </div>
                    `;

                    // Get address from coordinates (reverse geocoding)
                    getAddressFromCoords(latitude, longitude);
                },
                function(error) {
                    let errorMessage = '';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Location access denied. Please allow location access.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Location information unavailable.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "Location request timed out.";
                            break;
                        default:
                            errorMessage = "An unknown error occurred.";
                            break;
                    }
                    statusDiv.innerHTML = `<p style="color: red;"><i class="fas fa-exclamation-triangle"></i> ${errorMessage}</p>`;
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        }

        function getAddressFromCoords(lat, lng) {
            // Using OpenStreetMap Nominatim API for reverse geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        const statusDiv = document.getElementById('locationStatus');
                        statusDiv.innerHTML += `<p style="font-size: 0.9rem; color: #333; margin-top: 5px;"><strong>Address:</strong> ${data.display_name}</p>`;
                    }
                })
                .catch(error => {
                    console.log('Error getting address:', error);
                });
        }

        function confirmLocation(lat, lng) {
            // Update the location selector with current location
            const locationSelector = document.querySelector('.location-selector span:last-child');
            if (locationSelector) {
                locationSelector.textContent = '📍 Current Location ▼';
            }

            // Close the popup
            closeLocationPopup();

            // Show success message
            showNotification('Location updated successfully!', 'success');

            // Store location in localStorage
            localStorage.setItem('userLocation', JSON.stringify({
                latitude: lat,
                longitude: lng,
                timestamp: new Date().getTime()
            }));
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#0ea86f' : '#ff4757'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-weight: 500;
                max-width: 300px;
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Quick location selection function
        function selectQuickLocation(shortName, fullAddress) {
            // Create form and submit to save_location.php
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'save_location.php';

            // Add form fields
            const fields = {
                'latitude': '22.3039', // Default Rajkot coordinates
                'longitude': '70.8022',
                'full_address': fullAddress,
                'quick_location': shortName
            };

            Object.keys(fields).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        // Close notification function
        function closeNotification() {
            const notification = document.getElementById('locationNotification');
            if (notification) {
                notification.style.animation = 'slideOutRight 0.5s ease-out';
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Make sure location popup is hidden by default
            const popup = document.getElementById("locationPopup");
            if (popup) {
                popup.style.display = "none";
            }

            // Check if user has saved location
            const savedLocation = localStorage.getItem('userLocation');
            if (savedLocation) {
                const location = JSON.parse(savedLocation);
                const locationSelector = document.querySelector('.location-selector span:last-child');
                if (locationSelector) {
                    locationSelector.textContent = '📍 Current Location ▼';
                }
            }

            // Auto-hide location notification after 5 seconds
            const notification = document.getElementById('locationNotification');
            if (notification) {
                setTimeout(() => {
                    closeNotification();
                }, 5000);
            }
        });

        // Function to close the location toast (globally accessible for onclick)
        function closeToast() {
            const toast = document.getElementById('locationToast');
            if (toast) {
                toast.classList.remove('show');
                // Optional: remove from DOM after animation to clean up
                setTimeout(() => {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 500); // Match CSS transition duration
            }
        }

    </script>

</body>

</html>