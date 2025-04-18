<?php
// signup.php
include 'header.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'signupProcess.php';
}
?>

<section class="auth-container">
    <div class="container">
        <div class="auth-form-container">
            <div class="auth-header">
                <h2>Create Your Account</h2>
                <p>Join thousands of users taking control of their finances</p>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="signup.php" method="post" class="auth-form">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <!-- Password input section with fixed positioning -->
<div class="form-group">
    <label for="password">Password</label>
    <div class="input-wrapper">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    <div class="password-strength-meter">
        <div class="strength-bar"></div>
    </div>
    <small>Use at least 8 characters with letters, numbers, and symbols</small>
</div>

<!-- Confirm password input section with fixed positioning -->
<div class="form-group">
    <label for="confirm_password">Confirm Password</label>
    <div class="input-wrapper">
        <i class="fas fa-lock"></i>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
            <i class="fas fa-eye"></i>
        </button>
    </div>
</div>
                
                <div class="form-group terms">
                    <input type="checkbox" id="terms" name="terms" required style="width:0px;">
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                
                <div class="social-login">
                    <p>Or sign up with</p>
                    <div class="social-buttons">
                        <a href="#" class="social-btn google"><i class="fab fa-google"></i> Google</a>
                        <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                    </div>
                </div>
                
                <div class="auth-footer">
                    Already have an account? <a href="login.php">Log In</a>
                </div>
            </form>
        </div>
        <div class="auth-banner">
            <h3>Why Join Us?</h3>
            <ul class="benefits-list">
                <li><i class="fas fa-check-circle"></i> Instant currency conversion</li>
                <li><i class="fas fa-check-circle"></i> Smart budget recommendations</li>
                <li><i class="fas fa-check-circle"></i> Track spending habits</li>
                <li><i class="fas fa-check-circle"></i> Visualize your financial growth</li>
            </ul>
            <div class="testimonial">
                <p>"This app changed how I manage my finances when traveling internationally!"</p>
                <div class="testimonial-author">
                    <span>Sarah M.</span>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.strength-bar');
    
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 50) {
            strengthBar.style.backgroundColor = '#ff4d4d';
        } else if (strength < 75) {
            strengthBar.style.backgroundColor = '#ffd633';
        } else {
            strengthBar.style.backgroundColor = '#66cc66';
        }
    });
    
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            // Toggle the input type between password and text
            if (input.type === 'password') {
                input.type = 'text';
                input.classList.add('password-visible');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                input.classList.remove('password-visible');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>