<?php
session_start();
include 'config.php';

// Helper function to generate a clean, root-relative image path
function get_image_path($db_path) {
    $default_image = '/DunzoQuick/Image/no-image.png';
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
    
    return '/DunzoQuick/' . htmlspecialchars($path);
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
    <style>
        /* Modern Stores Section Styles */
        .nearby-stores {
            padding: 40px 5%;
            background: #f7faff;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #171e30;
            margin: 0;
        }
        .sort-container select {
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            background: white;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            outline: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .store-card-new {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
        }
        .store-card-new:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .store-image-container {
            position: relative;
            height: 160px;
            overflow: hidden;
        }
        .store-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .store-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            z-index: 2;
        }
        .badge-open { background: #e6fffa; color: #00b37a; }
        .badge-closed { background: #fff5f5; color: #e53e3e; }
        .store-details {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .store-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .store-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #718096;
            margin-bottom: 12px;
        }
        .store-rating {
            background: #24963e;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .store-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #4a5568;
        }
        .info-item { display: flex; align-items: center; gap: 6px; }
        .info-item i { color: #00d290; width: 16px; text-align: center; }
        .view-btn {
            margin-top: auto;
            display: block;
            width: 100%;
            padding: 10px;
            background: #f0fff4;
            color: #00b37a;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #c6f6d5;
        }
        .view-btn:hover { background: #00b37a; color: white; }
        .view-btn.disabled {
            background: #edf2f7;
            color: #a0aec0;
            border-color: #e2e8f0;
            pointer-events: none;
        }
        @media (max-width: 768px) {
            .store-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        }
        @media (max-width: 480px) {
            .store-grid { grid-template-columns: 1fr; }
        }
    </style>
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
        <div class="section-header">
            <h2>🛒 Stores Near You</h2>
            <div class="sort-container">
                <select id="storeSort" onchange="sortStores()">
                    <option value="default">Sort By</option>
                    <option value="rating">Rating: High to Low</option>
                    <option value="time">Delivery Time: Fastest</option>
                    <option value="distance">Distance: Nearest</option>
                </select>
            </div>
        </div>
        
        <div class="store-grid" id="storeGrid">
            <?php
            $stores = [
                ["id" => 1, "name" => "Fresh Mart", "image" => "Image/PICTURE/71.jpg", "location" => "Civil Lines", "distance" => 1.2, "time" => 15, "rating" => 4.8, "reviews" => "1.2k", "fee" => 0, "status" => "Open", "link" => "product/Grocery.php", "tags" => "Groceries"],
                ["id" => 2, "name" => "Daily Needs Pharmacy", "image" => "Image/PICTURE/72.jpg", "location" => "Market Road", "distance" => 0.8, "time" => 10, "rating" => 4.5, "reviews" => "850", "fee" => 20, "status" => "Open", "link" => "product/Pharmacy.php", "tags" => "Pharmacy"],
                ["id" => 3, "name" => "Snacky Station", "image" => "Image/PICTURE/73.jpg", "location" => "University Area", "distance" => 2.5, "time" => 25, "rating" => 4.2, "reviews" => "500+", "fee" => 0, "status" => "Open", "link" => "product/SnackZone.php", "tags" => "Snacks"],
                ["id" => 4, "name" => "Tech Express", "image" => "Image/PICTURE/74.jpeg", "location" => "Tech Park", "distance" => 3.5, "time" => 30, "rating" => 4.9, "reviews" => "2k+", "fee" => 0, "status" => "Open", "link" => "product/Electronics.php", "tags" => "Electronics"],
                ["id" => 5, "name" => "Green Basket Organic", "image" => "Image/PICTURE/75.jpeg", "location" => "Green Valley", "distance" => 2.8, "time" => 35, "rating" => 4.6, "reviews" => "300", "fee" => 25, "status" => "Closed", "link" => "#", "tags" => "Organic"],
                ["id" => 6, "name" => "Pet Paradise", "image" => "Image/PICTURE/Pet Care.jpg", "location" => "West End", "distance" => 4.0, "time" => 40, "rating" => 4.7, "reviews" => "150", "fee" => 30, "status" => "Open", "link" => "product/Pet.php", "tags" => "Pet Care"],
                ["id" => 7, "name" => "Cool Sips Beverages", "image" => "Image/PICTURE/Drinks.jpg", "location" => "North Avenue", "distance" => 1.5, "time" => 20, "rating" => 4.3, "reviews" => "400", "fee" => 15, "status" => "Open", "link" => "product/CoolSips.php", "tags" => "Beverages"],
                ["id" => 8, "name" => "Oven Fresh Bakery", "image" => "Image/PICTURE/Bakery.jpg", "location" => "Central Plaza", "distance" => 0.5, "time" => 12, "rating" => 4.9, "reviews" => "1.5k", "fee" => 0, "status" => "Open", "link" => "product/Bakery.php", "tags" => "Bakery"]
            ];

            foreach ($stores as $store) {
                $isOpen = $store['status'] === 'Open';
                $badgeClass = $isOpen ? 'badge-open' : 'badge-closed';
                $btnClass = $isOpen ? '' : 'disabled';
                $btnText = $isOpen ? 'View Store' : 'Closed Now';
                $feeText = $store['fee'] == 0 ? 'Free Delivery' : '₹' . $store['fee'] . ' Delivery';
                
                echo "
                <div class='store-card-new' data-rating='{$store['rating']}' data-time='{$store['time']}' data-distance='{$store['distance']}'>
                    <div class='store-image-container'>
                        <span class='store-badge {$badgeClass}'>{$store['status']}</span>
                        <img src='{$store['image']}' alt='{$store['name']}' loading='lazy'>
                    </div>
                    <div class='store-details'>
                        <div class='store-name'>{$store['name']}</div>
                        <div class='store-meta'>
                            <span>{$store['tags']}</span>
                            <span>•</span>
                            <span>{$store['location']}</span>
                        </div>
                        <div class='store-info-grid'>
                            <div class='info-item'>
                                <span class='store-rating'>{$store['rating']} <i class='fas fa-star' style='color:white; width:auto;'></i></span>
                                <span style='font-size:11px; color:#a0aec0;'>({$store['reviews']})</span>
                            </div>
                            <div class='info-item'>
                                <i class='fas fa-clock'></i> {$store['time']} mins
                            </div>
                            <div class='info-item'>
                                <i class='fas fa-map-marker-alt'></i> {$store['distance']} km
                            </div>
                            <div class='info-item'>
                                <i class='fas fa-motorcycle'></i> {$feeText}
                            </div>
                        </div>
                        <a href='{$store['link']}' class='view-btn {$btnClass}'>{$btnText}</a>
                    </div>
                </div>";
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
        
        // Store Sorting Function
        function sortStores() {
            const grid = document.getElementById('storeGrid');
            const sortValue = document.getElementById('storeSort').value;
            const cards = Array.from(grid.getElementsByClassName('store-card-new'));

            cards.sort((a, b) => {
                switch(sortValue) {
                    case 'rating':
                        return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
                    case 'time':
                        return parseInt(a.dataset.time) - parseInt(b.dataset.time);
                    case 'distance':
                        return parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance);
                    default:
                        return 0;
                }
            });
            cards.forEach(card => grid.appendChild(card));
        }

    </script>

</body>

</html>