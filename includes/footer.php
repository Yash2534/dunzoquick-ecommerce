<style>
/* Footer */
.footer {
  background-color: #333;
  color: #fff;
  padding: 40px 20px;
  text-align: center;
}

.footer-top {
  margin-bottom: 20px;
}

.footer-apps {
  margin: 20px 0;
}

.footer-apps span {
  display: block;
  font-size: 1.1rem;
  margin-bottom: 10px;
}

.footer-socials img {
  width: 30px;
  margin: 0 10px;
}

.footer-bottom {
  margin-top: 20px;
  font-size: 0.9rem;
  color: #ccc;
}
/* Footer container */
.footer {
  background-color: #f9f9f9;
  padding: 20px 40px;
  font-family: 'Poppins', sans-serif;
  color: #444;
  font-size: 14px;
}

/* Top section: Copyright, Apps, Socials */
.footer-top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #ddd;
  padding-bottom: 15px;
}

/* App download section */
.footer-apps {
  display: flex;
  align-items: center;
  gap: 10px;
}

.footer-apps span {
  font-weight: 600;
}

/* App store icons */
.store-icon {
  height: 35px;
  cursor: pointer;
}

/* Social media icons */
.footer-socials i {
  margin-left: 12px;
  font-size: 18px;
  color: #000;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.footer-socials i:hover {
  transform: scale(1.2);
}

/* Bottom section: Disclaimer */
.footer-bottom {
  margin-top: 15px;
  color: #666;
  font-size: 13px;
  text-align: center;
}

.footer-links {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 15px;
}

.footer-links a {
  color: #666;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: #00a651;
  text-decoration: underline;
}
.footer-socials {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 10px 20px;
  background-color: transparent; /* Optional */
}

.social-icon {
  width: 36px;
  height: 36px;
  object-fit: contain;
  transition: transform 0.3s ease;
  cursor: pointer;
}

.social-icon:hover {
  transform: scale(1.1);
}

</style>
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
                <a href="help.php">Help & Support</a>
                <a href="about.php">About Us</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
            </div>
            <p>&copy; 2024 DUNZO. All rights reserved. Quick delivery, happy customers! 🚀</p>
        </div>
    </footer>