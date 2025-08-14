<?php
require_once 'config.php';
require_once 'auth.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);

// Require login
$auth->requireLogin();

$message = '';
$records = [];
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';

// Get departments for filter
function getDepartments($pdo) {
    try {
        $stmt = $pdo->query("SELECT dept_id, dept_name, dept_code FROM departments ORDER BY dept_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get faculty records with search and filter
function getFacultyRecords($pdo, $search = '', $department = '') {
    try {
        $sql = "SELECT fr.*, d.dept_name, d.dept_code 
                FROM faculty_records fr 
                JOIN departments d ON fr.department_id = d.dept_id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (fr.first_name LIKE ? OR fr.last_name LIKE ? OR fr.employee_id LIKE ? OR fr.email LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($department)) {
            $sql .= " AND fr.department_id = ?";
            $params[] = $department;
        }
        
        $sql .= " ORDER BY fr.last_name, fr.first_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Delete record
if (isset($_POST['delete_id'])) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM faculty_records WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $message = "Record deleted successfully!";
    } catch(PDOException $e) {
        $message = "Error deleting record: " . $e->getMessage();
    }
    closeDBConnection($pdo);
}

// Get data
$pdo = getDBConnection();
$departments = getDepartments($pdo);
$records = getFacultyRecords($pdo, $search, $department);
closeDBConnection($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Faculty Records - College Department Records</title>
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
            max-width: 1400px;
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
        
        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .records-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .records-table th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .records-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e1e8ed;
        }
        
        .records-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .records-table tr:last-child td {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        .no-records {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-size: 1.2em;
        }
        
        @media (max-width: 768px) {
            .search-filters {
                grid-template-columns: 1fr;
            }
            
            .records-table {
                font-size: 14px;
            }
            
            .records-table th,
            .records-table td {
                padding: 8px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Faculty Records</h1>
            <p>View and manage all faculty and staff records</p>
        </div>
        
        <div class="content">
            <!-- User Info -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <p style="margin: 5px 0; color: #2c3e50;"><strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                <p style="margin: 5px 0; color: #2c3e50;"><strong>Demo Code:</strong> <?php echo htmlspecialchars($_SESSION['demo_code']); ?></p>
                <a href="logout.php" style="color: #e74c3c; text-decoration: none; font-weight: 500;">Logout</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo count($records); ?></h3>
                    <p>Total Records</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo count($departments); ?></h3>
                    <p>Departments</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format(array_sum(array_column($records, 'salary'))); ?></h3>
                    <p>Total Salary Budget</p>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="search-filters">
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="search">Search Records</label>
                        <input type="text" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search by name, ID, or email">
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Filter by Department</label>
                        <select id="department" name="department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['dept_id']; ?>" 
                                        <?php echo $department == $dept['dept_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Search</button>
                        <a href="view_records.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Records Table -->
            <?php if (empty($records)): ?>
                <div class="no-records">
                    <h3>No records found</h3>
                    <p>Try adjusting your search criteria or add new records.</p>
                    <a href="index.php" class="btn">Add New Record</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Salary</th>
                                <th>Hire Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($record['employee_id']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['email']); ?></td>
                                    <td>
                                        <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <?php echo htmlspecialchars($record['dept_code']); ?>
                                        </span>
                                        <br>
                                        <small><?php echo htmlspecialchars($record['dept_name']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['position']); ?></td>
                                    <td><?php echo htmlspecialchars($record['phone']); ?></td>
                                    <td>$<?php echo number_format($record['salary']); ?></td>
                                    <td><?php echo date('M Y', strtotime($record['hire_date'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="edit_record.php?id=<?php echo $record['id']; ?>" 
                                               class="btn btn-secondary">Edit</a>
                                                                                         <button type="button" class="btn btn-danger" 
                                                     onclick="confirmDelete(<?php echo $record['id']; ?>, '<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>')">
                                                 Delete
                                             </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn">Add New Record</a>
            </div>
        </div>
    </div>
    
    <!-- Hidden form for delete -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="delete_id" id="deleteId">
    </form>
    
    <script>
        function confirmDelete(recordId, recordName) {
            if (confirm(`Are you sure you want to delete the record for "${recordName}"?\n\nThis action cannot be undone.`)) {
                document.getElementById('deleteId').value = recordId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html> 