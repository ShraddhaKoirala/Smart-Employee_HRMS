<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$message = '';

// Handle Add Policy
if (isset($_POST['add_policy'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $created_by = $_SESSION['user_id'];
    
    $insert = "INSERT INTO policies (title, content, category, created_by) VALUES ('$title', '$content', '$category', '$created_by')";
    
    if (mysqli_query($conn, $insert)) {
        $message = "Policy added successfully!";
    }
}

// Handle Delete Policy
if (isset($_GET['delete'])) {
    $policy_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM policies WHERE id='$policy_id'");
    $message = "Policy deleted successfully!";
}

// Get all policies
$policies = mysqli_query($conn, "SELECT * FROM policies ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Policies - HRMS</title>
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
        
        .form-group { margin-bottom: 20px; }
        
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
            border-color: #8b5cf6; 
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }
        
        .form-group textarea { 
            resize: vertical; 
            min-height: 120px; 
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
            background: #8b5cf6; 
            color: white;
        }
        
        .btn-primary:hover {
            background: #7c3aed; 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }
        
        .policy-item { 
            padding: 20px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            margin-bottom: 20px;
            transition: box-shadow 0.2s ease;
        }
        
        .policy-item:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #f8fafc;
        }
        
        .policy-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: start; 
            margin-bottom: 12px; 
        }
        
        .policy-header h3 { 
            color: #1e293b; 
            margin-bottom: 5px; 
            font-size: 16px;
            font-weight: 600;
        }
        
        .policy-category { 
            background: #e0e7ff; 
            color: #4338ca; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .policy-content { 
            color: #475569; 
            line-height: 1.6; 
            margin-bottom: 15px; 
            font-size: 14px;
        }
        
        .policy-meta { 
            font-size: 12px; 
            color: #94a3b8; 
            margin-bottom: 0; 
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
        
        .btn-delete { 
            padding: 6px 12px; 
            background: #fee2e2; 
            color: #b91c1c; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 12px; 
            border: 1px solid #fecaca;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .btn-delete:hover {
            background: #fecaca;
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
            <li><a href="policies.php" class="active"> <span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Messages</span></a></li>
            <li><a href="payroll.php"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>HR Policies</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Add New Policy</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Policy Title *</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Leave">Leave</option>
                        <option value="Code of Conduct">Code of Conduct</option>
                        <option value="Work Hours">Work Hours</option>
                        <option value="Benefits">Benefits</option>
                        <option value="Safety">Safety</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Policy Content *</label>
                    <textarea name="content" required placeholder="Enter policy details..."></textarea>
                </div>
                
                <button type="submit" name="add_policy" class="btn btn-primary">Add Policy</button>
            </form>
        </div>

        <div class="card">
            <h2>All Policies</h2>
            <?php if (mysqli_num_rows($policies) > 0): ?>
                <?php while ($policy = mysqli_fetch_assoc($policies)): ?>
                    <div class="policy-item">
                        <div class="policy-header">
                            <div>
                                <h3><?php echo htmlspecialchars($policy['title']); ?></h3>
                                <span class="policy-category"><?php echo htmlspecialchars($policy['category']); ?></span>
                            </div>
                            <a href="?delete=<?php echo $policy['id']; ?>" class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this policy?')">
                                Delete
                            </a>
                        </div>
                        <div class="policy-content">
                            <?php echo nl2br(htmlspecialchars($policy['content'])); ?>
                        </div>
                        <div class="policy-meta">
                            Created on: <?php echo date('d M Y', strtotime($policy['created_at'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px;">No policies found</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
