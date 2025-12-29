<?php
session_start();

// If user is already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Include the central configuration file for database credentials
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Use constants from config.php for PDO connection
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        
        // Get form data
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Validation
        if (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } else {
            // Check if user exists and get their role and status
            $stmt = $pdo->prepare("SELECT id, full_name, email, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] === 'blocked') {
                    $error = "Your account has been blocked. Please contact support.";
                } else {
                    // Login successful, regenerate session ID for security
                    session_regenerate_id(true);

                    // Set common session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];

                    // Check the user's role for redirection
                    if ($user['role'] === 'admin') {
                        // Set admin-specific session variables and redirect to admin dashboard
                        $_SESSION['admin_loggedin'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['full_name'];
                        header("Location: admin/dashboard.php");
                        exit();
                    } else {
                        // Redirect regular users to the main index page
                        // Explicitly set admin_loggedin to false for regular users
                        $_SESSION['admin_loggedin'] = false;
                        
                        // Redirect to a specific page if one was requested before login
                        if (isset($_SESSION['redirect_to'])) {
                            header("Location: " . $_SESSION['redirect_to']);
                            unset($_SESSION['redirect_to']);
                            exit();
                        }
                        header("Location: index.php");
                        exit();
                    }
                }
            } else {
                $error = "Invalid email or password.";
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
    <title>Login - DUNZO</title>
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

        .login-container {
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

        .forgot-password {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: #28a745;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-password a:hover {
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

        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container {
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
    <div class="login-container">
        <!-- Left Side - Illustration -->
        <div class="left-side">
            <div class="illustration">
                <i class="fas fa-user-circle"></i>
                <h2>Welcome Back!</h2>
                <p>Login to continue your quick delivery experience</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="right-side">
            <div class="logo">
                <h1><span class="yellow">dun</span><span class="green">zo</span></h1>
            </div>

            <div class="form-header">
                <h2>Login to Your Account</h2>
                <p>Enter your credentials to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            
           

            <div class="signup-link">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
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
    </script
</body>
</html> 