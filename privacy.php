<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .privacy-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .privacy-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .privacy-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .privacy-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .privacy-section {
            margin-bottom: 50px;
        }

        .privacy-section h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #00a651;
        }

        .privacy-section h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin: 30px 0 15px 0;
        }

        .privacy-section p {
            font-size: 1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .privacy-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .privacy-section li {
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

        .contact-info-box a:hover {
            text-decoration: underline;
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

        @media (max-width: 768px) {
            .privacy-hero h1 {
                font-size: 2rem;
            }

            .privacy-section h2 {
                font-size: 1.6rem;
            }

            .privacy-section h3 {
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
    color: #ffd600; /* Dunzo yellow */
}
.logo .green {
    color: #00a651; /* Dunzo green */
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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

    <!-- Privacy Hero -->
    <section class="privacy-hero">
        <h1>Privacy Policy</h1>
        <p>Your privacy is important to us. Learn how we protect and handle your information.</p>
    </section>

    <!-- Privacy Content -->
    <div class="privacy-content">
        <div class="last-updated">
            <p><strong>Last Updated:</strong> January 15, 2024</p>
        </div>

        <div class="privacy-section">
            <h2>1. Introduction</h2>
            <p>DUNZO Technologies Private Limited ("DUNZO," "we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website, mobile application, and services.</p>
            
            <div class="highlight-box">
                <h4>Important Notice</h4>
                <p>By using our services, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our services.</p>
            </div>
        </div>

        <div class="privacy-section">
            <h2>2. Information We Collect</h2>
            
            <h3>2.1 Personal Information</h3>
            <p>We may collect the following personal information:</p>
            <ul>
                <li>Name, email address, and phone number</li>
                <li>Delivery address and location data</li>
                <li>Payment information (processed securely through third-party providers)</li>
                <li>Profile information and preferences</li>
                <li>Communication history with our support team</li>
            </ul>

            <h3>2.2 Device and Usage Information</h3>
            <p>We automatically collect certain information when you use our services:</p>
            <ul>
                <li>Device information (IP address, browser type, operating system)</li>
                <li>Usage data (pages visited, features used, time spent)</li>
                <li>Location data (with your consent)</li>
                <li>Cookies and similar tracking technologies</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>3. How We Use Your Information</h2>
            <p>We use the collected information for the following purposes:</p>
            <ul>
                <li><strong>Service Delivery:</strong> To process and fulfill your orders</li>
                <li><strong>Communication:</strong> To send order updates, notifications, and support messages</li>
                <li><strong>Personalization:</strong> To customize your experience and provide relevant recommendations</li>
                <li><strong>Security:</strong> To protect against fraud and ensure account security</li>
                <li><strong>Improvement:</strong> To analyze usage patterns and improve our services</li>
                <li><strong>Legal Compliance:</strong> To comply with applicable laws and regulations</li>
            </ul>
        </div>

        <div class="privacy-section">
            <h2>4. Information Sharing and Disclosure</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>
            
            <h3>4.1 Service Providers</h3>
            <p>We may share information with trusted third-party service providers who assist us in:</p>
            <ul>
                <li>Payment processing</li>
                <li>Delivery services</li>
                <li>Customer support</li>
                <li>Data analytics</li>
            </ul>

            <h3>4.2 Legal Requirements</h3>
            <p>We may disclose your information if required by law or in response to valid legal requests.</p>

            <h3>4.3 Business Transfers</h3>
            <p>In the event of a merger, acquisition, or sale of assets, your information may be transferred as part of the transaction.</p>
        </div>

        <div class="privacy-section">
            <h2>5. Data Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
            <ul>
                <li>Encryption of sensitive data in transit and at rest</li>
                <li>Regular security assessments and updates</li>
                <li>Access controls and authentication measures</li>
                <li>Employee training on data protection</li>
            </ul>
            
            <div class="highlight-box">
                <h4>Security Notice</h4>
                <p>While we strive to protect your information, no method of transmission over the internet is 100% secure. We cannot guarantee absolute security.</p>
            </div>
        </div>

        <div class="privacy-section">
            <h2>6. Your Rights and Choices</h2>
            <p>You have the following rights regarding your personal information:</p>
            
            <h3>6.1 Access and Correction</h3>
            <p>You can access and update your personal information through your account settings or by contacting us.</p>

            <h3>6.2 Data Portability</h3>
            <p>You can request a copy of your personal data in a structured, machine-readable format.</p>

            <h3>6.3 Deletion</h3>
            <p>You can request deletion of your personal information, subject to legal and contractual obligations.</p>

            <h3>6.4 Marketing Communications</h3>
            <p>You can opt out of marketing communications by updating your preferences or clicking the unsubscribe link.</p>
        </div>

        <div class="privacy-section">
            <h2>7. Cookies and Tracking Technologies</h2>
            <p>We use cookies and similar technologies to enhance your experience:</p>
            <ul>
                <li><strong>Essential Cookies:</strong> Required for basic functionality</li>
                <li><strong>Analytics Cookies:</strong> Help us understand how you use our services</li>
                <li><strong>Marketing Cookies:</strong> Used for personalized advertising</li>
            </ul>
            <p>You can control cookie settings through your browser preferences.</p>
        </div>

        <div class="privacy-section">
            <h2>8. Children's Privacy</h2>
            <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you believe we have collected such information, please contact us immediately.</p>
        </div>

        <div class="privacy-section">
            <h2>9. International Data Transfers</h2>
            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p>
        </div>

        <div class="privacy-section">
            <h2>10. Changes to This Privacy Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by:</p>
            <ul>
                <li>Posting the updated policy on our website</li>
                <li>Sending email notifications to registered users</li>
                <li>Displaying prominent notices in our app</li>
            </ul>
            <p>Your continued use of our services after such changes constitutes acceptance of the updated policy.</p>
        </div>

        <div class="contact-info-box">
            <h3>Contact Us</h3>
            <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
            <p><strong>Email:</strong> <a href="mailto:privacy@dunzo.com">privacy@dunzo.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:+9118001234567">+91 1800-123-4567</a></p>
            <p><strong>Address:</strong> DUNZO Technologies Private Limited, Rajkot, Gujarat, India</p>
        </div>
    </div>

    <!-- Footer -->
   <?php include 'includes/footer.php'; ?>
                            
</body>
</html> 