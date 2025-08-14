# College Department Records Management System

A comprehensive PHP-based web application for managing faculty and staff records in college departments with robust data validation and sanitization.

## Features

- **Complete CRUD Operations**: Add, view, edit, and delete faculty records
- **Data Validation & Sanitization**: Comprehensive input validation and XSS protection
- **Modern UI/UX**: Responsive design with beautiful gradients and animations
- **Search & Filter**: Search records by name, ID, or email, filter by department
- **Statistics Dashboard**: View total records, departments, and salary budget
- **Database Security**: Prepared statements to prevent SQL injection
- **Mobile Responsive**: Works perfectly on all device sizes

## Database Schema

### Tables

1. **departments** - Stores department information
   - dept_id (Primary Key)
   - dept_name
   - dept_code
   - created_at

2. **faculty_records** - Stores faculty/staff information
   - id (Primary Key)
   - employee_id (Unique)
   - first_name, last_name
   - email (Unique)
   - phone
   - date_of_birth
   - hire_date
   - position
   - department_id (Foreign Key)
   - salary
   - qualification
   - address
   - emergency_contact
   - emergency_phone
   - created_at, updated_at

## Input Fields (14 Total)

1. Employee ID (EMP001 format)
2. First Name
3. Last Name
4. Email Address
5. Phone Number
6. Date of Birth
7. Hire Date
8. Position
9. Department (Dropdown)
10. Annual Salary
11. Qualification
12. Address
13. Emergency Contact Name
14. Emergency Phone

## Validation Rules

### Employee ID
- Required
- Format: EMP001, EMP002, etc.
- Must be unique

### Names
- Required
- 2-50 characters
- Letters and spaces only

### Email
- Required
- Valid email format
- Must be unique

### Phone Numbers
- Required
- 10-20 characters
- Digits, hyphens, parentheses, spaces allowed

### Dates
- Date of Birth: Must be valid date, age 18-80
- Hire Date: Must be valid date, cannot be future

### Salary
- Required
- Numeric
- Range: $20,000 - $200,000

### Address
- Required
- 10-500 characters

### Department
- Required
- Must select from existing departments

## Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation

1. **Clone/Download the project**
   ```bash
   # Place all files in your XAMPP htdocs directory
   C:\xampp\htdocs\datavalidationandsanitization\
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Import the `database.sql` file
   - Or run the SQL commands manually

4. **Configure Database Connection**
   - Edit `config.php` if needed
   - Default settings work with XAMPP:
     - Host: localhost
     - User: root
     - Password: (empty)
     - Database: college_department_db

5. **Access the Application**
   - Open browser: http://localhost/datavalidationandsanitization/
   - Or: http://localhost/datavalidationandsanitization/index.php

## File Structure

```
datavalidationandsanitization/
├── index.php              # Main form for adding records
├── view_records.php       # View all records with search/filter
├── edit_record.php        # Edit existing records
├── config.php             # Database configuration
├── validation.php         # Data validation and sanitization class
├── database.sql           # Database schema and sample data
└── README.md              # This file
```

## Security Features

### Data Sanitization
- All inputs are trimmed, stripped of slashes, and HTML-encoded
- Prevents XSS attacks
- Removes potentially harmful characters

### SQL Injection Prevention
- Uses PDO prepared statements
- Parameterized queries
- No direct string concatenation in SQL

### Input Validation
- Server-side validation for all fields
- Client-side HTML5 validation
- Comprehensive error messages
- Duplicate checking for unique fields

### XSS Protection
- htmlspecialchars() for all output
- ENT_QUOTES flag for complete protection
- UTF-8 encoding

## Usage

### Adding Records
1. Navigate to the main page
2. Fill in all required fields
3. Submit the form
4. View success/error messages

### Viewing Records
1. Click "View All Records" button
2. Use search box to find specific records
3. Filter by department using dropdown
4. View statistics at the top

### Editing Records
1. Click "Edit" button on any record
2. Modify the required fields
3. Submit to update

### Deleting Records
1. Click "Delete" button on any record
2. Confirm the deletion
3. Record is permanently removed

## Sample Data

The database comes with:
- 10 sample departments (CS, Math, Physics, etc.)
- 5 sample faculty records
- All validation rules tested

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check if MySQL is running
   - Verify database credentials in config.php
   - Ensure database exists

2. **Form Not Submitting**
   - Check for validation errors
   - Ensure all required fields are filled
   - Check browser console for JavaScript errors

3. **Records Not Displaying**
   - Verify database has data
   - Check SQL queries in view_records.php
   - Ensure proper JOIN syntax

### Error Logs
- Check XAMPP error logs: `C:\xampp\apache\logs\error.log`
- Check PHP error logs in XAMPP control panel

## Contributing

Feel free to enhance this system with:
- Additional validation rules
- More search/filter options
- Export functionality (PDF/Excel)
- User authentication
- Role-based access control

## License

This project is open source and available under the MIT License.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the validation rules
3. Test with sample data first
4. Ensure all prerequisites are met 