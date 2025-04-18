<?php
// contact.php
include 'header.php';
?>

    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h2>Get in Touch</h2>
                <p>Have questions about Currency Buddy? We're here to help!</p>
            </div>
        </div>
    </section>

    <section class="contact-main">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Our Location</h3>
                        <p>LPU<br>Jalandhar Punjaab, PB 144411</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>support@currencybuddy.com<br>info@currencybuddy.com</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3>Call Us</h3>
                        <p>+91 9622466563<br>Mon-Fri: 9am - 5pm IST</p>
                    </div>
                    
                    <div class="contact-social">
                        <h3>Connect With Us</h3>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <div class="contact-form-container">
                    <h3>Send Us a Message</h3>
                    <?php
                    // Display message if form is submitted
                    if (isset($_GET['status']) && $_GET['status'] == 'success') {
                        echo '<div class="form-message success">Your message has been sent successfully!</div>';
                    } elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
                        echo '<div class="form-message error">There was an error sending your message. Please try again.</div>';
                    }
                    ?>
                    <form action="process_contact.php" method="POST" class="contact-form">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Message Subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" placeholder="Type your message here..." required></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="faq">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>How accurate are your currency exchange rates?</h3>
                    <p>Our currency exchange rates are updated every hour from reliable financial data sources to ensure accuracy and reliability for your conversions.</p>
                </div>
                <div class="faq-item">
                    <h3>Is my financial data secure on Currency Buddy?</h3>
                    <p>Yes, all your financial data is encrypted and securely stored. We never share your personal information with third parties.</p>
                </div>
                <div class="faq-item">
                    <h3>Can I use Currency Buddy on my mobile device?</h3>
                    <p>Yes, Currency Buddy is fully responsive and works seamlessly on desktops, tablets, and smartphones.</p>
                </div>
                <div class="faq-item">
                    <h3>Do I need to create an account to use the converter?</h3>
                    <p>No, you can use our basic currency converter without an account. However, creating a free account allows you to save conversions and access all budget tools.</p>
                </div>
            </div>
        </div>
    </section>
<?php
include 'footer.php';
?>