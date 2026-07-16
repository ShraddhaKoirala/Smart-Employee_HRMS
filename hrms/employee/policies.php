<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

// Get all policies
$policies = mysqli_query($conn, "SELECT * FROM policies ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>HR Policies - HRMS</title>
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
            overflow-y: auto;
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
        
        .policy-item { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        
        .policy-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .policy-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: start; 
            margin-bottom: 15px; 
        }
        
        .policy-header h3 { 
            color: #1e293b; 
            font-size: 18px; 
            font-weight: 700;
        }
        
        .policy-category { 
            background: #e0e7ff; 
            color: #4338ca; 
            padding: 6px 12px; 
            border-radius: 6px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .policy-content { 
            color: #475569; 
            line-height: 1.8; 
            margin-bottom: 15px; 
            font-size: 14px;
        }
        
        .policy-meta { 
            font-size: 12px; 
            color: #94a3b8; 
            border-top: 1px solid #f1f5f9; 
            padding-top: 15px; 
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
            <li><a href="policies.php" class="active"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>HR Policies & Guidelines</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if (mysqli_num_rows($policies) > 0): ?>
            <?php while ($policy = mysqli_fetch_assoc($policies)): ?>
                <div class="policy-item">
                    <div class="policy-header">
                        <h3><?php echo htmlspecialchars($policy['title']); ?></h3>
                        <span class="policy-category"><?php echo htmlspecialchars($policy['category']); ?></span>
                    </div>
                    <div class="policy-content">
                        <?php echo nl2br(htmlspecialchars($policy['content'])); ?>
                    </div>
                    <div class="policy-meta">
                        Published on: <?php echo date('d M Y', strtotime($policy['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background: white; padding: 60px; text-align: center; border-radius: 10px;">
                <p style="font-size: 18px; color: #718096;">No policies available at the moment</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
