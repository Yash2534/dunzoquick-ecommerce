<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .terms-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .terms-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .terms-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .terms-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .terms-section {
            margin-bottom: 50px;
        }

        .terms-section h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #00a651;
        }

        .terms-section h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin: 30px 0 15px 0;
        }

        .terms-section p {
            font-size: 1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .terms-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .terms-section li {
            color: #666;
            line-height: 1.8;
            margin-bottom: 8px;
        }

        .highlight-box {
            background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);
            border-left: 4px solid #00a651;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .highlight-box h4 {
            color: #00a651;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .warning-box {
            background: linear-gradient(135deg, #fff8f8 0%, #fff0f0 100%);
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .warning-box h4 {
            color: #dc3545;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .contact-info-box {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin: 40px 0;
            text-align: center;
        }

        .contact-info-box h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .contact-info-box p {
            color: #666;
            margin-bottom: 10px;
        }

        .contact-info-box a {
            color: #00a651;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info-box a:hover {
            text-decoration: underline;
        }

        .back-btn {
            background: #00a651;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: #008f47;
        }

        .last-updated {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 40px;
        }

        .last-updated p {
            margin: 0;
            color: #666;
            font-weight: 500;
        }

        .table-of-contents {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
        }

        .table-of-contents h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .table-of-contents ul {
            list-style: none;
            padding: 0;
        }

        .table-of-contents li {
            margin-bottom: 10px;
        }

        .table-of-contents a {
            color: #00a651;
            text-decoration: none;
            font-weight: 500;
        }

        .table-of-contents a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .terms-hero h1 {
                font-size: 2rem;
            }

            .terms-section h2 {
                font-size: 1.6rem;
            }

            .terms-section h3 {
                font-size: 1.2rem;
            }
        }

        /* Add this inside your <style> tag in about.php */
        .logo {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: 'Poppins', Arial, sans-serif;
            display: inline-block;
            user-select: none;
        }

        .logo .yellow {
            color: #ffd600;
            /* Dunzo yellow */
        }

        .logo .green {
            color: #00a651;
            /* Dunzo green */
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <span class="yellow">dun</span><span class="green">zo</span>
            </div>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </header>

    <!-- Terms Hero -->
    <section class="terms-hero">
        <h1>Terms of Service</h1>
        <p>Please read these terms carefully before using our services</p>
    </section>

    <!-- Terms Content -->
    <div class="terms-content">
        <div class="last-updated">
            <p><strong>Last Updated:</strong> January 15, 2024</p>
        </div>

        <div class="table-of-contents">
            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#acceptance">1. Acceptance of Terms</a></li>
                <li><a href="#services">2. Description of Services</a></li>
                <li><a href="#registration">3. User Registration and Accounts</a></li>
                <li><a href="#orders">4. Orders and Payment</a></li>
                <li><a href="#delivery">5. Delivery and Returns</a></li>
                <li><a href="#prohibited">6. Prohibited Activities</a></li>
                <li><a href="#intellectual">7. Intellectual Property</a></li>
                <li><a href="#privacy">8. Privacy and Data Protection</a></li>
                <li><a href="#limitation">9. Limitation of Liability</a></li>
                <li><a href="#termination">10. Termination</a></li>
                <li><a href="#governing">11. Governing Law</a></li>
                <li><a href="#changes">12. Changes to Terms</a></li>
            </ul>
        </div>

        <div class="terms-section" id="acceptance">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using the DUNZO website, mobile application, and services, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

            <div class="highlight-box">
                <h4>Important Notice</h4>
                <p>These Terms of Service constitute a legally binding agreement between you and DUNZO Technologies Private Limited. By using our services, you acknowledge that you have read, understood, and agree to be bound by these terms.</p>
            </div>
        </div>

        <div class="terms-section" id="services">
            <h2>2. Description of Services</h2>
            <p>DUNZO provides on-demand delivery services, including but not limited to:</p>
            <ul>
                <li>Grocery and household item delivery</li>
                <li>Pharmacy and medical supplies delivery</li>
                <li>Food and beverage delivery</li>
                <li>Electronics and gadgets delivery</li>
                <li>Personal care and beauty products delivery</li>
                <li>Pet supplies delivery</li>
            </ul>

            <h3>2.1 Service Availability</h3>
            <p>Our services are available in select cities and areas. Service availability may vary based on your location, time of day, and other factors. We reserve the right to modify or discontinue services at any time.</p>
        </div>

        <div class="terms-section" id="registration">
            <h2>3. User Registration and Accounts</h2>
            <p>To use certain features of our services, you must create an account. You agree to:</p>
            <ul>
                <li>Provide accurate, current, and complete information</li>
                <li>Maintain and update your account information</li>
                <li>Keep your account credentials secure</li>
                <li>Accept responsibility for all activities under your account</li>
                <li>Notify us immediately of any unauthorized use</li>
            </ul>

            <div class="warning-box">
                <h4>Account Security</h4>
                <p>You are responsible for maintaining the confidentiality of your account credentials. DUNZO is not liable for any loss or damage arising from unauthorized access to your account.</p>
            </div>
        </div>

        <div class="terms-section" id="orders">
            <h2>4. Orders and Payment</h2>
            <h3>4.1 Order Placement</h3>
            <p>When you place an order through our platform:</p>
            <ul>
                <li>You confirm that all information provided is accurate</li>
                <li>You authorize us to process your payment</li>
                <li>You agree to pay all charges associated with your order</li>
                <li>You acknowledge that prices may vary based on location and availability</li>
            </ul>

            <h3>4.2 Payment Methods</h3>
            <p>We accept various payment methods including:</p>
            <ul>
                <li>Credit and debit cards</li>
                <li>Digital wallets (UPI, Paytm, PhonePe, Google Pay)</li>
                <li>Net banking</li>
                <li>Cash on delivery (where available)</li>
            </ul>

            <h3>4.3 Pricing and Charges</h3>
            <p>All prices are displayed in Indian Rupees (INR) and include applicable taxes. Additional charges may apply for:</p>
            <ul>
                <li>Delivery fees</li>
                <li>Service charges</li>
                <li>Peak hour surcharges</li>
                <li>Special handling fees</li>
            </ul>
        </div>

        <div class="terms-section" id="delivery">
            <h2>5. Delivery and Returns</h2>
            <h3>5.1 Delivery</h3>
            <p>We strive to deliver your orders within the estimated time frame. However:</p>
            <ul>
                <li>Delivery times are estimates and may vary</li>
                <li>Factors beyond our control may affect delivery</li>
                <li>We will notify you of any significant delays</li>
                <li>You must be available to receive your order</li>
            </ul>

            <h3>5.2 Returns and Refunds</h3>
            <p>Our return and refund policy:</p>
            <ul>
                <li>Damaged or incorrect items will be replaced or refunded</li>
                <li>Returns must be reported within 24 hours of delivery</li>
                <li>Refunds will be processed within 5-7 business days</li>
                <li>Some items may not be eligible for return</li>
            </ul>
        </div>

        <div class="terms-section" id="prohibited">
            <h2>6. Prohibited Activities</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use our services for any illegal or unauthorized purpose</li>
                <li>Violate any applicable laws or regulations</li>
                <li>Interfere with or disrupt our services</li>
                <li>Attempt to gain unauthorized access to our systems</li>
                <li>Harass, abuse, or harm our delivery partners or staff</li>
                <li>Provide false or misleading information</li>
                <li>Use our services to distribute harmful content</li>
            </ul>

            <div class="warning-box">
                <h4>Consequences</h4>
                <p>Violation of these terms may result in account suspension, termination, or legal action as appropriate.</p>
            </div>
        </div>

        <div class="terms-section" id="intellectual">
            <h2>7. Intellectual Property</h2>
            <p>All content, features, and functionality of our services are owned by DUNZO and are protected by copyright, trademark, and other intellectual property laws. You may not:</p>
            <ul>
                <li>Copy, modify, or distribute our content</li>
                <li>Use our trademarks without permission</li>
                <li>Reverse engineer our software</li>
                <li>Remove or alter copyright notices</li>
            </ul>
        </div>

        <div class="terms-section" id="privacy">
            <h2>8. Privacy and Data Protection</h2>
            <p>Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy, which is incorporated into these Terms of Service by reference.</p>

            <div class="highlight-box">
                <h4>Data Protection</h4>
                <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the internet is 100% secure.</p>
            </div>
        </div>

        <div class="terms-section" id="limitation">
            <h2>9. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, DUNZO shall not be liable for:</p>
            <ul>
                <li>Indirect, incidental, or consequential damages</li>
                <li>Loss of profits, data, or business opportunities</li>
                <li>Damages arising from third-party actions</li>
                <li>Issues beyond our reasonable control</li>
            </ul>

            <p>Our total liability shall not exceed the amount paid by you for the specific service giving rise to the claim.</p>
        </div>

        <div class="terms-section" id="termination">
            <h2>10. Termination</h2>
            <p>We may terminate or suspend your account and access to our services at any time, with or without cause, with or without notice. You may terminate your account at any time by contacting our support team.</p>

            <p>Upon termination:</p>
            <ul>
                <li>Your right to use our services ceases immediately</li>
                <li>We may delete your account and data</li>
                <li>Outstanding obligations remain enforceable</li>
            </ul>
        </div>

        <div class="terms-section" id="governing">
            <h2>11. Governing Law</h2>
            <p>These Terms of Service shall be governed by and construed in accordance with the laws of India. Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the courts in Bangalore, Karnataka.</p>
        </div>

        <div class="terms-section" id="changes">
            <h2>12. Changes to Terms</h2>
            <p>We reserve the right to modify these Terms of Service at any time. We will notify users of material changes by:</p>
            <ul>
                <li>Posting updated terms on our website</li>
                <li>Sending email notifications</li>
                <li>Displaying notices in our application</li>
            </ul>

            <p>Your continued use of our services after such changes constitutes acceptance of the updated terms.</p>
        </div>

        <div class="contact-info-box">
            <h3>Contact Us</h3>
            <p>If you have any questions about these Terms of Service, please contact us:</p>
            <p><strong>Email:</strong> <a href="mailto:legal@dunzo.com">legal@dunzo.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:+9118001234567">+91 1800-123-4567</a></p>
            <p><strong>Address:</strong> DUNZO Technologies Private Limited, Rajkot, Gujrat, India</p>
        </div>
    </div>

   <?php include 'includes/footer.php'; ?>

</body>

</html>