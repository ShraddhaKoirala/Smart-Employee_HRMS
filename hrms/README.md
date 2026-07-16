# HR Management System (HRMS)

A complete web-based Human Resource Management System with separate portals for HR/Admin and Employees.

## Features

### HR/Admin Module
- ✅ Secure login with role-based access
- ✅ Add, edit, and manage employee profiles
- ✅ View and manage leave requests (Approve/Reject)
- ✅ Real-time leave status updates
- ✅ Manage HR policies and announcements
- ✅ Payroll management with automatic salary calculations based on leaves
- ✅ View employee messages and communications
- ✅ Dashboard with statistics and recent activities

### Employee Module
- ✅ Secure employee login
- ✅ View personal profile information
- ✅ View HR policies and company guidelines
- ✅ Apply for leave with automatic day calculation
- ✅ Track leave status (Pending/Approved/Rejected)
- ✅ View salary details and payroll history
- ✅ Send messages to HR department
- ✅ Real-time leave status updates

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Design**: Responsive design (works on desktop and mobile)

## Installation Instructions

### Step 1: Prerequisites
Make sure you have installed:
- XAMPP/WAMP/LAMP (includes Apache, MySQL, PHP)
- Web browser (Chrome, Firefox, etc.)

### Step 2: Database Setup

1. Start your Apache and MySQL servers in XAMPP/WAMP
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `hrms`
4. Import the `database.sql` file:
   - Click on the `hrms` database
   - Go to "Import" tab
   - Choose the `database.sql` file
   - Click "Go"

Alternatively, you can run the SQL commands directly:
- Open the `database.sql` file
- Copy all the SQL commands
- Paste them in the SQL tab in phpMyAdmin
- Click "Go"

### Step 3: Configure Database Connection

1. Open `db.php` file
2. Update the database credentials if needed:
```php
$host = 'localhost';
$username = 'root';        // Your MySQL username
$password = '';            // Your MySQL password
$database = 'hrms';
```

### Step 4: Deploy the Application

1. Copy all project files to your web server directory:
   - For XAMPP: `C:\xampp\htdocs\hrms\`
   - For WAMP: `C:\wamp64\www\hrms\`
   - For LAMP: `/var/www/html/hrms/`

2. Make sure all files are in the correct structure:
```
hrms/
├── admin/
│   ├── dashboard.php
│   ├── employees.php
│   ├── leave_requests.php
│   ├── policies.php
│   ├── messages.php
│   └── payroll.php
├── employee/
│   ├── dashboard.php
│   ├── profile.php
│   ├── leave.php
│   ├── salary.php
│   ├── policies.php
│   └── messages.php
├── index.php
├── db.php
├── logout.php
└── database.sql
```

### Step 5: Access the Application

1. Open your web browser
2. Go to: `http://localhost/hrms/`
3. You will see the login page

## Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: admin123

### Employee Account (Sample)
- **Username**: john
- **Password**: john123

**Important**: Change the admin password after first login!

## How to Use

### For HR/Admin:

1. **Login** with admin credentials
2. **Dashboard**: View statistics and recent activities
3. **Manage Employees**:
   - Add new employees with all details
   - Edit employee information
   - Activate/Deactivate employees
   - Delete employees
4. **Leave Requests**:
   - View all leave requests
   - Approve or reject leaves
   - Add remarks for decisions
   - Changes reflect immediately on employee dashboard
5. **HR Policies**:
   - Create new policies
   - Categorize policies
   - Delete outdated policies
6. **Messages**:
   - Read messages from employees
   - Mark messages as read
7. **Payroll**:
   - Generate monthly payroll
   - Automatic calculation: Base Salary - (Leaves × $100)
   - View all payroll records

### For Employees:

1. **Login** with employee credentials
2. **Dashboard**: View your statistics and recent activities
3. **My Profile**: View your complete profile information
4. **Leave Management**:
   - Apply for different types of leaves
   - Automatic day calculation
   - Track all your leave requests
   - See status updates in real-time
   - View admin remarks
5. **Salary Details**:
   - View your basic salary
   - Check payroll history
   - See deductions for leaves taken
6. **HR Policies**: Read all company policies and guidelines
7. **Send Message**: Communicate directly with HR department

## System Features

### Real-Time Updates
- Leave status changes are immediately visible to employees
- No page refresh needed for status updates

### Automatic Calculations
- Leave days automatically calculated from date range
- Payroll automatically deducts $100 per approved leave day
- Net salary = Basic Salary - Total Deductions

### Security Features
- Role-based access control (Admin vs Employee)
- Session management
- SQL injection prevention with mysqli_real_escape_string
- Password-protected access
- Automatic logout functionality

### Responsive Design
- Works on desktop computers
- Responsive layout for tablets
- Mobile-friendly interface

## Database Structure

### Tables:
1. **users** - Login credentials and roles
2. **employees** - Complete employee information
3. **leave_requests** - All leave applications and status
4. **policies** - HR policies and guidelines
5. **messages** - Employee-to-HR communications
6. **payroll** - Monthly salary records

## Customization

### Adding More Leave Types
Edit `employee/leave.php` and add options in the leave type dropdown:
```php
<option value="New Leave Type">New Leave Type</option>
```

### Changing Deduction Amount
Edit `admin/payroll.php` and modify:
```php
$deduction_per_leave = 100; // Change to your amount
```

### Changing Colors/Theme
All CSS is embedded in the PHP files. Search for color codes:
- Admin theme: `#667eea`, `#764ba2`
- Employee theme: `#48bb78`, `#38a169`

## Troubleshooting

### Cannot Connect to Database
- Check if MySQL is running
- Verify database credentials in `db.php`
- Make sure `hrms` database exists

### Page Not Found
- Check if files are in correct directory
- Verify Apache is running
- Check URL: `http://localhost/hrms/`

### Login Not Working
- Verify database has been imported
- Check if sample users exist in database
- Clear browser cookies and try again

### Changes Not Reflecting
- Clear browser cache
- Check if you're logged in with correct role
- Verify database connection

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all installation steps were followed
3. Check browser console for JavaScript errors
4. Verify PHP error logs

## Future Enhancements

Possible additions:
- Password encryption (using password_hash)
- Email notifications
- File upload for documents
- Advanced reporting features
- Employee performance reviews
- Attendance tracking with clock in/out
- Department-wise reports

## Security Notes

**Important for Production Use**:
1. Implement proper password hashing (currently uses plain text)
2. Use prepared statements for all queries
3. Add CSRF protection
4. Implement proper session security
5. Add SSL/HTTPS
6. Regular database backups
7. Input validation on all forms
8. Limit failed login attempts

## License

This project is created for educational purposes.

## Version

Version 1.0 - January 2026

---

**Note**: This system uses simple code (HTML, CSS, JavaScript, PHP, MySQL) as requested, making it easy to understand and modify for beginners.
