<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$user_id = $_SESSION['user_id'];
$employee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, full_name FROM employees WHERE user_id='$user_id'"));
$emp_id = $employee['id'];

$message = '';

// Handle Send Message
if (isset($_POST['send_message'])) {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $msg_content = mysqli_real_escape_string($conn, $_POST['message']);
    
    $insert = "INSERT INTO messages (sender_id, subject, message) VALUES ('$emp_id', '$subject', '$msg_content')";
    
    if (mysqli_query($conn, $insert)) {
        $message = "Message sent successfully to HR!";
    }
}

// Get sent messages
$sent_messages = mysqli_query($conn, "SELECT * FROM messages WHERE sender_id='$emp_id' ORDER BY sent_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Message - HRMS</title>
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
        
        .message { 
            padding: 16px 20px; 
            background: #ecfdf5; 
            color: #047857; 
            border-radius: 6px; 
            margin-bottom: 25px;
            border: 1px solid #d1fae5;
            font-size: 14px;
        }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            margin-bottom: 25px;
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
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 13px; 
            color: #475569; 
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 14px; 
            font-family: 'Poppins', sans-serif; 
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6; 
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group textarea { resize: vertical; min-height: 150px; }
        
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
            background: #3b82f6; 
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb; 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
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
        
        .message-header { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .message-header h3 { 
            color: #1e293b; 
            font-size: 16px; 
            font-weight: 600;
        }
        
        .badge { 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-read { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-unread { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        
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
            <h2>Employee Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"> <span>Dashboard</span></a></li>
            <li><a href="profile.php"> <span>My Profile</span></a></li>
            <li><a href="leave.php"> <span>Leave Management</span></a></li>
            <li><a href="salary.php"> <span>Salary Details</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php" class="active"> <span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Send Message to HR</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Compose Message</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required placeholder="Enter message subject">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required placeholder="Write your message here..."></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <div class="card">
            <h2>Sent Messages</h2>
            <?php if (mysqli_num_rows($sent_messages) > 0): ?>
                <?php while ($msg = mysqli_fetch_assoc($sent_messages)): ?>
                    <div class="message-item">
                        <div class="message-header">
                            <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                            <span class="badge badge-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                        <div class="message-meta">
                            Sent on: <?php echo date('d M Y, h:i A', strtotime($msg['sent_date'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 40px;">No messages sent yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
