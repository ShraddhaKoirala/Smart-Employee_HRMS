<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Query to check user credentials
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: employee/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HR Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            /* Professional Office Background */
            background: url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=1920') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Subtle Overlay for Readability */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.65); /* Slate-900 with transparency */
            backdrop-filter: blur(4px);
            z-index: 0;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            width: 100%;
            max-width: 450px;
            border-radius: 12px; /* Subtle radius */
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
            border-top: 4px solid #1e293b; /* Corporate Accent */
        }

        .logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: #1e293b; /* Slate-800 */
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(30, 41, 59, 0.3);
            transition: transform 0.3s ease;
        }

        .logo-icon:hover {
            transform: translateY(-5px);
        }

        .logo h1 {
            font-size: 28px;
            color: #1e293b; /* Slate-800 */
            margin-bottom: 8px;
            font-weight: 700;
        }

        .logo p {
            color: #64748b; /* Slate-500 */
            font-size: 14px;
            font-weight: 400;
        }

        .error-message {
            background: #fef2f2;
            color: #991b1b;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #fee2e2;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155; /* Slate-700 */
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd5e1; /* Slate-300 */
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: #f8fafc; /* Slate-50 */
        }

        .form-group input:focus {
            outline: none;
            border-color: #334155; /* Slate-700 */
            background: white;
            box-shadow: 0 0 0 3px rgba(51, 65, 85, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: #1e293b; /* Slate-800 */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            background: #0f172a; /* Slate-900 */
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
        }

        .role-info {
            margin-top: 30px;
            padding: 20px;
            background: #f1f5f9; /* Slate-100 */
            border-radius: 8px;
            font-size: 13px;
            color: #475569; /* Slate-600 */
            border: 1px solid #e2e8f0;
        }

        .role-info strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        /* Interactive feedback */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">👥</div>
            <h1>HRMS</h1>
            <p>Human Resource Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="role-info">
            <p><strong>Demo Credentials:</strong></p>
            <p> Admin: admin / admin123</p>
            <p> Employee: Riju / Riju123</p>
        </div>
    </div>
</body>
</html>
