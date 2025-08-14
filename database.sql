-- College Department Records Database
-- Run this SQL in phpMyAdmin to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS college_department_db;
USE college_department_db;

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL,
    dept_code VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create faculty/staff records table
CREATE TABLE IF NOT EXISTS faculty_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE NOT NULL,
    hire_date DATE NOT NULL,
    position VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    qualification VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    emergency_contact VARCHAR(100) NOT NULL,
    emergency_phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(dept_id)
);

-- Insert sample departments
INSERT INTO departments (dept_name, dept_code) VALUES
('Computer Science', 'CS'),
('Mathematics', 'MATH'),
('Physics', 'PHY'),
('Chemistry', 'CHEM'),
('Biology', 'BIO'),
('Engineering', 'ENG'),
('Business Administration', 'BUS'),
('English Literature', 'ENG-LIT'),
('History', 'HIST'),
('Psychology', 'PSYCH');

-- Insert sample faculty records
INSERT INTO faculty_records (employee_id, first_name, last_name, email, phone, date_of_birth, hire_date, position, department_id, salary, qualification, address, emergency_contact, emergency_phone) VALUES
('EMP001', 'John', 'Smith', 'john.smith@college.edu', '555-0101', '1980-05-15', '2015-08-01', 'Associate Professor', 1, 75000.00, 'Ph.D. Computer Science', '123 Main St, City, State 12345', 'Jane Smith', '555-0102'),
('EMP002', 'Sarah', 'Johnson', 'sarah.johnson@college.edu', '555-0103', '1985-03-22', '2018-01-15', 'Assistant Professor', 1, 65000.00, 'Ph.D. Software Engineering', '456 Oak Ave, City, State 12345', 'Mike Johnson', '555-0104'),
('EMP003', 'Michael', 'Brown', 'michael.brown@college.edu', '555-0105', '1975-11-08', '2010-09-01', 'Professor', 2, 85000.00, 'Ph.D. Mathematics', '789 Pine Rd, City, State 12345', 'Lisa Brown', '555-0106'),
('EMP004', 'Emily', 'Davis', 'emily.davis@college.edu', '555-0107', '1988-07-14', '2019-03-01', 'Lecturer', 3, 55000.00, 'M.S. Physics', '321 Elm St, City, State 12345', 'Robert Davis', '555-0108'),
('EMP005', 'David', 'Wilson', 'david.wilson@college.edu', '555-0109', '1982-12-03', '2016-06-15', 'Associate Professor', 4, 72000.00, 'Ph.D. Chemistry', '654 Maple Dr, City, State 12345', 'Mary Wilson', '555-0110'); 