<?php
/**
 * Database Migration Script - Leave Management System
 * This script adds leave balance tracking columns to the employees table
 * Run this once to update your database schema
 */

include 'db.php';

echo "<h2>Leave Management System - Database Migration</h2>";
echo "<p>Starting migration...</p>";

// Add new columns to employees table
$migrations = [
    "ALTER TABLE employees ADD COLUMN paid_leave_quota INT DEFAULT 12 AFTER basic_salary",
    "ALTER TABLE employees ADD COLUMN paid_leave_taken INT DEFAULT 0 AFTER paid_leave_quota",
    "ALTER TABLE employees ADD COLUMN unpaid_leave_taken INT DEFAULT 0 AFTER paid_leave_taken"
];

$success_count = 0;
$error_count = 0;

foreach ($migrations as $index => $sql) {
    echo "<p>Running migration " . ($index + 1) . "...</p>";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Migration " . ($index + 1) . " completed successfully!</p>";
        $success_count++;
    } else {
        $error = mysqli_error($conn);
        // Check if column already exists
        if (strpos($error, 'Duplicate column name') !== false) {
            echo "<p style='color: orange;'>⚠ Migration " . ($index + 1) . " skipped (column already exists)</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Migration " . ($index + 1) . " failed: " . $error . "</p>";
            $error_count++;
        }
    }
}

// Also add a column to leave_requests to track if it's paid or unpaid
$leave_type_sql = "ALTER TABLE leave_requests ADD COLUMN is_paid TINYINT(1) DEFAULT NULL AFTER status";
echo "<p>Adding leave type tracking to leave_requests table...</p>";

if (mysqli_query($conn, $leave_type_sql)) {
    echo "<p style='color: green;'>✓ Leave type column added successfully!</p>";
    $success_count++;
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column name') !== false) {
        echo "<p style='color: orange;'>⚠ Leave type column already exists</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>✗ Failed to add leave type column: " . $error . "</p>";
        $error_count++;
    }
}

echo "<hr>";
echo "<h3>Migration Summary</h3>";
echo "<p>Successful: $success_count</p>";
echo "<p>Errors: $error_count</p>";

if ($error_count == 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ All migrations completed successfully! Your database is ready.</p>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some migrations failed. Please check the errors above.</p>";
}

mysqli_close($conn);
?>
