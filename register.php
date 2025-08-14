<?php
require_once 'config.php';
require_once 'auth.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';
$demoCode = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($fullName)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters long.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $result = $auth->register($username, $email, $password, $fullName);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            $demoCode = $result['demo_code'];
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

closeDBConnection($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - College Department Records</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .register-header p {
            opacity: 0.9;
            font-size: 0.9em;
        }
        
        .register-form {
            padding: 40px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
        }
        
        .form-group input.error {
            border-color: #e74c3c;
        }
        
        .form-group input.valid {
            border-color: #27ae60;
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .password-requirements h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .requirement.valid {
            color: #27ae60;
        }
        
        .requirement.invalid {
            color: #e74c3c;
        }
        
        .requirement-icon {
            margin-right: 8px;
            font-size: 14px;
        }
        
        .btn {
            width: 100%;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .register-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #e1e8ed;
        }
        
        .register-footer a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-footer a:hover {
            color: #2ecc71;
        }
        
        .demo-code-display {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .demo-code-display h3 {
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .demo-code {
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle input {
            padding-right: 50px;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .password-toggle-btn:hover {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Register</h1>
            <p>Create your account</p>
        </div>
        
        <div class="register-form">
            <?php if ($demoCode): ?>
                <div class="demo-code-display">
                    <h3>🎉 Registration Successful!</h3>
                    <p>Your demo code is:</p>
                    <div class="demo-code"><?php echo htmlspecialchars($demoCode); ?></div>
                    <p style="margin-top: 10px; font-size: 0.9em;">Please save this code. You'll need it for login.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="Enter your email" required>
                </div>
                
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <div class="requirement" id="req-length">
                        <span class="requirement-icon">❌</span>
                        At least 8 characters
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <span class="requirement-icon">❌</span>
                        One uppercase letter
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <span class="requirement-icon">❌</span>
                        One lowercase letter
                    </div>
                    <div class="requirement" id="req-number">
                        <span class="requirement-icon">❌</span>
                        One number
                    </div>
                    <div class="requirement" id="req-special">
                        <span class="requirement-icon">❌</span>
                        One special character (@$!%*?&)
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" 
                               placeholder="Create a strong password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-toggle">
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">
                            👁️
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">Create Account</button>
            </form>
        </div>
        
        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
        
        function validatePassword(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[@$!%*?&]/.test(password)
            };
            
            // Update requirement indicators
            document.getElementById('req-length').className = requirements.length ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-uppercase').className = requirements.uppercase ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-lowercase').className = requirements.lowercase ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-number').className = requirements.number ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-special').className = requirements.special ? 'requirement valid' : 'requirement invalid';
            
            // Update icons
            document.querySelectorAll('.requirement.valid .requirement-icon').forEach(icon => icon.textContent = '✅');
            document.querySelectorAll('.requirement.invalid .requirement-icon').forEach(icon => icon.textContent = '❌');
            
            return Object.values(requirements).every(req => req);
        }
        
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');
            
            const passwordValid = validatePassword(password);
            const passwordsMatch = password === confirmPassword;
            
            // Update input styles
            document.getElementById('password').className = passwordValid ? 'valid' : 'error';
            document.getElementById('confirm_password').className = passwordsMatch ? 'valid' : 'error';
            
            // Enable/disable submit button
            submitBtn.disabled = !(passwordValid && passwordsMatch);
        }
        
        // Add event listeners
        document.getElementById('password').addEventListener('input', validateForm);
        document.getElementById('confirm_password').addEventListener('input', validateForm);
        
        // Auto-focus on first field
        document.getElementById('full_name').focus();
    </script>
</body>
</html> 