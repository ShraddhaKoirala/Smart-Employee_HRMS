<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

// Get employee details
$user_id = $_SESSION['user_id'];
$emp_query = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$employee = mysqli_fetch_assoc($emp_query);
$emp_id = $employee['id'];

// Get statistics
$pending_leaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE employee_id='$emp_id' AND status='Pending'"))['count'];
$approved_leaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE employee_id='$emp_id' AND status='Approved'"))['count'];
$total_policies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM policies"))['count'];

// Get recent leave requests
$recent_leaves = mysqli_query($conn, "SELECT * FROM leave_requests WHERE employee_id='$emp_id' ORDER BY applied_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard - HRMS</title>
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
        
        .welcome-card { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 12px; 
            margin-bottom: 30px; 
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
        }
        
        .welcome-card h2 { font-size: 24px; margin-bottom: 5px; font-weight: 700; }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 25px; 
            margin-bottom: 30px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover { transform: translateY(-4px); }
        
        .stat-card h3 { 
            font-size: 13px; 
            color: #64748b; 
            margin-bottom: 10px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value { 
            font-size: 36px; 
            font-weight: 700; 
            color: #1e293b; 
        }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            border: 1px solid #e2e8f0;
        }
        
        .card h2 { 
            font-size: 18px; 
            margin-bottom: 20px; 
            color: #334155; 
            border-bottom: 2px solid #f1f5f9; 
            padding-bottom: 10px;
            font-weight: 700;
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
        
        .badge { 
            padding: 6px 12px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-approved { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
        
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
            <li><a href="dashboard.php" class="active"> <span>Dashboard</span></a></li>
            <li><a href="profile.php"> <span>My Profile</span></a></li>
            <li><a href="leave.php"> <span>Leave Management</span></a></li>
            <li><a href="salary.php"> <span>Salary Details</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Employee Dashboard</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($employee['full_name']); ?>! </h2>
            <p><?php echo htmlspecialchars($employee['position']); ?> | <?php echo htmlspecialchars($employee['department']); ?> Department</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pending Leave Requests</h3>
                <div class="stat-value"><?php echo $pending_leaves; ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved Leaves</h3>
                <div class="stat-value"><?php echo $approved_leaves; ?></div>
            </div>
            <div class="stat-card">
                <h3>Available Policies</h3>
                <div class="stat-value"><?php echo $total_policies; ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Recent Leave Requests</h2>
            <?php if (mysqli_num_rows($recent_leaves) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($leave = mysqli_fetch_assoc($recent_leaves)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['start_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['end_date'])); ?></td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td><span class="badge badge-<?php echo strtolower($leave['status']); ?>"><?php echo $leave['status']; ?></span></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['applied_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px;">No leave requests yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
