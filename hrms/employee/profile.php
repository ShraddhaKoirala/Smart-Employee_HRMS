<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$user_id = $_SESSION['user_id'];
$employee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - HRMS</title>
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
        
        .card { 
            background: white; 
            padding: 35px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            max-width: 900px; 
            border: 1px solid #e2e8f0;
        }
        
        .card h2 { 
            margin-bottom: 25px; 
            color: #334155; 
            font-size: 20px;
            font-weight: 700;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 10px;
        }
        
        .profile-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
        }
        
        .profile-item { 
            padding-bottom: 15px; 
            border-bottom: 1px solid #f1f5f9; 
        }
        
        .profile-item label { 
            font-size: 13px; 
            color: #64748b; 
            display: block; 
            margin-bottom: 6px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .profile-item p { 
            font-size: 16px; 
            color: #1e293b; 
            font-weight: 500; 
        }
        
        .profile-header { 
            text-align: center; 
            padding: 40px; 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            border-radius: 12px; 
            color: white; 
            margin-bottom: 35px; 
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
        }
        
        .profile-avatar { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            background: white; 
            color: #2563eb; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 48px; 
            font-weight: 700; 
            margin: 0 auto 20px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-header h2, .sidebar-menu a span { display: none; }
            .main-content { margin-left: 70px; }
            .profile-grid { grid-template-columns: 1fr; }
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
            <li><a href="profile.php" class="active"> <span>My Profile</span></a></li>
            <li><a href="leave.php"> <span>Leave Management</span></a></li>
            <li><a href="salary.php"> <span>Salary Details</span></a></li>
            <li><a href="policies.php"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Send Message</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>My Profile</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="card">
            <div class="profile-header">
                <div class="profile-avatar"><?php echo strtoupper(substr($employee['full_name'], 0, 1)); ?></div>
                <h2 style="margin: 0;"><?php echo htmlspecialchars($employee['full_name']); ?></h2>
                <p><?php echo htmlspecialchars($employee['position']); ?></p>
            </div>

            <h2>Personal Information</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Employee ID</label>
                    <p>EMP-<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="profile-item">
                    <label>Email Address</label>
                    <p><?php echo htmlspecialchars($employee['email']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Phone Number</label>
                    <p><?php echo htmlspecialchars($employee['phone']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Department</label>
                    <p><?php echo htmlspecialchars($employee['department']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Position</label>
                    <p><?php echo htmlspecialchars($employee['position']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Date of Joining</label>
                    <p><?php echo date('d M Y', strtotime($employee['date_of_joining'])); ?></p>
                </div>
                <div class="profile-item">
                    <label>Employment Status</label>
                    <p><?php echo ucfirst($employee['status']); ?></p>
                </div>
                <div class="profile-item">
                    <label>Basic Salary</label>
                    <p>$<?php echo number_format($employee['basic_salary'], 2); ?></p>
                </div>
            </div>

            <?php if ($employee['address']): ?>
                <div style="margin-top: 30px;">
                    <h3 style="color: #2d3748; margin-bottom: 10px;">Address</h3>
                    <p style="color: #4a5568;"><?php echo nl2br(htmlspecialchars($employee['address'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
