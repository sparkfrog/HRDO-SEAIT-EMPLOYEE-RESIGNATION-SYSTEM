# Installation Guide
## HRDO Employee Resignation System

This guide will walk you through the complete installation process.

---

## 📋 Prerequisites

Before you begin, ensure you have:

- ✅ XAMPP installed (or similar PHP/MySQL environment)
- ✅ PHP 8.0 or higher
- ✅ MySQL 5.7 or MariaDB 10.4 or higher
- ✅ Modern web browser (Chrome, Firefox, Edge, Safari)
- ✅ Text editor (for configuration, if needed)

---

## 🚀 Step-by-Step Installation

### Step 1: Download & Extract

1. Extract the project folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM\
   ```

2. Verify all files are present:
   - `index.php` (login page)
   - `db.php` (database connection)
   - `database.sql` (database schema)
   - `admin/` folder
   - `hrstaff/` folder
   - `components/` folder
   - `includes/` folder
   - `auth/` folder

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Verify both services show "Running" status (green)

### Step 3: Create Database

**Option A: Using phpMyAdmin (Recommended)**

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar
3. Enter database name: `hrdo_resign_records`
4. Select collation: `utf8mb4_general_ci`
5. Click **"Create"**

**Option B: Using MySQL Command Line**

```sql
CREATE DATABASE hrdo_resign_records CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### Step 4: Import Database Schema

**Using phpMyAdmin:**

1. In phpMyAdmin, click on the `hrdo_resign_records` database
2. Click the **"Import"** tab at the top
3. Click **"Choose File"** and select `database.sql` from the project folder
4. Scroll down and click **"Go"**
5. Wait for success message: "Import has been successfully finished"

**Using MySQL Command Line:**

```bash
mysql -u root -p hrdo_resign_records < database.sql
```

### Step 5: Verify Database Tables

After import, you should see these 4 tables:
- ✅ `departments`
- ✅ `employees`
- ✅ `employee_audit_logs`
- ✅ `system_users`

Check that `system_users` has 2 default accounts.

### Step 6: Configure Database Connection

1. Open `db.php` in a text editor
2. Verify these settings (default XAMPP configuration):

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hrdo_resign_records');
define('DB_USER', 'root');
define('DB_PASS', '');
```

3. If your MySQL has a password, update `DB_PASS`
4. Save the file

### Step 7: Set File Permissions (Important)

Ensure the following files/folders have proper permissions:

- `db.php` - Read only
- `includes/` - Read only
- `.htaccess` - Read only

**For Windows (XAMPP):** Usually no action needed

**For Linux/Mac:**
```bash
chmod 644 db.php
chmod 644 .htaccess
chmod -R 755 admin/ hrstaff/ components/ includes/ auth/
```

### Step 8: Access the System

1. Open your web browser
2. Navigate to: `http://localhost/HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/`
3. You should see the login page

### Step 9: First Login

**Admin Account:**
- Username: `admin`
- Password: `password`

**HR Staff Account:**
- Username: `hrstaff`
- Password: `password`

⚠️ **IMPORTANT**: Change these passwords immediately after first login!

### Step 10: Initial Setup (Admin Only)

1. Login as admin
2. Go to **Profile Settings** and change your password
3. Go to **Departments** and add your organization's departments
4. Go to **System Users** and:
   - Update the HR Staff account details
   - Add additional users as needed
   - Change default passwords

---

## ✅ Verification Checklist

After installation, verify:

- [ ] Login page loads without errors
- [ ] Can login with admin account
- [ ] Can login with HR staff account
- [ ] Dashboard displays correctly
- [ ] Charts load on dashboard
- [ ] Can add a department
- [ ] Can add an employee record
- [ ] DataTables work (search, sort, pagination)
- [ ] Can update profile settings
- [ ] Logout works correctly

---

## 🔧 Troubleshooting

### Problem: "Database connection failed"

**Solution:**
1. Verify MySQL service is running in XAMPP
2. Check database name is exactly: `hrdo_resign_records`
3. Verify credentials in `db.php`
4. Try restarting MySQL service

### Problem: "Access denied for user 'root'@'localhost'"

**Solution:**
1. Your MySQL has a password set
2. Update `DB_PASS` in `db.php` with your MySQL root password
3. Or use a different MySQL user

### Problem: "Table 'hrdo_resign_records.system_users' doesn't exist"

**Solution:**
1. Database was created but tables weren't imported
2. Re-import `database.sql` in phpMyAdmin
3. Verify all 4 tables exist

### Problem: "Page not found" or 404 errors

**Solution:**
1. Verify project folder name is exactly: `HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM`
2. Check Apache is running
3. Try accessing: `http://localhost/`
4. Clear browser cache

### Problem: Charts not displaying

**Solution:**
1. Check internet connection (Chart.js loads from CDN)
2. Open browser console (F12) and check for errors
3. Try a different browser
4. Disable browser extensions temporarily

### Problem: Login shows blank page

**Solution:**
1. Enable error reporting temporarily in `db.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check what error appears
3. Verify PHP session is working
4. Check Apache error logs

### Problem: "Permission denied" errors

**Solution:**
1. Run XAMPP as Administrator (Windows)
2. Check file permissions on Linux/Mac
3. Ensure Apache has read access to project folder

---

## 🔒 Security Recommendations

After installation:

1. **Change all default passwords immediately**
2. **Update database credentials** if using default root account
3. **Keep sensitive files protected** (db.php, .env)
4. **Regular backups** of database
5. **Monitor audit logs** for suspicious activity
6. **Use strong passwords** (min 12 characters, mixed case, numbers, symbols)
7. **Disable error display** in production:
   ```php
   // In db.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

---

## 📊 Post-Installation Tasks

### For Administrators:

1. **Add Departments**
   - Navigate to Departments page
   - Add all your organization's departments

2. **Create User Accounts**
   - Go to System Users
   - Create accounts for HR staff
   - Assign appropriate roles

3. **Configure System**
   - Review all settings
   - Test all features
   - Set up backup schedule

4. **Train Users**
   - Show HR staff how to add records
   - Explain the dashboard
   - Demonstrate search and filters

### For HR Staff:

1. **Familiarize with Interface**
   - Explore dashboard
   - Practice adding test records
   - Learn to use filters

2. **Update Profile**
   - Change default password
   - Update contact information

---

## 📱 Browser Compatibility

Tested and working on:
- ✅ Google Chrome (recommended)
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari
- ✅ Opera

**Note**: Internet Explorer is not supported.

---

## 🆘 Getting Help

If you encounter issues not covered in this guide:

1. Check the `README.md` file
2. Review PHP error logs: `C:\xampp\apache\logs\error.log`
3. Check MySQL error logs: `C:\xampp\mysql\data\*.err`
4. Verify system requirements are met
5. Contact system administrator

---

## ✨ You're All Set!

Your HRDO Employee Resignation System is now installed and ready to use.

**Next Steps:**
1. Login and change default passwords
2. Add departments
3. Create user accounts
4. Start recording employee data

**Enjoy using the system!** 🎉

---

## 📞 Support Information

**System Version:** 1.0.0  
**Last Updated:** November 2025  
**Developed For:** HRDO-SEAIT 
**Developed By:** Lemuel Bantillo And Axl John Mijares 


For technical support, contact your system administrator.
