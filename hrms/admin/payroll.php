<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$message = '';

// Handle Generate Payroll
if (isset($_POST['generate_payroll'])) {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $month = mysqli_real_escape_string($conn, $_POST['month']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    
    // Get employee salary
    $emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT basic_salary FROM employees WHERE id='$employee_id'"));
    $basic_salary = $emp['basic_salary'];
    
    // Calculate total unpaid leave days for the month
    $leave_query = mysqli_query($conn, 
        "SELECT SUM(total_days) as leaves FROM leave_requests 
         WHERE employee_id='$employee_id' AND status='Approved' 
         AND is_paid = 0
         AND MONTH(start_date)='$month' AND YEAR(start_date)='$year'");
    
    $leave_data = mysqli_fetch_assoc($leave_query);
    $leave_count = $leave_data['leaves'] ? $leave_data['leaves'] : 0;
    
    // Calculate deductions (assume $100 per unpaid leave day)
    $deduction_per_leave = 100;
    $total_deductions = $leave_count * $deduction_per_leave;
    $net_salary = $basic_salary - $total_deductions;
    
    // Check if payroll already exists
    $check = mysqli_query($conn, "SELECT * FROM payroll WHERE employee_id='$employee_id' AND month='$month' AND year='$year'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = "Payroll for this month already generated!";
    } else {
        $insert = "INSERT INTO payroll (employee_id, month, year, basic_salary, leaves_taken, deductions, net_salary) 
                   VALUES ('$employee_id', '$month', '$year', '$basic_salary', '$leave_count', '$total_deductions', '$net_salary')";
        
        if (mysqli_query($conn, $insert)) {
            $message = "Payroll generated successfully!";
        }
    }
}

// Get all payroll records
$payrolls = mysqli_query($conn, "SELECT p.*, e.full_name, e.department FROM payroll p JOIN employees e ON p.employee_id = e.id ORDER BY p.generated_date DESC");

// Get all employees for dropdown
$employees = mysqli_query($conn, "SELECT id, full_name FROM employees WHERE status='active'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payroll - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            /* Executive Meeting Room Background */
            background: url('https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&q=80&w=1920') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 250, 252, 0.90);
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
            background: #0f172a; 
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
            color: #94a3b8; 
            text-decoration: none; 
            border-radius: 6px; 
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: #1e293b; 
            color: white;
            border-left: 3px solid #8b5cf6;
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
            color: #0f172a; 
            font-weight: 700;
        }
        
        .logout-btn { 
            padding: 8px 16px; 
            background: white;
            color: #ef4444; 
            text-decoration: none; 
            border-radius: 6px; 
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #ef4444;
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
            background: #dcfce7; 
            color: #166534; 
            border: 1px solid #bbf7d0;
        }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
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
            grid-template-columns: 1fr 1fr 1fr 1fr; 
            gap: 20px;
            align-items: end; /* Align button with inputs */
        }
        
        .form-group { margin-bottom: 0; } /* Reset margin inside grid */
        
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 13px; 
            color: #475569; 
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 10px 14px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 14px; 
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            background: #f8fafc;
            height: 42px; /* Fixed height for alignment */
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #8b5cf6; 
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        
        .btn { 
            padding: 0 24px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600; 
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            height: 42px; /* Match input height */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary { 
            background: #8b5cf6; 
            color: white;
        }
        
        .btn-primary:hover {
            background: #7c3aed; 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }
        
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
        }
        
        table th, table td { 
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
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-group { margin-bottom: 15px; } /* Restore margin for mobile */
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
            <li><a href="dashboard.php"><span>Dashboard</span></a></li>
            <li><a href="employees.php"> <span>Employees</span></a></li>
            <li><a href="leave_requests.php"> <span>Leave Requests</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Messages</span></a></li>
            <li><a href="payroll.php" class="active"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Payroll Management</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Generate Payroll</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee *</label>
                        <select name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                                <option value="<?php echo $emp['id']; ?>"><?php echo htmlspecialchars($emp['full_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Month *</label>
                        <select name="month" required>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo date('F', mktime(0, 0, 0, $i, 1)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year *</label>
                        <select name="year" required>
                            <?php for ($y = 2024; $y <= 2026; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="generate_payroll" class="btn btn-primary">Generate</button>
                    </div>
                </div>
                <p style="font-size: 12px; color: #718096; margin-top: 10px;">
                    Note: Deduction of $100 per approved leave day will be applied.
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Payroll Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Month/Year</th>
                        <th>Basic Salary</th>
                        <th>Leaves Taken</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                        <th>Generated On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payroll = mysqli_fetch_assoc($payrolls)): ?>
                        <tr>
                            <td><?php echo $payroll['id']; ?></td>
                            <td><?php echo htmlspecialchars($payroll['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($payroll['department']); ?></td>
                            <td><?php echo date('F', mktime(0, 0, 0, $payroll['month'], 1)) . ' ' . $payroll['year']; ?></td>
                            <td>$<?php echo number_format($payroll['basic_salary'], 2); ?></td>
                            <td><?php echo $payroll['leaves_taken']; ?></td>
                            <td>$<?php echo number_format($payroll['deductions'], 2); ?></td>
                            <td><strong>$<?php echo number_format($payroll['net_salary'], 2); ?></strong></td>
                            <td><?php echo date('d-m-Y', strtotime($payroll['generated_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
