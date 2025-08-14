<?php
require_once 'config.php';
require_once 'validation.php';

$message = '';
$messageType = '';
$formData = [];

// Get departments for dropdown
function getDepartments($pdo) {
    try {
        $stmt = $pdo->query("SELECT dept_id, dept_name, dept_code FROM departments ORDER BY dept_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    $validator = new DataValidator();
    
    // Validate and sanitize all inputs
    $employeeId = $validator->validateEmployeeId($_POST['employee_id'] ?? '');
    $firstName = $validator->validateFirstName($_POST['first_name'] ?? '');
    $lastName = $validator->validateLastName($_POST['last_name'] ?? '');
    $email = $validator->validateEmail($_POST['email'] ?? '');
    $phone = $validator->validatePhone($_POST['phone'] ?? '');
    $dateOfBirth = $validator->validateDateOfBirth($_POST['date_of_birth'] ?? '');
    $hireDate = $validator->validateHireDate($_POST['hire_date'] ?? '');
    $position = $validator->validatePosition($_POST['position'] ?? '');
    $departmentId = $validator->validateDepartmentId($_POST['department_id'] ?? '');
    $salary = $validator->validateSalary($_POST['salary'] ?? '');
    $qualification = $validator->validateQualification($_POST['qualification'] ?? '');
    $address = $validator->validateAddress($_POST['address'] ?? '');
    $emergencyContact = $validator->validateEmergencyContact($_POST['emergency_contact'] ?? '');
    $emergencyPhone = $validator->validateEmergencyPhone($_POST['emergency_phone'] ?? '');
    
    // Check for duplicate email and employee ID
    if (!$validator->hasErrors()) {
        $validator->checkEmailExists($email, $pdo);
        $validator->checkEmployeeIdExists($employeeId, $pdo);
    }
    
    // If no validation errors, insert into database
    if (!$validator->hasErrors()) {
        try {
            $sql = "INSERT INTO faculty_records (employee_id, first_name, last_name, email, phone, 
                    date_of_birth, hire_date, position, department_id, salary, qualification, 
                    address, emergency_contact, emergency_phone) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $employeeId, $firstName, $lastName, $email, $phone, $dateOfBirth, 
                $hireDate, $position, $departmentId, $salary, $qualification, 
                $address, $emergencyContact, $emergencyPhone
            ]);
            
            $message = "Faculty record added successfully!";
            $messageType = "success";
            
            // Clear form data after successful submission
            $formData = [];
            
        } catch(PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Please correct the errors below:";
        $messageType = "error";
        
        // Preserve form data for re-display
        $formData = $_POST;
    }
    
    closeDBConnection($pdo);
}

// Get departments for dropdown
$pdo = getDBConnection();
$departments = getDepartments($pdo);
closeDBConnection($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Department Records - Faculty Management</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 500;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            margin-left: 15px;
        }
        
        .btn-secondary:hover {
            box-shadow: 0 10px 20px rgba(149, 165, 166, 0.3);
        }
        
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .help-text {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏫 College Department Records</h1>
            <p>Faculty & Staff Management System</p>
        </div>
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-grid">
                    <!-- Employee ID -->
                    <div class="form-group">
                        <label for="employee_id">Employee ID <span class="required">*</span></label>
                        <input type="text" id="employee_id" name="employee_id" 
                               value="<?php echo htmlspecialchars($formData['employee_id'] ?? ''); ?>"
                               placeholder="EMP001">
                        <div class="help-text">Format: EMP001, EMP002, etc.</div>
                        <?php if (isset($validator) && isset($validator->getErrors()['employee_id'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['employee_id']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>"
                               placeholder="John">
                        <?php if (isset($validator) && isset($validator->getErrors()['first_name'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['first_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>"
                               placeholder="Smith">
                        <?php if (isset($validator) && isset($validator->getErrors()['last_name'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['last_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                               placeholder="john.smith@college.edu">
                        <?php if (isset($validator) && isset($validator->getErrors()['email'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>"
                               placeholder="555-0101">
                        <?php if (isset($validator) && isset($validator->getErrors()['phone'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['phone']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Date of Birth -->
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" 
                               value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>">
                        <div class="help-text">Age must be between 18-80 years</div>
                        <?php if (isset($validator) && isset($validator->getErrors()['date_of_birth'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['date_of_birth']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Hire Date -->
                    <div class="form-group">
                        <label for="hire_date">Hire Date <span class="required">*</span></label>
                        <input type="date" id="hire_date" name="hire_date" 
                               value="<?php echo htmlspecialchars($formData['hire_date'] ?? ''); ?>">
                        <?php if (isset($validator) && isset($validator->getErrors()['hire_date'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['hire_date']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Position -->
                    <div class="form-group">
                        <label for="position">Position <span class="required">*</span></label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo htmlspecialchars($formData['position'] ?? ''); ?>"
                               placeholder="Associate Professor">
                        <?php if (isset($validator) && isset($validator->getErrors()['position'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['position']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Department -->
                    <div class="form-group">
                        <label for="department_id">Department <span class="required">*</span></label>
                        <select id="department_id" name="department_id">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['dept_id']; ?>" 
                                        <?php echo (isset($formData['department_id']) && $formData['department_id'] == $dept['dept_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['dept_name'] . ' (' . $dept['dept_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($validator) && isset($validator->getErrors()['department_id'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['department_id']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Salary -->
                    <div class="form-group">
                        <label for="salary">Annual Salary ($) <span class="required">*</span></label>
                        <input type="number" id="salary" name="salary" 
                               value="<?php echo htmlspecialchars($formData['salary'] ?? ''); ?>"
                               placeholder="75000" min="20000" max="200000" step="1000">
                        <div class="help-text">Range: $20,000 - $200,000</div>
                        <?php if (isset($validator) && isset($validator->getErrors()['salary'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['salary']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Qualification -->
                    <div class="form-group">
                        <label for="qualification">Qualification <span class="required">*</span></label>
                        <input type="text" id="qualification" name="qualification" 
                               value="<?php echo htmlspecialchars($formData['qualification'] ?? ''); ?>"
                               placeholder="Ph.D. Computer Science">
                        <?php if (isset($validator) && isset($validator->getErrors()['qualification'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['qualification']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Address -->
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="address">Address <span class="required">*</span></label>
                        <textarea id="address" name="address" 
                                  placeholder="123 Main Street, City, State 12345"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                        <?php if (isset($validator) && isset($validator->getErrors()['address'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['address']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact Name <span class="required">*</span></label>
                        <input type="text" id="emergency_contact" name="emergency_contact" 
                               value="<?php echo htmlspecialchars($formData['emergency_contact'] ?? ''); ?>"
                               placeholder="Jane Smith">
                        <?php if (isset($validator) && isset($validator->getErrors()['emergency_contact'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['emergency_contact']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Emergency Phone -->
                    <div class="form-group">
                        <label for="emergency_phone">Emergency Phone <span class="required">*</span></label>
                        <input type="tel" id="emergency_phone" name="emergency_phone" 
                               value="<?php echo htmlspecialchars($formData['emergency_phone'] ?? ''); ?>"
                               placeholder="555-0102">
                        <?php if (isset($validator) && isset($validator->getErrors()['emergency_phone'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($validator->getErrors()['emergency_phone']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Add Faculty Record</button>
                    <a href="view_records.php" class="btn btn-secondary">View All Records</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 