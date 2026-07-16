<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$message = '';
$error = '';

// Handle Add Employee
if (isset($_POST['add_employee'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $date_of_joining = mysqli_real_escape_string($conn, $_POST['date_of_joining']);
    $basic_salary = mysqli_real_escape_string($conn, $_POST['basic_salary']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if username already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already exists!";
    } else {
        // Insert into users table
        $insert_user = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'employee')";
        if (mysqli_query($conn, $insert_user)) {
            $user_id = mysqli_insert_id($conn);
            
            // Insert into employees table
            $insert_employee = "INSERT INTO employees (user_id, full_name, email, phone, department, position, date_of_joining, basic_salary, address) 
                               VALUES ('$user_id', '$full_name', '$email', '$phone', '$department', '$position', '$date_of_joining', '$basic_salary', '$address')";
            
            if (mysqli_query($conn, $insert_employee)) {
                $message = "Employee added successfully!";
            }
        }
    }
}

// Handle Delete Employee
if (isset($_GET['delete'])) {
    $emp_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $get_user_id = mysqli_query($conn, "SELECT user_id FROM employees WHERE id='$emp_id'");
    $user_row = mysqli_fetch_assoc($get_user_id);
    
    if ($user_row) {
        mysqli_query($conn, "DELETE FROM users WHERE id='{$user_row['user_id']}'");
        $message = "Employee deleted successfully!";
    }
}

// Handle Update Status
if (isset($_GET['toggle_status'])) {
    $emp_id = mysqli_real_escape_string($conn, $_GET['toggle_status']);
    $emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM employees WHERE id='$emp_id'"));
    $new_status = ($emp['status'] == 'active') ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE employees SET status='$new_status' WHERE id='$emp_id'");
    $message = "Employee status updated!";
}

// Handle Update Employee Details
if (isset($_POST['update_employee'])) {
    $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $basic_salary = mysqli_real_escape_string($conn, $_POST['basic_salary']);
    
    $update = "UPDATE employees SET department='$department', position='$position', basic_salary='$basic_salary' WHERE id='$emp_id'";
    
    if (mysqli_query($conn, $update)) {
        $message = "Employee details updated successfully!";
    } else {
        $error = "Failed to update employee details!";
    }
}

// Get all employees
$employees = mysqli_query($conn, "SELECT * FROM employees ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            /* Executive Boardroom Background */
            background: url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=1920') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        /* Darker Overlay for Admin - Executive Feel */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 250, 252, 0.90); /* Slate-50 with high opacity */
            z-index: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 250px;
            background: #0f172a; /* Slate-900 (Darker for Admin) */
            padding: 20px;
            color: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-top: 10px;
            letter-spacing: 1px;
            font-weight: 600;
            text-transform: uppercase;
            color: #f1f5f9;
        }

        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 8px; }

        .sidebar-menu a {
            display: block;
            padding: 12px 16px;
            color: #94a3b8; /* Slate-400 */
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #1e293b; /* Slate-800 */
            color: white;
            border-left: 3px solid #8b5cf6; /* Violet-500 accent for Admin */
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.6s ease-out;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .top-bar h1 {
            font-size: 24px;
            color: #0f172a; /* Slate-900 */
            font-weight: 700;
        }

        .logout-btn {
            padding: 10px 20px;
            background: white;
            color: #ef4444;
            border: 1px solid #ef4444;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #ef4444;
            color: white;
        }

        .message {
            padding: 16px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }

        .message.success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #dcfce7;
        }

        .message.error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .card h2 {
            margin-bottom: 25px;
            color: #334155;
            font-weight: 700;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6; /* Violet-500 */
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: #8b5cf6; /* Violet-500 */
            color: white;
        }

        .btn-primary:hover {
            background: #7c3aed; /* Violet-600 */
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        table th,
        table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
        }

        table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        table tbody tr:hover {
            background: #f8fafc;
        }

        .action-btn {
            padding: 6px 12px;
            margin: 0 3px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-edit {
            background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;
        }
        .btn-edit:hover { background: #bae6fd; }

        .btn-delete {
            background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
        }
        .btn-delete:hover { background: #fecaca; }

        .btn-toggle {
            background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
        }
        .btn-toggle:hover { background: #bbf7d0; }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7;
        }

        .status-inactive {
            background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
            .form-grid { grid-template-columns: 1fr; }
            table { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div style="font-size: 40px;"></div>
            <h2>HRMS Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"> <span>Dashboard</span></a></li>
            <li><a href="employees.php" class="active"> <span>Employees</span></a></li>
            <li><a href="leave_requests.php"><span>Leave Requests</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Messages</span></a></li>
            <li><a href="payroll.php"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Employees</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Add New Employee</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" name="department" required>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <input type="text" name="position" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Joining *</label>
                        <input type="date" name="date_of_joining" required>
                    </div>
                    <div class="form-group">
                        <label>Basic Salary *</label>
                        <input type="number" step="0.01" name="basic_salary" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"></textarea>
                </div>
                <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
            </form>
        </div>

        <div class="card">
            <h2>All Employees</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                        <tr>
                            <td><?php echo $emp['id']; ?></td>
                            <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                            <td><?php echo htmlspecialchars($emp['department']); ?></td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td>$<?php echo number_format($emp['basic_salary'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $emp['status']; ?>">
                                    <?php echo ucfirst($emp['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($emp)); ?>)">
                                    Edit
                                </button>
                                <a href="?toggle_status=<?php echo $emp['id']; ?>" class="action-btn btn-toggle">
                                    Toggle
                                </a>
                                <a href="?delete=<?php echo $emp['id']; ?>" class="action-btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this employee?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #2d3748;">Edit Employee Details</h2>
                <span class="close-btn" onclick="closeEditModal()" style="font-size: 28px; cursor: pointer; color: #718096;">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="emp_id" id="edit_emp_id">
                
                <div class="form-group">
                    <label>Employee Name</label>
                    <input type="text" id="edit_name" readonly style="background: #f7fafc; cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" id="edit_department" required>
                </div>
                
                <div class="form-group">
                    <label>Position *</label>
                    <input type="text" name="position" id="edit_position" required>
                </div>
                
                <div class="form-group">
                    <label>Basic Salary *</label>
                    <input type="number" step="0.01" name="basic_salary" id="edit_salary" required>
                </div>
                
                <button type="submit" name="update_employee" class="btn btn-primary">Update Employee</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(emp) {
            document.getElementById('edit_emp_id').value = emp.id;
            document.getElementById('edit_name').value = emp.full_name;
            document.getElementById('edit_department').value = emp.department;
            document.getElementById('edit_position').value = emp.position;
            document.getElementById('edit_salary').value = emp.basic_salary;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.id == 'editModal') {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
