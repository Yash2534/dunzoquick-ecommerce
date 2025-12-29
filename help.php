<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .help-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .help-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .help-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .help-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .help-search {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
            text-align: center;
        }

        .help-search h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.1);
        }

        .search-btn {
            background: #00a651;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-btn:hover {
            background: #008f47;
        }

        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .help-category {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .help-category:hover {
            transform: translateY(-5px);
        }

        .help-category i {
            font-size: 3rem;
            color: #00a651;
            margin-bottom: 20px;
        }

        .help-category h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .help-category p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .help-category a {
            color: #00a651;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .help-category a:hover {
            text-decoration: underline;
        }

        .faq-section {
            margin: 60px 0;
        }

        .faq-section h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 50px;
        }

        .faq-grid {
            display: grid;
            gap: 20px;
        }

        .faq-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            padding: 25px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #333;
            transition: background 0.3s ease;
        }

        .faq-question:hover {
            background: #e9ecef;
        }

        .faq-question i {
            color: #00a651;
            transition: transform 0.3s ease;
        }

        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-answer.active {
            padding: 25px;
            max-height: 300px;
        }

        .faq-answer p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .faq-answer ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .faq-answer li {
            color: #666;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .support-options {
            background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);
            padding: 60px 20px;
            border-radius: 15px;
            margin: 60px 0;
        }

        .support-options h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 50px;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .support-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .support-card:hover {
            transform: translateY(-5px);
        }

        .support-card i {
            font-size: 3rem;
            color: #00a651;
            margin-bottom: 20px;
        }

        .support-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .support-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .support-card a {
            background: #00a651;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
            display: inline-block;
        }

        .support-card a:hover {
            background: #008f47;
        }

        .troubleshooting {
            background: #f8f9fa;
            padding: 60px 20px;
            border-radius: 15px;
            margin: 60px 0;
        }

        .troubleshooting h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 50px;
        }

        .trouble-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .trouble-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .trouble-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .trouble-card h3 i {
            color: #00a651;
        }

        .trouble-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .trouble-card ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .trouble-card li {
            color: #666;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .contact-info-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 40px 0;
        }

        .contact-info-box h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
        }

        .contact-info-box p {
            color: #666;
            margin-bottom: 15px;
        }

        .contact-info-box a {
            color: #00a651;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info-box a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .help-hero h1 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
            }

            .help-categories {
                grid-template-columns: 1fr;
            }

            .support-grid {
                grid-template-columns: 1fr;
            }

            .trouble-grid {
                grid-template-columns: 1fr;
            }
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

    <!-- Help Hero -->
    <section class="help-hero">
        <h1>Help & Support</h1>
        <p>We're here to help you with any questions or issues</p>
    </section>

    <!-- Help Content -->
    <div class="help-content">
        <!-- Search Section -->
        <section class="help-search">
            <h2>How can we help you?</h2>
            <form class="search-form">
                <input type="text" class="search-input" placeholder="Search for help topics, FAQs, or contact support..." required>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </section>

        <!-- Help Categories -->
        <div class="help-categories">
            <div class="help-category" onclick="scrollToSection('ordering')">
                <i class="fas fa-shopping-cart"></i>
                <h3>Ordering & Payment</h3>
                <p>Learn about placing orders, payment methods, and managing your purchases.</p>
                <a href="#ordering">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="help-category" onclick="scrollToSection('delivery')">
                <i class="fas fa-shipping-fast"></i>
                <h3>Delivery & Tracking</h3>
                <p>Track your orders, understand delivery times, and manage delivery preferences.</p>
                <a href="#delivery">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="help-category" onclick="scrollToSection('account')">
                <i class="fas fa-user-circle"></i>
                <h3>Account & Profile</h3>
                <p>Manage your account, update profile information, and handle account settings.</p>
                <a href="#account">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="help-category" onclick="scrollToSection('returns')">
                <i class="fas fa-undo"></i>
                <h3>Returns & Refunds</h3>
                <p>Understand our return policy, request refunds, and handle damaged items.</p>
                <a href="#returns">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="help-category" onclick="scrollToSection('app')">
                <i class="fas fa-mobile-alt"></i>
                <h3>App & Technical</h3>
                <p>Get help with app issues, technical problems, and platform features.</p>
                <a href="#app">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="help-category" onclick="scrollToSection('partnership')">
                <i class="fas fa-handshake"></i>
                <h3>Partnership</h3>
                <p>Information for delivery partners, store partners, and business collaborations.</p>
                <a href="#partnership">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- FAQ Section -->
        <section class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        How long does delivery take?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Our standard delivery time is 10-15 minutes for most items. However, delivery time may vary based on:</p>
                        <ul>
                            <li>Your location and distance from the store</li>
                            <li>Order size and complexity</li>
                            <li>Current demand and traffic conditions</li>
                            <li>Weather conditions</li>
                        </ul>
                        <p>You can track your order in real-time through our app to get live updates on delivery status.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What payment methods do you accept?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We accept all major payment methods including:</p>
                        <ul>
                            <li>Credit and debit cards (Visa, MasterCard, American Express)</li>
                            <li>Digital wallets (UPI, Paytm, PhonePe, Google Pay, Amazon Pay)</li>
                            <li>Net banking</li>
                            <li>Cash on delivery (available in select areas)</li>
                        </ul>
                        <p>All online payments are processed securely through our trusted payment partners.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        Can I cancel my order?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can cancel your order under certain conditions:</p>
                        <ul>
                            <li>Within 2 minutes of placing the order (free cancellation)</li>
                            <li>If the store hasn't started preparing your order</li>
                            <li>In case of technical issues or errors</li>
                        </ul>
                        <p>After the 2-minute window, please contact our support team immediately for assistance. Cancellation fees may apply.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What if my order is damaged or incorrect?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>If you receive damaged or incorrect items:</p>
                        <ul>
                            <li>Report the issue within 24 hours of delivery</li>
                            <li>Take photos of the damaged items</li>
                            <li>Contact our support team immediately</li>
                            <li>We'll arrange a replacement or full refund</li>
                        </ul>
                        <p>Our customer support team will guide you through the process and ensure a quick resolution.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        Do you deliver to my area?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We currently serve 500+ cities across India. To check if we deliver to your area:</p>
                        <ul>
                            <li>Enter your location on our website or app</li>
                            <li>Check the delivery availability notification</li>
                            <li>Contact our support team for specific area queries</li>
                        </ul>
                        <p>We're constantly expanding our service areas. If we don't deliver to your area yet, you can register for notifications when we launch there.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        How can I become a delivery partner?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>To become a delivery partner:</p>
                        <ul>
                            <li>Download our partner app from Google Play Store</li>
                            <li>Complete the registration process with required documents</li>
                            <li>Attend our orientation and training session</li>
                            <li>Complete the verification process</li>
                            <li>Start accepting delivery requests</li>
                        </ul>
                        <p>We offer competitive earnings, flexible working hours, and comprehensive support to our delivery partners.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Support Options -->
        <section class="support-options">
            <h2>Get in Touch</h2>
            <div class="support-grid">
                <div class="support-card">
                    <i class="fas fa-comments"></i>
                    <h3>Live Chat</h3>
                    <p>Chat with our support team in real-time for instant help with your queries and issues.</p>
                    <a href="#">Start Chat Now</a>
                </div>
                
                <div class="support-card">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Call Support</h3>
                    <p>Speak directly with our customer service representatives for personalized assistance.</p>
                    <a href="tel:+9118001234567">Call +91 1800-123-4567</a>
                </div>
                
                <div class="support-card">
                    <i class="fas fa-envelope-open"></i>
                    <h3>Email Support</h3>
                    <p>Send us a detailed email and we'll get back to you within 24 hours with a solution.</p>
                    <a href="mailto:support@dunzo.com">Send Email</a>
                </div>
            </div>
        </section>

        <!-- Troubleshooting Section -->
        <section class="troubleshooting">
            <h2>Common Issues & Solutions</h2>
            <div class="trouble-grid">
                <div class="trouble-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> App Not Working</h3>
                    <p>If the DUNZO app is not working properly:</p>
                    <ul>
                        <li>Check your internet connection</li>
                        <li>Update the app to the latest version</li>
                        <li>Clear app cache and data</li>
                        <li>Restart your device</li>
                        <li>Uninstall and reinstall the app</li>
                    </ul>
                </div>
                
                <div class="trouble-card">
                    <h3><i class="fas fa-credit-card"></i> Payment Issues</h3>
                    <p>If you're experiencing payment problems:</p>
                    <ul>
                        <li>Verify your payment method details</li>
                        <li>Check if your card is active and has sufficient balance</li>
                        <li>Try a different payment method</li>
                        <li>Contact your bank for transaction issues</li>
                        <li>Use cash on delivery if available</li>
                    </ul>
                </div>
                
                <div class="trouble-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Location Issues</h3>
                    <p>If there are problems with location services:</p>
                    <ul>
                        <li>Enable location services on your device</li>
                        <li>Allow location access for the DUNZO app</li>
                        <li>Check if GPS is turned on</li>
                        <li>Try refreshing the location</li>
                        <li>Enter your address manually if needed</li>
                    </ul>
                </div>
                
                <div class="trouble-card">
                    <h3><i class="fas fa-clock"></i> Delivery Delays</h3>
                    <p>If your delivery is taking longer than expected:</p>
                    <ul>
                        <li>Check the estimated delivery time in the app</li>
                        <li>Track your order in real-time</li>
                        <li>Contact the delivery partner directly</li>
                        <li>Check for any notifications about delays</li>
                        <li>Contact support if delay is significant</li>
                    </ul>
                </div>
                <div class="trouble-card">
                    <h3><i class="fas fa-user"></i> Account Issues</h3>
                    <p>If you're having trouble with your account:</p>
                    <ul>
                        <li>Ensure you are using the correct login credentials</li>
                        <li>Reset your password if you forgot it</li>
                        <li>Check if your account is active and not suspended</li>
                        <li>Update your app to the latest version</li>
                        <li>Contact support for account recovery</li>
                    </ul>
                </div>
                <div class="trouble-card">
                    <h3><i class="fas fa-tag"></i> Promo Code Not Working</h3>
                    <p>If your promo code is not applying:</p>
                    <ul>
                        <li>Check if the promo code is still valid and not expired</li>
                        <li>Ensure you meet the minimum order requirements</li>
                        <li>Verify the promo code is entered correctly</li>
                        <li>Check if the promo code is applicable to your order type</li>
                        <li>Contact support if the issue persists</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Contact Information -->
        <div class="contact-info-box">
            <h3>Still Need Help?</h3>
            <p>Our support team is available 24/7 to assist you with any questions or concerns.</p>
            <p><strong>Email:</strong> <a href="mailto:support@dunzo.com">support@dunzo.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:+9118001234567">+91 1800-123-4567</a></p>
            <p><strong>Business Hours:</strong> 24/7 Customer Support</p>
            <p><strong>Address:</strong> DUNZO Technologies Private Limited, Rajkot, Gujrat, India</p>
        </div>
    </div>

   <?php include 'includes/footer.php'; ?>
    <script>
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-answer').forEach(item => {
                if (item !== answer) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current FAQ item
            answer.classList.toggle('active');
            
            // Rotate icon
            if (answer.classList.contains('active')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }

        function scrollToSection(sectionId) {
            // This function would scroll to specific help sections
            // For now, it just logs the section
            console.log('Scrolling to section:', sectionId);
        }
    </script>
</body>


</html> 