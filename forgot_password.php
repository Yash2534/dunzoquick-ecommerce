<?php
session_start();
require_once 'email_config.php';
require_once 'config.php'; // For database connection

$step = 1;
$error = '';
$success = '';
$otp_method = $_POST['otp_method'] ?? ($_SESSION['otp_method'] ?? 'email');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_otp'])) {
        $otp_method = $_POST['otp_method'] ?? 'email';
        $contact = trim($_POST['contact'] ?? '');

        if (empty($contact)) {
            $error = 'Please enter your ' . ($otp_method === 'email' ? 'email address.' : 'phone number.');
        } else {
            // Check if user exists in the database
            $column = $otp_method === 'email' ? 'email' : 'mobile';
            $stmt = $conn->prepare("SELECT id FROM users WHERE $column = ?");
            $stmt->bind_param("s", $contact);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // User found, generate and send OTP
                $otp = strval(rand(100000, 999999));
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_contact'] = $contact;
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['otp_method'] = $otp_method;
                $_SESSION['otp_expires'] = time() + 600; // OTP is valid for 10 minutes

                if ($otp_method === 'email') {
                    $to = $contact;
                    $subject = 'Your DUNZO Password Reset OTP';
                    $message = '<h2>Your OTP for DUNZO Password Reset</h2>' .
                               '<p>Use the following OTP to reset your password:</p>' .
                               '<div style="font-size:2em;letter-spacing:8px;font-weight:bold;color:#28a745;">' . $otp . '</div>' .
                               '<p>This OTP is valid for 10 minutes.</p>' .
                               '<p>If you did not request this, please ignore this email.</p>';

                    if (sendSimpleEmail($to, $subject, $message)) {
                        $step = 2;
                    } else {
                        $error = 'Failed to send OTP email. Please try again.';
                    }
                } else {
                    // SMS sending logic would go here. For now, we just proceed.
                    $step = 2;
                }
            } else {
                $error = 'No account found with that ' . ($otp_method === 'email' ? 'email address.' : 'phone number.');
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        $otp = trim($_POST['otp'] ?? '');
        $newpass = $_POST['new_password'] ?? '';
        $confirmpass = $_POST['confirm_password'] ?? '';

        if (empty($otp) || empty($newpass) || empty($confirmpass)) {
            $error = 'Please fill in all fields.';
            $step = 2;
        } elseif ($otp !== ($_SESSION['reset_otp'] ?? '')) {
            $error = 'Invalid OTP.';
            $step = 2;
        } elseif (time() > ($_SESSION['otp_expires'] ?? 0)) {
            $error = 'OTP has expired. Please request a new one.';
            unset($_SESSION['reset_user_id'], $_SESSION['reset_contact'], $_SESSION['reset_otp'], $_SESSION['otp_method'], $_SESSION['otp_expires']);
            $step = 1;
        } elseif ($newpass !== $confirmpass) {
            $error = 'Passwords do not match.';
            $step = 2;
        } else {
            // All checks passed, update the password in the database
            $hashed_password = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['reset_user_id']);
            if ($stmt->execute()) {
                unset($_SESSION['reset_user_id'], $_SESSION['reset_contact'], $_SESSION['reset_otp'], $_SESSION['otp_method'], $_SESSION['otp_expires']);
                $success = 'Your password has been reset successfully! You can now <a href="login.php">login</a>.';
                $step = 3;
            } else {
                $error = 'Failed to reset password. Please try again.';
                $step = 2;
            }
        }
    }
}

// Check if a valid reset process is already underway
if (isset($_SESSION['reset_otp']) && $step === 1) {
    if (time() > ($_SESSION['otp_expires'] ?? 0)) {
        unset($_SESSION['reset_user_id'], $_SESSION['reset_contact'], $_SESSION['reset_otp'], $_SESSION['otp_method'], $_SESSION['otp_expires']);
        $error = "Your previous password reset request has expired. Please start over.";
    } else {
        $step = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DUNZO</title>
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
        .forgot-container {
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
            background: #e6ffed;
            color: #28a745;
            border: 1px solid #b7f5c2;
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
        .otp-info {
            color: #007bff;
            font-size: 0.98rem;
            margin-bottom: 8px;
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
        .login-link {
            text-align: center;
            margin-top: 18px;
            font-size: 1rem;
            color: #555;
        }
        .login-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .forgot-container {
                flex-direction: column;
                max-width: 400px;
            }
            .left-side {
                padding: 30px 20px;
            }
            .right-side {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <!-- Left Side - Illustration -->
        <div class="left-side">
            <div class="illustration">
                <i class="fas fa-unlock-alt"></i>
                <h2>Reset Password</h2>
                <p>Choose how you want to receive your OTP and reset your password securely.</p>
            </div>
        </div>
        <!-- Right Side - Form -->
        <div class="right-side">
            <div class="logo">
                <h1><span class="yellow">dun</span><span class="green">zo</span></h1>
            </div>
            <div class="form-header">
                <h2>Forgot Password</h2>
                <p>We'll help you recover your account</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif ($step === 1): ?>
                <form method="post" autocomplete="off">
                    <div class="form-group">
                        <label>Choose OTP Delivery Method</label>
                        <div style="display:flex;gap:30px;align-items:center;justify-content:center;">
                            <label style="font-weight:400;display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="radio" name="otp_method" value="email" <?php if($otp_method==='email') echo 'checked'; ?> style="accent-color:#28a745;">
                                <i class="fas fa-envelope" style="font-size:1.3em;color:#007bff;"></i> Email
                            </label>
                            <label style="font-weight:400;display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="radio" name="otp_method" value="phone" <?php if($otp_method==='phone') echo 'checked'; ?> style="accent-color:#28a745;">
                                <i class="fas fa-mobile-alt" style="font-size:1.3em;color:#28a745;"></i> Phone
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contact" id="contact-label">
                            <?php echo $otp_method === 'phone' ? 'Phone Number' : 'Email Address'; ?>
                        </label>
                        <input type="text" id="contact" name="contact" placeholder="<?php echo $otp_method === 'phone' ? 'Enter your phone number' : 'Enter your email address'; ?>" required autofocus>
                    </div>
                    <button type="submit" name="send_otp" class="btn btn-primary">Send OTP</button>
                </form> 
                <script>
                // Switch label and placeholder based on radio
                const radios = document.querySelectorAll('input[name="otp_method"]');
                const contactLabel = document.getElementById('contact-label');
                const contactInput = document.getElementById('contact');
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'phone') {
                            contactLabel.textContent = 'Phone Number';
                            contactInput.placeholder = 'Enter your phone number';
                        } else {
                            contactLabel.textContent = 'Email Address';
                            contactInput.placeholder = 'Enter your email address';
                        }
                    });
                });
                </script>
            <?php elseif ($step === 2): ?>
                <div class="otp-info">
                    <?php if (isset($_SESSION['otp_method']) && $_SESSION['otp_method'] === 'email'): ?>
                        An OTP has been sent to your email. Please check your inbox (and spam folder).<br>(OTP for demo: <b><?php echo $_SESSION['reset_otp']; ?></b>)
                    <?php else: ?>
                        An OTP has been sent to your phone number. (OTP for demo: <b><?php echo $_SESSION['reset_otp']; ?></b>)
                    <?php endif; ?>
                </div>
                <form method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <input type="text" id="otp" name="otp" placeholder="Enter the OTP" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                </form>
            <?php endif; ?>
            <div class="login-link">
                Remembered your password? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</body>
</html> 