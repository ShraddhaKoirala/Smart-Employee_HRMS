# QUICK START GUIDE - HR Management System

## 🚀 Quick Setup (5 Minutes)

### Step 1: Install XAMPP
Download and install XAMPP from: https://www.apachefriends.org/

### Step 2: Start Services
- Open XAMPP Control Panel
- Start "Apache" and "MySQL"

### Step 3: Setup Database
1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" to create database
3. Name it: `hrms`
4. Click "Import" tab
5. Choose file: `database.sql`
6. Click "Go"

### Step 4: Copy Files
Copy ALL project files to:
- Windows: `C:\xampp\htdocs\hrms\`
- Mac/Linux: `/Applications/XAMPP/htdocs/hrms/`

### Step 5: Access System
Open browser and go to: http://localhost/hrms/

## 🔐 Login Credentials

**Admin/HR Portal:**
- Username: `admin`
- Password: `admin123`

**Employee Portal:**
- Username: `john`
- Password: `john123`

## 📁 File Structure

Make sure your files are organized like this:
```
htdocs/hrms/
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
├── index.php (login page)
├── db.php
├── logout.php
└── database.sql
```

## ✅ What Works?

### Admin Can:
- ✅ Add/Edit/Delete employees
- ✅ Approve/Reject leave requests
- ✅ Create HR policies
- ✅ Generate payroll
- ✅ View employee messages

### Employee Can:
- ✅ View their profile
- ✅ Apply for leave
- ✅ Track leave status (REAL-TIME)
- ✅ View salary & payroll
- ✅ Read HR policies
- ✅ Send messages to HR

## 🆘 Troubleshooting

**Problem: Can't access localhost**
- Solution: Make sure Apache is running in XAMPP

**Problem: Database connection error**
- Solution: Make sure MySQL is running
- Check `db.php` has correct credentials

**Problem: Page not found**
- Solution: Make sure files are in `htdocs/hrms/` folder
- Try: `http://localhost/hrms/index.php`

**Problem: Login not working**
- Solution: Make sure database was imported successfully
- Check if `users` table has data

## 🎯 Quick Test

1. Login as admin (admin/admin123)
2. Go to "Employees" and add a new employee
3. Logout
4. Login as that employee
5. Apply for leave
6. Logout
7. Login as admin again
8. Go to "Leave Requests"
9. Approve the leave
10. Logout and login as employee again
11. See the leave status changed to "Approved" ✅

## 📞 Need Help?

Check the detailed README.md file for:
- Complete feature list
- Detailed installation steps
- Database structure
- Customization guide
- Security notes

## ⚠️ Important Notes

1. **Change default passwords** after first login!
2. This uses **simple code** (no frameworks) for easy learning
3. For production, add **password encryption** (see README.md)
4. Database credentials in `db.php`: username=root, password=(empty)

## 💡 Tips

- The system works on desktop and mobile
- Leave status updates in REAL-TIME
- Payroll deducts $100 per leave day
- All dates use format: DD-MM-YYYY
- Green theme = Employee portal
- Purple theme = Admin portal

---

**That's it! You're ready to use the HR Management System!** 🎉

For detailed information, see README.md
