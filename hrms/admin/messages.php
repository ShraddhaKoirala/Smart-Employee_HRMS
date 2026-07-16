<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

// Mark message as read
if (isset($_GET['mark_read'])) {
    $msg_id = mysqli_real_escape_string($conn, $_GET['mark_read']);
    mysqli_query($conn, "UPDATE messages SET status='read' WHERE id='$msg_id'");
}

// Get all messages
$messages = mysqli_query($conn, "SELECT m.*, e.full_name, e.email, e.department FROM messages m JOIN employees e ON m.sender_id = e.id ORDER BY m.sent_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        
        .message-item { 
            padding: 25px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }
        
        .message-item:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #f8fafc;
        }
        
        .message-item.unread { 
            background: #fff7ed; 
            border-color: #ffedd5; 
            border-left: 4px solid #f97316;
        }
        
        .message-header { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .message-header h3 { 
            color: #1e293b; 
            margin-bottom: 5px; 
            font-size: 16px;
            font-weight: 600;
        }
        
        .badge-unread { 
            background: #f97316; 
            color: white; 
            padding: 4px 12px; 
            border-radius: 12px; 
            font-size: 11px; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .message-content { 
            color: #475569; 
            margin: 15px 0; 
            line-height: 1.6; 
            font-size: 14px;
        }
        
        .message-meta { 
            font-size: 12px; 
            color: #94a3b8; 
            margin-top: 15px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-mark-read { 
            padding: 4px 10px; 
            background: #dcfce7; 
            color: #166534; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600;
            border: 1px solid #bbf7d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        
        .btn-mark-read:hover {
            background: #bbf7d0;
        }
        
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
            <h2>HRMS Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"> <span>Dashboard</span></a></li>
            <li><a href="employees.php"> <span>Employees</span></a></li>
            <li><a href="leave_requests.php"> <span>Leave Requests</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php" class="active"> <span>Messages</span></a></li>
            <li><a href="payroll.php"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Employee Messages</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="card">
            <?php if (mysqli_num_rows($messages) > 0): ?>
                <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
                    <div class="message-item <?php echo $msg['status'] == 'unread' ? 'unread' : ''; ?>">
                        <div class="message-header">
                            <div>
                                <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                                <p style="font-size: 13px; color: #718096;">
                                    From: <strong><?php echo htmlspecialchars($msg['full_name']); ?></strong> 
                                    (<?php echo htmlspecialchars($msg['department']); ?>)
                                </p>
                            </div>
                            <div>
                                <?php if ($msg['status'] == 'unread'): ?>
                                    <span class="badge-unread">UNREAD</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                        <div class="message-meta">
                            Sent on: <?php echo date('d M Y, h:i A', strtotime($msg['sent_date'])); ?>
                            <?php if ($msg['status'] == 'unread'): ?>
                                | <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn-mark-read">Mark as Read</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 40px;">No messages yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
