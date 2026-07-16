<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

// Get statistics
$total_employees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE status='active'"))['count'];
$pending_leaves = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE status='Pending'"))['count'];
$unread_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE status='unread'"))['count'];
$total_departments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT department) as count FROM employees"))['count'];

// Get recent leave requests
$recent_leaves = mysqli_query($conn, "SELECT lr.*, e.full_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id ORDER BY lr.applied_date DESC LIMIT 5");

// Get recent messages
$recent_messages = mysqli_query($conn, "SELECT m.*, e.full_name FROM messages m JOIN employees e ON m.sender_id = e.id ORDER BY m.sent_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HRMS</title>
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

        /* Darker Overlay for Admin */
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
            background: #0f172a; /* Slate-900 */
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
        
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: #1e293b; /* Slate-800 */
            color: white;
            border-left: 3px solid #8b5cf6; /* Violet-500 accent */
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
        
        .user-info { display: flex; align-items: center; gap: 20px; }
        .user-info span { color: #64748b; font-size: 14px; }
        .user-info strong { color: #334155; }
        
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
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 25px; 
            margin-bottom: 30px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 { 
            font-size: 13px; 
            color: #64748b; 
            margin-bottom: 10px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .stat-value { 
            font-size: 36px; 
            font-weight: 700; 
            margin-bottom: 5px;
            color: #0f172a;
        }
        
        .stat-card p {
            font-size: 13px;
            color: #94a3b8;
        }
        
        .content-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 25px; 
        }
        
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
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
        
        .list-item { 
            padding: 15px 0; 
            border-bottom: 1px solid #f1f5f9; 
            transition: background-color 0.2s;
        }
        
        .list-item:hover {
            background-color: #f8fafc;
            padding-left: 10px;
            padding-right: 10px;
            margin-left: -10px;
            margin-right: -10px;
            border-radius: 4px;
        }
        
        .list-item:last-child { border-bottom: none; }
        
        .list-item h4 { 
            font-size: 14px; 
            color: #334155; 
            margin-bottom: 4px; 
            font-weight: 600;
        }
        
        .list-item p { 
            font-size: 13px; 
            color: #64748b; 
        }
        
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600; 
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pending { 
            background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;
        }
        
        .badge-unread { 
            background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
            .content-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div style="font-size: 40px;"></div>
            <h2>HRMS Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"> <span>Dashboard</span></a></li>
            <li><a href="employees.php"> <span>Employees</span></a></li>
            <li><a href="leave_requests.php"> <span>Leave Requests</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Messages</span></a></li>
            <li><a href="payroll.php"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1>Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Employees</h3>
                <div class="stat-value"><?php echo $total_employees; ?></div>
                <p>Active employees</p>
            </div>
            <div class="stat-card">
                <h3>Pending Leaves</h3>
                <div class="stat-value"><?php echo $pending_leaves; ?></div>
                <p>Awaiting approval</p>
            </div>
            <div class="stat-card">
                <h3>Unread Messages</h3>
                <div class="stat-value"><?php echo $unread_messages; ?></div>
                <p>From employees</p>
            </div>
            <div class="stat-card">
                <h3>Departments</h3>
                <div class="stat-value"><?php echo $total_departments; ?></div>
                <p>Active departments</p>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <div class="card">
                <h2>Recent Leave Requests</h2>
                <?php if (mysqli_num_rows($recent_leaves) > 0): ?>
                    <?php while ($leave = mysqli_fetch_assoc($recent_leaves)): ?>
                        <div class="list-item">
                            <h4><?php echo htmlspecialchars($leave['full_name']); ?></h4>
                            <p><?php echo $leave['leave_type']; ?> - <?php echo $leave['total_days']; ?> days</p>
                            <span class="badge badge-pending"><?php echo $leave['status']; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #718096; text-align: center; padding: 20px;">No leave requests</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Recent Messages</h2>
                <?php if (mysqli_num_rows($recent_messages) > 0): ?>
                    <?php while ($message = mysqli_fetch_assoc($recent_messages)): ?>
                        <div class="list-item">
                            <h4><?php echo htmlspecialchars($message['full_name']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($message['subject'], 0, 50)); ?>...</p>
                            <?php if ($message['status'] == 'unread'): ?>
                                <span class="badge badge-unread">Unread</span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #718096; text-align: center; padding: 20px;">No messages</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
