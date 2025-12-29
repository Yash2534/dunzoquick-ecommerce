<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - DUNZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .about-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .about-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .about-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .mission-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 80px;
        }

        .mission-text h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .mission-text p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .mission-image {
            text-align: center;
        }

        .mission-image i {
            font-size: 200px;
            color: #00a651;
            opacity: 0.8;
        }

        .stats-section {
            background: #f8f9fa;
            padding: 60px 20px;
            margin: 60px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-card {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card i {
            font-size: 3rem;
            color: #00a651;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #666;
        }

        .team-section {
            margin: 80px 0;
        }

        .team-section h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 50px;
        }

        .team-grid {
            display: flex;
            flex-direction: row;
            gap: 40px;
            overflow-x: auto;
            justify-content: flex-start;
        }

        .team-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: #fff;
            margin: 10% auto;
            padding: 30px 40px;
            border-radius: 15px;
            max-width: 400px;
            text-align: center;
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 2rem;
            color: #888;
            cursor: pointer;
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

        .team-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a651, #28a745);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 3rem;
            overflow: hidden;
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .team-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .team-card .position {
            color: #00a651;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .team-card p {
            color: #666;
            line-height: 1.6;
        }

        .team-summary {
            font-style: italic;
            color: #00a651;
            margin-top: 10px;
        }

        .values-section {
            background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);
            padding: 80px 20px;
            margin: 60px 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .value-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .value-card i {
            font-size: 3rem;
            color: #00a651;
            margin-bottom: 20px;
        }

        .value-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .value-card p {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .mission-section {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .about-hero h1 {
                font-size: 2rem;
            }

            .mission-text h2 {
                font-size: 2rem;
            }
            .team-grid {
                flex-direction: column;
                gap: 20px;
                overflow-x: unset;
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
    <!-- About Hero -->
    <section class="about-hero">
        <h1>About DUNZO</h1>
        <p>Revolutionizing delivery with lightning-fast service and unmatched convenience</p>
    </section>

    <!-- About Content -->
    <div class="about-content">
        <!-- Mission Section -->
        <section class="mission-section">
            <div class="mission-text">
                <h2>Our Mission</h2>
                <p>At DUNZO, we believe that time is the most valuable commodity. Our mission is to deliver happiness to your doorstep in minutes, not hours.</p>
                <p>We're building India's most reliable and fastest delivery platform, connecting millions of customers with their favorite products through our network of dedicated delivery partners.</p>
                <p>From groceries to medicines, electronics to fashion, we're here to make your life easier, one delivery at a time.</p>
            </div>
            <div class="mission-image">
                <i class="fas fa-rocket"></i>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-number">10M+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Cities Served</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shipping-fast"></i>
                    <div class="stat-number">10 Min</div>
                    <div class="stat-label">Average Delivery</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-store"></i>
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Partner Stores</div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <h2 style="text-align: center; font-size: 2.5rem; font-weight: 600; color: #333; margin-bottom: 50px;">Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-bolt"></i>
                    <h3>Speed</h3>
                    <p>We understand that every minute counts. Our commitment to 10-minute delivery sets us apart from the competition.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Reliability</h3>
                    <p>Your trust is our priority. We ensure every delivery is safe, secure, and on time, every time.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-heart"></i>
                    <h3>Customer First</h3>
                    <p>Your satisfaction drives everything we do. We're here to make your life easier and more convenient.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-handshake"></i>
                    <h3>Partnership</h3>
                    <p>We believe in building strong relationships with our delivery partners, stores, and customers.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-globe"></i>
                    <h3>Sustainability</h3>
                    <p>We are committed to eco-friendly practices and reducing our environmental impact at every step of the delivery process.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Innovation</h3>
                    <p>We constantly embrace new ideas and technologies to improve our services and stay ahead in the delivery industry.</p>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <h2>Meet Our Leadership</h2>
            <div class="team-grid">
                <div class="team-card" data-name="YASH ANDRAPIYA" data-role="CEO & Founder" data-img="Image/stf/yash.jpg" data-bio="Former tech executive with 15+ years experience in logistics and e-commerce. Passionate about revolutionizing delivery services." data-summary="Visionary leader driving DUNZO's mission and growth.">
                    <div class="team-photo">
                        <img src="Image/stf/yash.jpg" alt="Yash Andrapiya" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user" style="display: none;"></i>
                    </div>
                    <h3>YASH ANDRAPIYA</h3>
                    <div class="position">CEO & Founder</div>
                </div>
                <div class="team-card" data-name="MANAN GAJJAR" data-role="CTO" data-img="Image/stf/manan.jpg" data-bio="Technology leader with expertise in building scalable platforms. Driving innovation in delivery technology." data-summary="Architect of DUNZO's technology and innovation.">
                    <div class="team-photo">
                        <img src="Image/stf/manan.jpg" alt="Manan Gajjar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user" style="display: none;"></i>
                    </div>
                    <h3>MANAN GAJJAR</h3>
                    <div class="position">CTO</div>
                </div>
                <div class="team-card" data-name="Nain Patel" data-role="Head of Operations" data-img="Image/stf/nain.jpg" data-bio="Operations expert ensuring seamless delivery experiences across all our service areas." data-summary="Ensures smooth and efficient delivery operations.">
                    <div class="team-photo">
                        <img src="Image/stf/nain.jpg" alt=" nain patel" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user" style="display: none;"></i>
                    </div>
                    <h3>Nain Patel</h3>
                    <div class="position">Head of Operations</div>
                </div>
                <div class="team-card" data-name="Parthiv Sachaniya" data-role="Chief Marketing Officer" data-img="Image/stf/Parthiv.png" data-bio="Marketing visionary with a decade of experience in brand building and digital strategy. Focused on expanding DUNZO's reach and customer engagement." data-summary="Leads brand growth and customer engagement strategies.">
                    <div class="team-photo">
                        <img src="Image/stf/Parthiv.png" alt="Parthiv " onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user" style="display: none;"></i>
                    </div>
                    <h3>Parthiv Sachaniya</h3>
                    <div class="position">Chief Marketing Officer</div>
                </div>
                <div class="team-card" data-name="Hitendrasinh Gohil" data-role="Chief Customer Experience Officer" data-img="Image/stf/hito.jpg" data-bio="Dedicated to delivering exceptional customer journeys. Expert in service design and customer support, ensuring every interaction delights our users." data-summary="Champions outstanding customer experiences at DUNZO.">
                    <div class="team-photo">
                        <img src="Image/stf/hito.jpg" alt="Hitendrasinh Gohil" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user" style="display: none;"></i>
                    </div>
                    <h3>Hitendrasinh Gohil</h3>
                    <div class="position">Chief Customer Experience Officer</div>
                </div>
            </div>

            <!-- Modal for team member details -->
            <div id="teamModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <div id="modalPhoto"></div>
                    <h3 id="modalName"></h3>
                    <div id="modalRole"></div>
                    <p id="modalBio"></p>
                    <div id="modalSummary" class="team-summary"></div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>


<script>
// Team modal popup logic
document.querySelectorAll('.team-card').forEach(function(card) {
    card.addEventListener('click', function() {
        document.getElementById('modalPhoto').innerHTML = '<img src="' + card.dataset.img + '" alt="' + card.dataset.name + '" style="width:100px;height:100px;border-radius:50%;object-fit:cover;margin-bottom:15px;">';
        document.getElementById('modalName').textContent = card.dataset.name;
        document.getElementById('modalRole').textContent = card.dataset.role;
        document.getElementById('modalBio').textContent = card.dataset.bio;
        document.getElementById('modalSummary').textContent = card.dataset.summary;
        document.getElementById('teamModal').style.display = 'block';
    });
});
document.getElementById('closeModal').onclick = function() {
    document.getElementById('teamModal').style.display = 'none';
};
window.onclick = function(event) {
    if (event.target == document.getElementById('teamModal')) {
        document.getElementById('teamModal').style.display = 'none';
    }
};
</script>
</body>
</html> 