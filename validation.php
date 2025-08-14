<?php
// Data Validation and Sanitization Class
class DataValidator {
    
    private $errors = [];
    
    // Sanitize input data
    public function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
    
    // Validate Employee ID
    public function validateEmployeeId($employeeId) {
        $employeeId = $this->sanitize($employeeId);
        
        if (empty($employeeId)) {
            $this->errors['employee_id'] = "Employee ID is required";
        } elseif (!preg_match("/^EMP\d{3}$/", $employeeId)) {
            $this->errors['employee_id'] = "Employee ID must be in format EMP001, EMP002, etc.";
        }
        
        return $employeeId;
    }
    
    // Validate First Name
    public function validateFirstName($firstName) {
        $firstName = $this->sanitize($firstName);
        
        if (empty($firstName)) {
            $this->errors['first_name'] = "First name is required";
        } elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $firstName)) {
            $this->errors['first_name'] = "First name must be 2-50 characters and contain only letters and spaces";
        }
        
        return $firstName;
    }
    
    // Validate Last Name
    public function validateLastName($lastName) {
        $lastName = $this->sanitize($lastName);
        
        if (empty($lastName)) {
            $this->errors['last_name'] = "Last name is required";
        } elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $lastName)) {
            $this->errors['last_name'] = "Last name must be 2-50 characters and contain only letters and spaces";
        }
        
        return $lastName;
    }
    
    // Validate Email
    public function validateEmail($email) {
        $email = $this->sanitize($email);
        
        if (empty($email)) {
            $this->errors['email'] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Please enter a valid email address";
        }
        
        return $email;
    }
    
    // Validate Phone Number
    public function validatePhone($phone) {
        $phone = $this->sanitize($phone);
        
        if (empty($phone)) {
            $this->errors['phone'] = "Phone number is required";
        } elseif (!preg_match("/^[\d\-\+\(\)\s]{10,20}$/", $phone)) {
            $this->errors['phone'] = "Please enter a valid phone number";
        }
        
        return $phone;
    }
    
    // Validate Date of Birth
    public function validateDateOfBirth($dob) {
        $dob = $this->sanitize($dob);
        
        if (empty($dob)) {
            $this->errors['date_of_birth'] = "Date of birth is required";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $dob);
            if (!$date || $date->format('Y-m-d') !== $dob) {
                $this->errors['date_of_birth'] = "Please enter a valid date";
            } else {
                $today = new DateTime();
                $age = $today->diff($date)->y;
                if ($age < 18 || $age > 80) {
                    $this->errors['date_of_birth'] = "Age must be between 18 and 80 years";
                }
            }
        }
        
        return $dob;
    }
    
    // Validate Hire Date
    public function validateHireDate($hireDate) {
        $hireDate = $this->sanitize($hireDate);
        
        if (empty($hireDate)) {
            $this->errors['hire_date'] = "Hire date is required";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $hireDate);
            if (!$date || $date->format('Y-m-d') !== $hireDate) {
                $this->errors['hire_date'] = "Please enter a valid date";
            } else {
                $today = new DateTime();
                if ($date > $today) {
                    $this->errors['hire_date'] = "Hire date cannot be in the future";
                }
            }
        }
        
        return $hireDate;
    }
    
    // Validate Position
    public function validatePosition($position) {
        $position = $this->sanitize($position);
        
        if (empty($position)) {
            $this->errors['position'] = "Position is required";
        } elseif (!preg_match("/^[a-zA-Z\s]{3,100}$/", $position)) {
            $this->errors['position'] = "Position must be 3-100 characters and contain only letters and spaces";
        }
        
        return $position;
    }
    
    // Validate Department ID
    public function validateDepartmentId($deptId) {
        $deptId = $this->sanitize($deptId);
        
        if (empty($deptId)) {
            $this->errors['department_id'] = "Department is required";
        } elseif (!is_numeric($deptId) || $deptId < 1) {
            $this->errors['department_id'] = "Please select a valid department";
        }
        
        return $deptId;
    }
    
    // Validate Salary
    public function validateSalary($salary) {
        $salary = $this->sanitize($salary);
        
        if (empty($salary)) {
            $this->errors['salary'] = "Salary is required";
        } elseif (!is_numeric($salary) || $salary < 20000 || $salary > 200000) {
            $this->errors['salary'] = "Salary must be between $20,000 and $200,000";
        }
        
        return $salary;
    }
    
    // Validate Qualification
    public function validateQualification($qualification) {
        $qualification = $this->sanitize($qualification);
        
        if (empty($qualification)) {
            $this->errors['qualification'] = "Qualification is required";
        } elseif (strlen($qualification) < 5 || strlen($qualification) > 200) {
            $this->errors['qualification'] = "Qualification must be between 5 and 200 characters";
        }
        
        return $qualification;
    }
    
    // Validate Address
    public function validateAddress($address) {
        $address = $this->sanitize($address);
        
        if (empty($address)) {
            $this->errors['address'] = "Address is required";
        } elseif (strlen($address) < 10 || strlen($address) > 500) {
            $this->errors['address'] = "Address must be between 10 and 500 characters";
        }
        
        return $address;
    }
    
    // Validate Emergency Contact
    public function validateEmergencyContact($emergencyContact) {
        $emergencyContact = $this->sanitize($emergencyContact);
        
        if (empty($emergencyContact)) {
            $this->errors['emergency_contact'] = "Emergency contact is required";
        } elseif (!preg_match("/^[a-zA-Z\s]{2,100}$/", $emergencyContact)) {
            $this->errors['emergency_contact'] = "Emergency contact must be 2-100 characters and contain only letters and spaces";
        }
        
        return $emergencyContact;
    }
    
    // Validate Emergency Phone
    public function validateEmergencyPhone($emergencyPhone) {
        $emergencyPhone = $this->sanitize($emergencyPhone);
        
        if (empty($emergencyPhone)) {
            $this->errors['emergency_phone'] = "Emergency phone is required";
        } elseif (!preg_match("/^[\d\-\+\(\)\s]{10,20}$/", $emergencyPhone)) {
            $this->errors['emergency_phone'] = "Please enter a valid emergency phone number";
        }
        
        return $emergencyPhone;
    }
    
    // Check if email already exists in database
    public function checkEmailExists($email, $pdo, $excludeId = null) {
        try {
            $sql = "SELECT id FROM faculty_records WHERE email = ?";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                $this->errors['email'] = "This email address is already registered";
                return true;
            }
            return false;
        } catch(PDOException $e) {
            $this->errors['database'] = "Database error: " . $e->getMessage();
            return false;
        }
    }
    
    // Check if employee ID already exists
    public function checkEmployeeIdExists($employeeId, $pdo, $excludeId = null) {
        try {
            $sql = "SELECT id FROM faculty_records WHERE employee_id = ?";
            $params = [$employeeId];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                $this->errors['employee_id'] = "This Employee ID is already registered";
                return true;
            }
            return false;
        } catch(PDOException $e) {
            $this->errors['database'] = "Database error: " . $e->getMessage();
            return false;
        }
    }
    
    // Get all validation errors
    public function getErrors() {
        return $this->errors;
    }
    
    // Check if there are any validation errors
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    // Clear all errors
    public function clearErrors() {
        $this->errors = [];
    }
}
?> 