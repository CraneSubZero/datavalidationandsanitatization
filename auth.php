<?php
session_start();

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Generate secure random token
    private function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // Generate demo code
    private function generateDemoCode() {
        $prefix = 'DEMO';
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $year . $random;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    // Check if user is admin
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }
    
    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, full_name, role, demo_code FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }
    
    // Check login attempts for brute force protection
    private function checkLoginAttempts($username, $ip) {
        try {
            // Clean old attempts (older than 15 minutes)
            $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stmt->execute();
            
            // Count recent failed attempts
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stmt->execute([$username, $ip]);
            $attempts = $stmt->fetchColumn();
            
            return $attempts < 5; // Allow max 5 attempts in 15 minutes
        } catch(PDOException $e) {
            return true; // Allow login if there's a database error
        }
    }
    
    // Record login attempt
    private function recordLoginAttempt($username, $ip, $success) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)");
            $stmt->execute([$username, $ip, $success]);
        } catch(PDOException $e) {
            // Silently fail
        }
    }
    
    // Login user
    public function login($username, $password) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Check for brute force attempts
        if (!$this->checkLoginAttempts($username, $ip)) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again in 15 minutes.'];
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, password_hash, full_name, role, demo_code, is_active FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !$user['is_active']) {
                $this->recordLoginAttempt($username, $ip, false);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Update last login
                $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['demo_code'] = $user['demo_code'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $this->recordLoginAttempt($username, $ip, true);
                
                return [
                    'success' => true, 
                    'message' => 'Login successful!',
                    'demo_code' => $user['demo_code'],
                    'user' => $user
                ];
            } else {
                $this->recordLoginAttempt($username, $ip, false);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
    }
    
    // Register new user
    public function register($username, $email, $password, $fullName) {
        try {
            // Check if username exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username already exists.'];
            }
            
            // Check if email exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already registered.'];
            }
            
            // Validate password strength
            if (strlen($password) < 8) {
                return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
            }
            
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
                return ['success' => false, 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'];
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate demo code
            $demoCode = $this->generateDemoCode();
            
            // Insert new user
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, demo_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $passwordHash, $fullName, $demoCode]);
            
            return [
                'success' => true, 
                'message' => 'Registration successful! You can now login.',
                'demo_code' => $demoCode
            ];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    // Logout user
    public function logout() {
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        return ['success' => true, 'message' => 'Logged out successfully.'];
    }
    
    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    // Require admin
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: index.php?error=access_denied');
            exit();
        }
    }
    
    // Change password
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }
            
            // Validate new password
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
            }
            
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $newPassword)) {
                return ['success' => false, 'message' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'];
            }
            
            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newPasswordHash, $userId]);
            
            return ['success' => true, 'message' => 'Password changed successfully.'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to change password. Please try again.'];
        }
    }
}
?> 