<?php
include 'auth_check.php';
include 'db_connect.php';

// Fetch current admin details for header
$admin_id = $_SESSION['admin_id'] ?? 0;
$current_admin = null;
if ($admin_id > 0) {
    $stmt_admin = $conn->prepare("SELECT full_name, profile_photo FROM users WHERE id = ?");
    $stmt_admin->bind_param("i", $admin_id);
    $stmt_admin->execute();
    $current_admin = $stmt_admin->get_result()->fetch_assoc();
    $stmt_admin->close();
}

// Helper function for avatars (from dashboard.php)
function get_avatar($photo, $name) {
    if ($photo && file_exists('../' . $photo)) {
        return '../' . htmlspecialchars($photo);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&rounded=true';
}

$error_message = '';

// --- Fetch categories from the database ---
$categories = [];
$result = $conn->query("SELECT name FROM categories ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
} else {
    $error_message = "Could not fetch categories. Please ensure the 'categories' table exists.";
}

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['categories']);
    $category = trim($_POST['category']);
    $sub_category = trim($_POST['sub_category']);
    $description = trim($_POST['description']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    $stock = (int)$_POST['stock']; // Assuming this is a base/total stock value
    $unit = trim($_POST['unit']);

    // Basic validation
    if (empty($name) || $price <= 0 || empty($category) || empty($unit)) {
        $error_message = "Product name, price, category, and unit are required.";
    } else {
        $image_path = null; // Default to null if no image is uploaded

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../Image/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (in_array($file_type, $allowed_types)) {
                // Create a unique filename to avoid conflicts
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('prod_', true) . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = $new_filename; // Use the new image path
                } else {
                    $error_message = "Failed to move uploaded file.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error_message = "There was an error uploading the file. Please try again.";
        }

        // If no errors, insert into the database
        if (empty($error_message)) {
            // fix Argument #7 cannot be passed by reference
            $image_path_value = $image_path === null ? null : $image_path;

            $stmt = $conn->prepare("INSERT INTO products (name, price, category, sub_category, description, image, stock, stock_quantity, unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        

            // Bind parameters using the temporary variable
            $stmt->bind_param("sdssssiis", $name, $price, $category, $sub_category, $description, $image_path_value, $stock, $stock_quantity, $unit);

            
            
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Product '" . htmlspecialchars($name) . "' added successfully!";
                header("Location: products.php");
                exit();
            } else {
                $error_message = "Database insert failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - DUNZO Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
             --bg-color: #f4f7fe; --sidebar-bg: #ffffff; --card-bg: #ffffff;
            --bg-color: #f4f7fe;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #1a202c;
            --text-muted: #718096;
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --border-color: #e2e8f0;
            --danger: #e74c3c;
            --success: #1abc9c;
            --warning: #f1c40f;
            --info: #3498db;
            --body-font: 'Poppins', sans-serif;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-color);
            display: flex; 
        }
        a { text-decoration: none; color: inherit; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 20px; }
        .panel { background-color: var(--card-bg); padding: 25px; border-radius: var(--border-radius); box-shadow: var(--shadow); }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
        .panel-header h3 { font-size: 18px; font-weight: 600; }
        .panel-header a { color: var(--primary); font-weight: 500; font-size: 14px; }
        .form-label { font-weight: 500; color: var(--text-color); }
        .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border-color); padding: 10px 15px; background-color: #fff; width: 100%; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); outline: none; }
        .form-text { color: var(--text-muted); }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #3a56d3; border-color: #3a56d3; }
        .btn-secondary {
            color: var(--gray-600);
            background-color: var(--gray-100);
            border-color: var(--gray-300);
        }

        .btn-secondary:hover {
            background-color: var(--gray-200); border-color: var(--gray-300); }
        .image-upload-box { border: 2px dashed var(--border-color); border-radius: 0.5rem; padding: 2rem; background-color: #f9fafb; cursor: pointer; transition: border-color 0.2s ease, background-color 0.2s ease; position: relative; }
        .image-upload-box:hover, .image-upload-box.dragover { border-color: var(--primary); background-color: var(--primary-light); }
        #image-preview { max-height: 200px; width: auto; object-fit: contain; }
        #image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .alert-danger { background-color: rgba(231, 76, 60, 0.1); color: var(--danger); border: 1px solid var(--danger); padding: 1rem; border-radius: 8px; }
        /* Styles for header from dashboard.php */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background-color: var(--card-bg); padding: 15px 25px; border-radius: var(--border-radius); box-shadow: var(--shadow); }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .user-profile-dropdown { position: relative; }
        .user-profile { display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 5px; border-radius: 8px; transition: background-color 0.2s ease; }
        .user-profile:hover { background-color: var(--bg-color); }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile .user-details h4 { font-size: 15px; font-weight: 600; margin: 0; line-height: 1.2; }
        .user-profile .user-details p { font-size: 13px; color: var(--text-muted); margin: 0; }
        .user-profile .fa-chevron-down { font-size: 12px; color: var(--text-muted); transition: transform 0.2s ease; }
        .user-profile-dropdown.open .user-profile .fa-chevron-down { transform: rotate(180deg); }
        .dropdown-menu { position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card-bg); border-radius: var(--border-radius); box-shadow: 0 8px 25px rgba(0,0,0,0.1); width: 220px; z-index: 1000; border: 1px solid var(--border-color); overflow: hidden; display: none; animation: fadeInDropdown 0.2s ease-out; }
        .user-profile-dropdown.open .dropdown-menu { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; font-size: 14px; color: var(--text-color); transition: background-color 0.2s ease; }
        .dropdown-item i { width: 16px; text-align: center; color: var(--text-muted); }
        .dropdown-item:hover { background-color: var(--primary-light); }
        .dropdown-item.logout { color: var(--danger); }
        .dropdown-item.logout:hover { background-color: rgba(229, 62, 62, 0.1); }
        .dropdown-item.logout i { color: var(--danger); }
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            padding: 20px;
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
        }
          .sidebar {
            width: 260px; background-color: var(--sidebar-bg); height: 100vh;
            position: fixed; left: 0; top: 0; padding: 20px;
            display: flex; flex-direction: column; border-right: 1px solid var(--border-color);
        }
        .sidebar .logo { font-size: 28px; font-weight: 700; padding: 10px; margin-bottom: 30px; }
        .sidebar .logo .yellow { color: #febd69; }
        .sidebar .logo .green { color: #00a651; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 15px;
            color: var(--text-muted); font-weight: 500; border-radius: 8px;
            margin-bottom: 5px; transition: all 0.2s ease;
        }
        .sidebar-menu li a i { font-size: 18px; width: 20px; margin-right: 15px; text-align: center; }
        .sidebar-menu li a.active, .sidebar-menu li a:hover { background-color: var(--primary-light); color: var(--primary); }
        @keyframes fadeInDropdown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>
<div class="main-content">
    <?php include 'header.php'; ?>

        <div class="panel">
            <div class="panel-header">
                <h3>Product Information</h3>
                <a href="products.php" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
            <div class="panel-body" style="padding-top: 20px;">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="" disabled <?= !isset($_POST['category']) ? 'selected' : '' ?>>Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sub_category" class="form-label">Sub-Category</label>
                            <input type="text" class="form-control" id="sub_category" name="sub_category" value="<?= htmlspecialchars($_POST['sub_category'] ?? '') ?>">
                            <div class="form-text">Optional. E.g., 'shampoo', 'juice', 'chips'.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3"> 
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '0') ?>" required>
                                <div class="form-text">Current inventory level.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Total Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '100') ?>" required>
                                <div class="form-text">A base/total stock value.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?= htmlspecialchars($_POST['unit'] ?? '') ?>" placeholder="e.g. 500g, 1L" required>
                                <div class="form-text">E.g., '500g', '1L', 'Pack of 4'.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="6"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <div class="image-upload-box text-center" id="image-upload-box">
                                <img id="image-preview" src="#" alt="Image Preview" class="img-fluid rounded mb-2 d-none">
                                <div id="upload-prompt">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                    <p class="mb-0 mt-2">Click to upload or drag & drop</p>
                                    <p class="form-text mb-0">PNG, JPG, GIF, WEBP</p>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <a href="products.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Save Product</button>
                </div>
            </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageUploadBox = document.getElementById('image-upload-box');
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('image-preview');
        const uploadPrompt = document.getElementById('upload-prompt');

        imageUploadBox.addEventListener('click', (e) => {
            if (e.target.id !== 'image') {
                imageInput.click();
            }
        });

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                    uploadPrompt.classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Optional: Add drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            imageUploadBox.addEventListener(eventName, e => e.preventDefault() && e.stopPropagation(), false);
        });
        ['dragenter', 'dragover'].forEach(eventName => {
            imageUploadBox.addEventListener(eventName, () => imageUploadBox.classList.add('dragover'), false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            imageUploadBox.addEventListener(eventName, () => imageUploadBox.classList.remove('dragover'), false);
        });

        imageUploadBox.addEventListener('drop', (e) => {
            imageInput.files = e.dataTransfer.files;
            imageInput.dispatchEvent(new Event('change'));
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.user-profile-dropdown');
            if (dropdown && dropdown.classList.contains('open') && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    });
</script>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>