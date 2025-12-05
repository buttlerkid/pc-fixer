<?php
// Ensure functions are available for getThemeCss if this was a standalone include, 
// but usually it's included in pages that already have functions.
?>
<footer class="footer-modern">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col brand-col">
                <a href="/" class="footer-logo">
                    <i class="fa-solid fa-microchip"></i>
                    <span>LocalTechFix</span>
                </a>
                <p class="footer-desc">
                    Expert PC and laptop repair services brought directly to your door. 
                    Fast, reliable, and friendly support for all your tech needs.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="/#hero">Home</a></li>
                    <li><a href="/#services">Services</a></li>
                    <li><a href="/#process">How it Works</a></li>
                    <li><a href="/knowledge-base.php">Knowledge Base</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="footer-col">
                <h3>Services</h3>
                <ul class="footer-links">
                    <li><a href="/#services">Hardware Repair</a></li>
                    <li><a href="/#services">Virus Removal</a></li>
                    <li><a href="/#services">Data Recovery</a></li>
                    <li><a href="/#services">Network Setup</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-col">
                <h3>Contact Us</h3>
                <ul class="contact-list">
                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <span>123 Tech Street, Silicon Valley, CA</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-phone"></i>
                        <span>(555) 123-4567</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-envelope"></i>
                        <span>support@localtechfix.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; <?= date('Y') ?> LocalTechFix. All rights reserved.
            </div>
            <div class="social-links-modern">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>
