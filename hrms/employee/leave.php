<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$user_id = $_SESSION['user_id'];
$employee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, paid_leave_quota, paid_leave_taken, unpaid_leave_taken FROM employees WHERE user_id='$user_id'"));
$emp_id = $employee['id'];

$message = '';

// Handle Leave Application
if (isset($_POST['apply_leave'])) {
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    // Calculate total days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;
    
    $insert = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason) 
               VALUES ('$emp_id', '$leave_type', '$start_date', '$end_date', '$total_days', '$reason')";
    
    if (mysqli_query($conn, $insert)) {
        $message = "Leave request submitted successfully!";
    }
}

// Get leave requests
$leave_requests = mysqli_query($conn, "SELECT * FROM leave_requests WHERE employee_id='$emp_id' ORDER BY applied_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Leave Management - HRMS</title>
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

        /* White Overlay */
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
        
        .sidebar { 
            position: fixed; 
            left: 0; 
            top: 0; 
            height: 100%; 
            width: 250px; 
            background: #1e293b; /* Slate-800 */
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
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .sidebar-menu { 
            list-style: none; 
        }
        
        .sidebar-menu li { 
            margin-bottom: 8px;
        }
        
        .sidebar-menu a { 
            display: block; 
            padding: 12px 16px; 
            color: #cbd5e1; /* Slate-300 */
            text-decoration: none; 
            border-radius: 6px; 
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: #334155; /* Slate-700 */
            color: white;
            border-left: 3px solid #60a5fa; /* Blue-400 accent */
        }
        
        .main-content { 
            margin-left: 250px; 
            padding: 30px;
            position: relative;
            z-index: 1;
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
            color: #ef4444; /* Red-500 */
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
            background: #ecfdf5; /* Emerald-50 */
            color: #047857; /* Emerald-700 */
            border-radius: 6px; 
            margin-bottom: 25px;
            border: 1px solid #d1fae5;
            font-size: 14px;
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
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 13px; 
            color: #475569; 
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 10px 14px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 14px; 
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6; /* Blue-500 */
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary { 
            background: #3b82f6; /* Blue-500 */
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb; /* Blue-600 */
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
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
        
        .badge { 
            padding: 6px 12px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600; 
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-pending { 
            background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;
        }
        
        .badge-approved { 
            background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7;
        }
        
        .badge-rejected { 
            background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2;
        }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 25px; 
            margin-bottom: 30px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            position: relative; 
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.paid { border-top: 4px solid #10b981; } /* Emerald */
        .stat-card.unpaid { border-top: 4px solid #f43f5e; } /* Rose */
        
        .stat-card h3 { 
            font-size: 13px; 
            color: #64748b; 
            margin-bottom: 10px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .number { 
            font-size: 36px; 
            font-weight: 700; 
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .stat-card .label { 
            font-size: 13px; 
            color: #94a3b8;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
            .form-grid { grid-template-columns: 1fr; }
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
            <li><a href="leave.php" class="active"> <span>Leave Management</span></a></li>
            <li><a href="salary.php"> <span>Salary Details</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"><span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Leave Management</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Leave Balance Stats -->
        <div class="stats-grid">
            <div class="stat-card paid">
                <h3>Paid Leave Remaining</h3>
                <div class="number">
                    <?php 
                    $paid_remaining = $employee['paid_leave_quota'] - $employee['paid_leave_taken'];
                    echo $paid_remaining;
                    ?>
                </div>
                <div class="label">Out of <?php echo $employee['paid_leave_quota']; ?> days allocated</div>
            </div>
            
            <div class="stat-card unpaid">
                <h3>Unpaid Leave Taken</h3>
                <div class="number"><?php echo $employee['unpaid_leave_taken']; ?></div>
                <div class="label">Total unpaid days used</div>
            </div>
        </div>

        <div class="card">
            <h2>Apply for Leave</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Leave Type *</label>
                        <select name="leave_type" required>
                            <option value="">Select Type</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Annual Leave">Annual Leave</option>
                            <option value="Maternity Leave">Maternity Leave</option>
                            <option value="Paternity Leave">Paternity Leave</option>
                            <option value="Emergency Leave">Emergency Leave</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Reason *</label>
                    <textarea name="reason" required placeholder="Please explain the reason for your leave..."></textarea>
                </div>
                <button type="submit" name="apply_leave" class="btn btn-primary">Submit Leave Request</button>
            </form>
        </div>

        <div class="card">
            <h2>My Leave Requests</h2>
            <?php if (mysqli_num_rows($leave_requests) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($leave = mysqli_fetch_assoc($leave_requests)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['start_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['end_date'])); ?></td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td><span class="badge badge-<?php echo strtolower($leave['status']); ?>"><?php echo $leave['status']; ?></span></td>
                                <td><?php echo date('d-m-Y', strtotime($leave['applied_date'])); ?></td>
                                <td><?php echo $leave['admin_remarks'] ? htmlspecialchars($leave['admin_remarks']) : '-'; ?></td>
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
