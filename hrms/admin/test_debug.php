<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';

echo "Testing Query...\n";
$query = "SELECT lr.*, e.full_name, e.department, e.paid_leave_quota, e.paid_leave_taken, e.unpaid_leave_taken 
          FROM leave_requests lr 
          JOIN employees e ON lr.employee_id = e.id 
          ORDER BY lr.applied_date DESC";
$leave_requests = mysqli_query($conn, $query);

if (!$leave_requests) {
    die("Query Failed: " . mysqli_error($conn));
}

echo "Rows found: " . mysqli_num_rows($leave_requests) . "\n";

while ($leave = mysqli_fetch_assoc($leave_requests)) {
    echo "Processing ID: " . $leave['id'] . "\n";
    $json = json_encode($leave);
    if ($json === false) {
        echo "JSON Encode Failed: " . json_last_error_msg() . "\n";
    } else {
        echo "JSON OK\n";
    }
}
?>
