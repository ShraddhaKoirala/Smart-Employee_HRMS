<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../db.php';

$message = '';
// Handle Approve/Reject
if (isset($_POST['action'])) {
    $leave_id = mysqli_real_escape_string($conn, $_POST['leave_id']);
    $action = $_POST['action'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    $status = ($action == 'approve') ? 'Approved' : 'Rejected';
    
    // Get leave details
    $leave_query = mysqli_query($conn, "SELECT employee_id, total_days FROM leave_requests WHERE id='$leave_id'");
    $leave_data = mysqli_fetch_assoc($leave_query);
    
    if ($action == 'approve') {
        $is_paid = isset($_POST['is_paid']) ? (int)$_POST['is_paid'] : 0;
        
        // Update leave request with status and paid/unpaid flag
        $update = "UPDATE leave_requests SET status='$status', is_paid='$is_paid', admin_remarks='$remarks', action_date=NOW() WHERE id='$leave_id'";
        
        if (mysqli_query($conn, $update)) {
            // Update employee leave balance
            if ($is_paid == 1) {
                mysqli_query($conn, "UPDATE employees SET paid_leave_taken = paid_leave_taken + {$leave_data['total_days']} WHERE id='{$leave_data['employee_id']}'");
            } else {
                mysqli_query($conn, "UPDATE employees SET unpaid_leave_taken = unpaid_leave_taken + {$leave_data['total_days']} WHERE id='{$leave_data['employee_id']}'");
            }
            $message = "Leave request approved successfully!";
        }
    } else {
        // Reject without updating balances
        $update = "UPDATE leave_requests SET status='$status', admin_remarks='$remarks', action_date=NOW() WHERE id='$leave_id'";
        if (mysqli_query($conn, $update)) {
            $message = "Leave request rejected!";
        }
    }
}

// Get all leave requests with employee leave balance info
$query = "SELECT lr.*, e.full_name, e.department, e.paid_leave_quota, e.paid_leave_taken, e.unpaid_leave_taken 
          FROM leave_requests lr 
          JOIN employees e ON lr.employee_id = e.id 
          ORDER BY lr.applied_date DESC";
$leave_requests = mysqli_query($conn, $query);

if (!$leave_requests) {
    die("Query Failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            /* Corporate Office Hallway Background */
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
            background: rgba(248, 250, 252, 0.92);
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
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
            border: 1px solid #e2e8f0;
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

        table td {
            font-size: 13px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fef5e7;
            color: #d69e2e;
        }

        .status-approved {
            background: #c6f6d5;
            color: #276749;
        }

        .status-rejected {
            background: #fed7d7;
            color: #c53030;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            color: white;
            font-family: 'Poppins', sans-serif;
        }

        .btn-approve {
            background: #48bb78;
        }

        .btn-reject {
            background: #f56565;
        }

        .btn-view {
            background: #4299e1;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #2d3748;
        }

        .close-btn {
            font-size: 28px;
            cursor: pointer;
            color: #718096;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
            min-height: 80px;
        }

        .detail-row {
            margin-bottom: 15px;
        }

        .detail-row label {
            font-weight: 600;
            color: #2d3748;
            display: block;
            margin-bottom: 5px;
        }

        .detail-row p {
            color: #4a5568;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2,
            .sidebar-menu a span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            table {
                font-size: 11px;
            }
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
            <li><a href="leave_requests.php" class="active"> <span>Leave Requests</span></a></li>
            <li><a href="policies.php"><span>HR Policies</span></a></li>
            <li><a href="messages.php"> <span>Messages</span></a></li>
            <li><a href="payroll.php"> <span>Payroll</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Leave Requests</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Leave Balance</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($leave = mysqli_fetch_assoc($leave_requests)): ?>
                        <tr>
                            <td><?php echo $leave['id']; ?></td>
                            <td><?php echo htmlspecialchars($leave['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($leave['department']); ?></td>
                            <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($leave['start_date'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($leave['end_date'])); ?></td>
                            <td><?php echo $leave['total_days']; ?></td>
                            <td style="font-size: 11px;">
                                <?php 
                                $paid_remaining = $leave['paid_leave_quota'] - $leave['paid_leave_taken'];
                                echo "Paid: $paid_remaining/{$leave['paid_leave_quota']}<br>";
                                echo "Unpaid: {$leave['unpaid_leave_taken']}";
                                ?>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($leave['applied_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($leave['status']); ?>">
                                    <?php echo $leave['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn btn-view" 
                                        data-leave="<?php echo base64_encode(json_encode($leave)); ?>"
                                        onclick="openViewModal(this)">View</button>
                                <?php if ($leave['status'] == 'Pending'): ?>
                                    <button class="action-btn btn-approve" 
                                            data-leave="<?php echo base64_encode(json_encode($leave)); ?>"
                                            onclick="openActionModal(this, 'approve')">Approve</button>
                                    <button class="action-btn btn-reject" 
                                            data-leave="<?php echo base64_encode(json_encode($leave)); ?>"
                                            onclick="openActionModal(this, 'reject')">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Modal -->
    <div id="actionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Approve Leave Request</h2>
                <span class="close-btn" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="leave_id" id="leaveId">
                <input type="hidden" name="action" id="actionType">
                
                <div id="leaveBalanceInfo" style="background: #f7fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px; display: none;">
                    <h3 style="font-size: 14px; margin-bottom: 10px; color: #2d3748;">Employee Leave Balance</h3>
                    <p style="font-size: 13px; margin: 5px 0;" id="balanceText"></p>
                </div>
                
                <div class="form-group" id="leaveTypeSelection" style="display: none;">
                    <label>Leave Type *</label>
                    <div style="display: flex; gap: 20px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="is_paid" value="1" style="margin-right: 8px;">
                            <span>Paid Leave</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="is_paid" value="0" style="margin-right: 8px;">
                            <span>Unpaid Leave</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Remarks (optional)</label>
                    <textarea name="remarks" placeholder="Enter any remarks..."></textarea>
                </div>
                
                <button type="submit" class="action-btn" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Leave Request Details</h2>
                <span class="close-btn" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="leaveDetails"></div>
        </div>
    </div>

    <script>
        // Helper to safely parse leave data (Base64 encoded)
        function getLeaveData(element) {
            try {
                // Decode Base64 string
                var jsonStr = atob(element.getAttribute('data-leave'));
                return JSON.parse(jsonStr);
            } catch (e) {
                console.error("Failed to parse leave data", e);
                alert("Error loading leave data. Please refresh.");
                return null;
            }
        }

        function openActionModal(btn, action) {
            var leave = getLeaveData(btn);
            if (leave) showModal(leave.id, action, leave);
        }

        function openViewModal(btn) {
            var leave = getLeaveData(btn);
            if (leave) viewLeave(leave);
        }

        function showModal(leaveId, action, leave) {
            document.getElementById('leaveId').value = leaveId;
            document.getElementById('actionType').value = action;
            
            if (action == 'approve') {
                document.getElementById('modalTitle').textContent = 'Approve Leave Request';
                document.getElementById('submitBtn').className = 'action-btn btn-approve';
                document.getElementById('submitBtn').textContent = 'Approve';
                
                // Show leave balance and type selection
                document.getElementById('leaveBalanceInfo').style.display = 'block';
                document.getElementById('leaveTypeSelection').style.display = 'block';
                
                // Make radio buttons required for approval
                var radios = document.getElementsByName('is_paid');
                for(var i = 0; i < radios.length; i++) {
                    radios[i].required = true;
                }
                
                // Display balance and smart suggestion
                // Use || 0 to handle potential nulls safely
                var quota = parseInt(leave.paid_leave_quota) || 0;
                var taken = parseInt(leave.paid_leave_taken) || 0;
                var unpaidTaken = parseInt(leave.unpaid_leave_taken) || 0;
                
                var paidRemaining = quota - taken;
                var requestDays = parseInt(leave.total_days) || 0;
                
                var balanceHTML = '<strong>Paid Leave Balance:</strong> ' + paidRemaining + ' days (Quota: ' + quota + ')<br>';
                balanceHTML += '<strong>Unpaid Leave Taken:</strong> ' + unpaidTaken + ' days<br>';
                balanceHTML += '<strong>Requested Duration:</strong> ' + requestDays + ' days';
                
                // Smart suggestion logic
                var suggestionMsg = '';
                var suggestPaid = false;

                if (paidRemaining >= requestDays) {
                    suggestionMsg = '<span style="color: #276749;">✓ Sufficient paid leave balance. Suggested: <strong>Paid Leave</strong></span>';
                    suggestPaid = true;
                } else {
                    var shortfall = requestDays - paidRemaining;
                    suggestionMsg = '<span style="color: #c53030;">⚠ Insufficient paid leave (Short by ' + shortfall + ' days). Suggested: <strong>Unpaid Leave</strong> (Salary Deduction)</span>';
                    suggestPaid = false;
                }
                
                balanceHTML += '<div style="margin-top: 10px; padding: 10px; background: #fff; border-radius: 6px; border: 1px solid #e2e8f0;">' + suggestionMsg + '</div>';
                document.getElementById('balanceText').innerHTML = balanceHTML;

                // Auto-select radio button based on suggestion
                var radios = document.getElementsByName('is_paid');
                for(var i = 0; i < radios.length; i++) {
                    radios[i].required = true;
                    if (suggestPaid && radios[i].value == '1') {
                        radios[i].checked = true;
                    } else if (!suggestPaid && radios[i].value == '0') {
                        radios[i].checked = true;
                    }
                }
            } else {
                document.getElementById('modalTitle').textContent = 'Reject Leave Request';
                document.getElementById('submitBtn').className = 'action-btn btn-reject';
                document.getElementById('submitBtn').textContent = 'Reject';
                
                // Hide leave balance and type selection for reject
                document.getElementById('leaveBalanceInfo').style.display = 'none';
                document.getElementById('leaveTypeSelection').style.display = 'none';
                
                // Remove required from radio buttons for rejection
                var radios = document.getElementsByName('is_paid');
                for(var i = 0; i < radios.length; i++) {
                    radios[i].required = false;
                }
            }
            
            document.getElementById('actionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('actionModal').style.display = 'none';
        }

        function viewLeave(leave) {
            var details = `
                <div class="detail-row">
                    <label>Employee Name:</label>
                    <p>${leave.full_name}</p>
                </div>
                <div class="detail-row">
                    <label>Department:</label>
                    <p>${leave.department}</p>
                </div>
                <div class="detail-row">
                    <label>Leave Type:</label>
                    <p>${leave.leave_type}</p>
                </div>
                <div class="detail-row">
                    <label>Duration:</label>
                    <p>${leave.start_date} to ${leave.end_date} (${leave.total_days} days)</p>
                </div>
                <div class="detail-row">
                    <label>Reason:</label>
                    <p>${leave.reason}</p>
                </div>
                <div class="detail-row">
                    <label>Status:</label>
                    <p><span class="status-badge status-${leave.status.toLowerCase()}">${leave.status}</span></p>
                </div>
                ${leave.admin_remarks ? `<div class="detail-row">
                    <label>Admin Remarks:</label>
                    <p>${leave.admin_remarks}</p>
                </div>` : ''}
            `;
            
            document.getElementById('leaveDetails').innerHTML = details;
            document.getElementById('viewModal').style.display = 'block';
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.className == 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
