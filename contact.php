<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Support - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .contact-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .contact-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 80px;
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .contact-form h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.1);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background: linear-gradient(135deg, #00a651, #28a745);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 166, 81, 0.3);
        }

        .contact-info {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 15px;
        }

        .contact-info h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .contact-item i {
            font-size: 1.5rem;
            color: #00a651;
            width: 40px;
            text-align: center;
        }

        .contact-item-content h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .contact-item-content p {
            color: #666;
            font-size: 0.9rem;
        }

        .faq-section {
            margin: 80px 0;
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
            padding: 20px;
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
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 200px;
        }

        .faq-answer p {
            color: #666;
            line-height: 1.6;
        }

        .support-options {
            background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);
            padding: 60px 20px;
            margin: 60px 0;
            border-radius: 15px;
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
            max-width: 1200px;
            margin: 0 auto;
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
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .support-card a:hover {
            background: #008f47;
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .contact-hero h1 {
                font-size: 2rem;
            }

            .contact-form,
            .contact-info {
                padding: 30px 20px;
            }
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

    <!-- Contact Hero -->
    <section class="contact-hero">
        <h1>Contact & Support</h1>
        <p>We're here to help! Get in touch with our support team</p>
    </section>

    <!-- Contact Content -->
    <div class="contact-content">
        <!-- Contact Form and Info -->
        <div class="contact-grid">
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="delivery-issue">Delivery Issue</option>
                            <option value="payment-problem">Payment Problem</option>
                            <option value="app-support">App Support</option>
                            <option value="partnership">Partnership Inquiry</option>
                            <option value="general">General Inquiry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Tell us how we can help you..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <h2>Get in Touch</h2>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div class="contact-item-content">
                        <h3>Customer Support</h3>
                        <p>+91 1800-123-4567<br>Available 24/7</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div class="contact-item-content">
                        <h3>Email Support</h3>
                        <p>support@dunzo.com<br>help@dunzo.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="contact-item-content">
                        <h3>Head Office</h3>
                        <p>DUNZO Technologies Pvt Ltd<br>Bangalore, Karnataka, India</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div class="contact-item-content">
                        <h3>Business Hours</h3>
                        <p>Monday - Sunday<br>24/7 Customer Support</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Options -->
        <section class="support-options">
            <h2>Quick Support Options</h2>
            <div class="support-grid">
                <div class="support-card">
                    <i class="fas fa-comments"></i>
                    <h3>Live Chat</h3>
                    <p>Chat with our support team in real-time for instant help with your queries.</p>
                    <a href="#">Start Chat</a>
                </div>
                <div class="support-card">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Call Support</h3>
                    <p>Speak directly with our customer service representatives for personalized assistance.</p>
                    <a href="tel:+9118001234567">Call Now</a>
                </div>
                <div class="support-card">
                    <i class="fas fa-envelope-open"></i>
                    <h3>Email Support</h3>
                    <p>Send us a detailed email and we'll get back to you within 24 hours.</p>
                    <a href="mailto:support@dunzo.com">Send Email</a>
                </div>
            </div>
        </section>

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
                        <p>Our standard delivery time is 10-15 minutes for most items. However, delivery time may vary based on your location and order size. You can track your order in real-time through our app.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What payment methods do you accept?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We accept all major payment methods including credit/debit cards, UPI, digital wallets (Paytm, PhonePe, Google Pay), and cash on delivery.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        Can I cancel my order?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can cancel your order within 2 minutes of placing it. After that, please contact our support team immediately for assistance.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What if my order is damaged or incorrect?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>If you receive damaged or incorrect items, please contact us immediately. We'll arrange a replacement or refund within 24 hours.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        Do you deliver to my area?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We currently serve 500+ cities across India. Enter your location on our website or app to check if we deliver to your area.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        How can I become a delivery partner?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>To become a delivery partner, download our partner app and complete the registration process. We'll guide you through the onboarding and training.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-top">
            <div class="footer-apps">
                <span>Download our app</span>
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="store-icon">
            </div>
            <div class="footer-socials">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-linkedin"></i>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-links">
                <a href="profile.php">My Profile</a>
                <a href="order.php">My Orders</a>
                <a href="location.php">Delivery Areas</a>
                <a href="contact.php">Help & Support</a>
                <a href="about.php">About Us</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
            </div>
            <p>&copy; 2024 DUNZO. All rights reserved. Quick delivery, happy customers! 🚀</p>
        </div>
    </footer>

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
    </script>
</body>
</html> 