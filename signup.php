<?php
session_start();

// If user is already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include the central configuration file
    require_once 'config.php';
    
    try {
        // Use constants from config.php for PDO connection
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get form data
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $mobile = trim($_POST['mobile']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $profile_photo = null;
        
        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $upload_dir = 'uploads/profile_photos/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file = $_FILES['profile_photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($file['type'], $allowed_types)) {
                $filename = time() . '_' . $file['name'];
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $profile_photo = $filepath;
                } else {
                    $error = "Failed to upload profile photo";
                }
            } else {
                $error = "Invalid file type. Please upload JPEG, PNG, or GIF";
            }
        }
        
        // Validation
        if (empty($full_name) || empty($email) || empty($mobile) || empty($password)) {
            $error = "All fields are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif ($error) {
            // Keep existing error from photo upload
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered. Please login instead.";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, mobile, password, profile_photo, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$full_name, $email, $mobile, $hashed_password, $profile_photo]);
                
                $success = "Account created successfully! Please login.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            min-height: 600px;
        }

        .left-side {
            flex: 1;
            background: linear-gradient(135deg, #28a745, #20c997);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .left-side::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .illustration {
            text-align: center;
            color: white;
            position: relative;
            z-index: 2;
        }

        .illustration i {
            font-size: 120px;
            margin-bottom: 20px;
            display: block;
        }

        .illustration h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .illustration p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .right-side {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            font-weight: 700;
        }

        .logo .yellow { color: #ffc107; }
        .logo .green { color: #28a745; }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .alert-success {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #c6f6d5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .password-toggle i:hover {
            color: #333;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-top: 2px;
        }

        .checkbox-group label {
            margin-bottom: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #666;
        }

        .checkbox-group a {
            color: #28a745;
            text-decoration: none;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #666;
            font-size: 14px;
        }

        .social-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .btn-social {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #333;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-social:hover {
            border-color: #28a745;
            transform: translateY(-1px);
        }

        .btn-social i {
            font-size: 16px;
        }

        .btn-google i { color: #db4437; }
        .btn-facebook i { color: #4267B2; }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .profile-photo-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-photo-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }
        .profile-photo-preview {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #f4f4f4;
            border: 2px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .profile-photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile-photo-upload input[type="file"] {
            display: none;
        }
        .profile-photo-upload .upload-text {
            color: #666;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .profile-photo-upload .upload-btn {
            color: #28a745;
            font-size: 14px;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            .signup-container {
                flex-direction: column;
                max-width: 400px;
            }

            .left-side {
                padding: 30px 20px;
            }

            .right-side {
                padding: 30px 20px;
            }

            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- Left Side - Illustration -->
        <div class="left-side">
            <div class="illustration">
                <i class="fas fa-shipping-fast"></i>
                <h2>Join DUNZO Today</h2>
                <p>Get your favorite items delivered in minutes with our lightning-fast service</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="right-side">
            <div class="logo">
                <h1><span class="yellow">dun</span><span class="green">zo</span></h1>
            </div>

            <div class="form-header">
                <h2>Create Your Account</h2>
                <p>Start your quick delivery journey today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="profile-photo-upload">
                    <label for="profile_photo" class="profile-photo-label">
                        <div class="profile-photo-preview" id="photoPreview">
                            <i class="fas fa-user" id="photoIcon" style="font-size:2.5em;color:#bbb;"></i>
                        </div>
                        <span class="upload-text">Profile Photo </span>
                        <button type="button" class="upload-btn" onclick="document.getElementById('profile_photo').click();">Choose Image</button>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    </label>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-toggle">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>


            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Profile photo preview
        const photoInput = document.getElementById('profile_photo');
        const photoPreview = document.getElementById('photoPreview');
        const photoIcon = document.getElementById('photoIcon');
        photoInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    photoPreview.innerHTML = '<img src="' + ev.target.result + '" alt="Profile Photo">';
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                photoPreview.innerHTML = '';
                photoPreview.appendChild(photoIcon);
            }
        });
    </script>
</body>
</html> 