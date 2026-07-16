<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$user_id = $_SESSION['user_id'];
$employee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'"));
$emp_id = $employee['id'];

// Get payroll records
$payrolls = mysqli_query($conn, "SELECT * FROM payroll WHERE employee_id='$emp_id' ORDER BY year DESC, month DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Salary Details - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            /* Clean Office Desk Background */
            background: url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=1920') no-repeat center center fixed;
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
            background: rgba(248, 250, 252, 0.85); /* Slate-50 with transparency */
            z-index: 0;
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .sidebar { 
            position: fixed; 
            left: 0; 
            top: 0; 
            height: 100%; 
            width: 250px; 
            background: #1e293b; 
            padding: 20px; 
            color: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
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
            letter-spacing: 0.5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 8px; }
        
        .sidebar-menu a { 
            display: block; 
            padding: 12px 16px; 
            color: #cbd5e1; 
            text-decoration: none; 
            border-radius: 6px; 
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: #334155; 
            color: white;
            border-left: 3px solid #60a5fa;
        }
        
        .main-content { 
            margin-left: 250px; 
            padding: 30px;
            position: relative;
            z-index: 1;
            animation: scaleIn 0.6s ease-out;
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
            color: #1e293b; 
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
        
        .salary-card { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            color: white; 
            padding: 35px; 
            border-radius: 12px; 
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2); 
        }
        
        .salary-card h2 { 
            font-size: 16px; 
            margin-bottom: 15px; 
            opacity: 0.9; 
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .salary-amount { 
            font-size: 56px; 
            font-weight: 700; 
            letter-spacing: -1px;
        }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            border: 1px solid #e2e8f0;
        }
        
        .card h2 { 
            margin-bottom: 25px; 
            color: #334155; 
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        
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
        
        table tbody tr:hover { background: #f8fafc; }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div style="font-size: 40px;"></div>
            <h2>Employee Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"> <span>Dashboard</span></a></li>
            <li><a href="profile.php"> <span>My Profile</span></a></li>
            <li><a href="leave.php"> <span>Leave Management</span></a></li>
            <li><a href="salary.php" class="active"> <span>Salary Details</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Salary Details</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="salary-card">
            <h2>Monthly Basic Salary</h2>
            <div class="salary-amount">$<?php echo number_format($employee['basic_salary'], 2); ?></div>
            <p style="margin-top: 10px; opacity: 0.9;">This is your base salary before deductions</p>
        </div>

        <div class="card">
            <h2>Payroll History</h2>
            <?php if (mysqli_num_rows($payrolls) > 0): ?>
                <table>
                    <thead>
                        <tr>
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
                                <td><?php echo date('F', mktime(0, 0, 0, $payroll['month'], 1)) . ' ' . $payroll['year']; ?></td>
                                <td>$<?php echo number_format($payroll['basic_salary'], 2); ?></td>
                                <td><?php echo $payroll['leaves_taken']; ?> days</td>
                                <td style="color: #f56565;">-$<?php echo number_format($payroll['deductions'], 2); ?></td>
                                <td><strong style="color: #10b981;">$<?php echo number_format($payroll['net_salary'], 2); ?></strong></td>
                                <td><?php echo date('d-m-Y', strtotime($payroll['generated_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 40px;">No payroll records available yet</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Salary Information</h2>
            <p style="color: #4a5568; line-height: 1.8;">
                <strong>Deduction Policy:</strong> Your net salary is calculated based on your basic salary minus deductions for approved leaves. Each approved leave day results in a deduction of $100. Your payroll is generated monthly by the HR department and will reflect the actual leaves taken during that month.
            </p>
        </div>
    </div>
</body>
</html>
