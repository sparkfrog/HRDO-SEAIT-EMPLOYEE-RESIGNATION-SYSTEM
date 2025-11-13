# HRDO Employee Resignation System

A comprehensive web-based system for managing employee resignation records, built with PHP, MySQL, Bootstrap 5, and modern web technologies.

## 📋 Features

### 🔐 Authentication & Security
- Secure login system with password hashing (bcrypt)
- Role-based access control (Admin & HR Staff)
- Session management
- CSRF protection on all forms
- SQL injection prevention using PDO prepared statements
- Input sanitization and validation

### 👨‍💼 Admin Features
- **Dashboard**: Analytics with charts showing resignations by department and monthly trends
- **System Users Management**: Full CRUD operations for user accounts
- **Department Management**: Add, edit, and delete departments
- **Employee Records**: Complete management of resignation records
- **Audit Logs**: Track all changes with old/new value comparison
- **Profile Settings**: Update personal information and password

### 👩‍💼 HR Staff Features
- **Dashboard**: View analytics and statistics
- **Add Employee**: Record new employee resignations
- **Employee Records**: View, edit, and manage resignation records
- **Profile Settings**: Update personal credentials

### 📊 Additional Features
- Interactive charts using Chart.js
- DataTables integration for advanced table features (search, sort, pagination)
- Responsive design with Bootstrap 5
- Modal-based forms for better UX
- Real-time form validation
- Collapsible sidebar navigation
- Auto-dismissible alerts

## 🛠️ Technology Stack

- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **Charts**: Chart.js
- **Tables**: DataTables
- **Server**: Apache (XAMPP)

## 📦 Installation

### Prerequisites
- XAMPP (or any PHP/MySQL environment)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Steps

1. **Extract the project to XAMPP htdocs folder**
   ```
   C:\xampp\htdocs\HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM\
   ```

2. **Create the database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `hrdo_resign_records`
   - Import the provided SQL schema (execute the SQL dump provided)

3. **Configure database connection**
   - Open `db.php`
   - Verify the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'hrdo_resign_records');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Start XAMPP services**
   - Start Apache
   - Start MySQL

5. **Access the system**
   - Open your browser and navigate to:
     ```
     http://localhost/HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/
     ```

## 🔑 Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `password` (default hash in SQL dump)

### HR Staff Account
- **Username**: `hrstaff`
- **Password**: `password` (default hash in SQL dump)

**Note**: Please change these default passwords immediately after first login!

## 📁 Project Structure

```
HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/
├── index.php                    # Login page (main entry point)
├── db.php                       # Database connection
├── README.md                    # This file
│
├── admin/                       # Admin pages
│   ├── dashboard.php           # Admin dashboard with analytics
│   ├── SystemUsers.php         # User management
│   ├── department.php          # Department management
│   ├── addEmployee.php         # Add employee record
│   ├── recordsEmployee.php     # View/edit employee records
│   ├── auditlogs.php          # Audit logs viewer
│   └── profilesettings.php    # Admin profile settings
│
├── hrstaff/                    # HR Staff pages
│   ├── dashboard.php          # HR Staff dashboard
│   ├── addEmployee.php        # Add employee record
│   ├── recordsEmployee.php    # View/edit employee records
│   └── profilesettings.php   # HR Staff profile settings
│
├── auth/                       # Authentication
│   └── logout.php             # Logout handler
│
├── components/                 # Reusable components
│   ├── adminNavigation.php   # Admin sidebar navigation
│   └── hrstaffNavigation.php # HR Staff sidebar navigation
│
└── includes/                   # Shared includes
    ├── functions.php          # Utility functions
    └── footer.php            # Footer with scripts
```

## 🗄️ Database Schema

### Tables

1. **system_users**
   - User accounts with roles (Admin/HR Staff)
   - Encrypted passwords
   - Active status tracking
   - Last login timestamp

2. **departments**
   - Organization departments
   - Unique department names

3. **employees**
   - Resigned employee records
   - Department association
   - Separation details
   - Audit trail (created_by, updated_by)

4. **employee_audit_logs**
   - Complete change history
   - JSON storage of old/new values
   - Action type tracking (INSERT/UPDATE/DELETE)

## 🔒 Security Features

1. **Password Security**
   - Passwords hashed using `password_hash()` with bcrypt
   - Verified using `password_verify()`
   - Minimum 6 character requirement

2. **SQL Injection Prevention**
   - All queries use PDO prepared statements
   - Parameterized queries throughout

3. **XSS Protection**
   - Input sanitization using `htmlspecialchars()`
   - Output encoding for all user data

4. **CSRF Protection**
   - Token generation and verification on all forms
   - Session-based token storage

5. **Session Security**
   - Secure session management
   - Role-based access control
   - Automatic logout on session expiry

6. **Input Validation**
   - Server-side validation
   - Client-side validation
   - Type checking and sanitization

## 🎨 User Interface

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with Bootstrap 5
- **Interactive Elements**: Modals, tooltips, and animated transitions
- **Color-Coded**: Different color schemes for Admin (purple) and HR Staff (teal)
- **Collapsible Sidebar**: Space-saving navigation that can be toggled
- **Data Tables**: Advanced table features with search, sort, and pagination

## 📊 Features Breakdown

### Dashboard Analytics
- **Pie Chart**: Distribution of resignations by department
- **Bar Chart**: Monthly resignation trends
- **Statistics Cards**: Quick overview of total counts
- **Filters**: Filter by department and year
- **Recent Records**: List of 5 most recent resignations

### Employee Management
- Add new resignation records
- View detailed employee information
- Edit existing records
- Delete records (with confirmation)
- Filter by department and year
- Export capabilities (via DataTables)

### Audit Logging
- Automatic logging of all changes
- Track INSERT, UPDATE, DELETE operations
- Store old and new values in JSON format
- View complete change history
- Identify who made changes and when

### User Management (Admin Only)
- Create new user accounts
- Edit user details
- Activate/deactivate accounts
- Role assignment
- Password management
- Cannot delete own account

### Department Management (Admin Only)
- Add new departments
- Edit department names
- Delete departments (only if no employees assigned)
- View employee count per department

## 🐛 Troubleshooting

### Common Issues

1. **"Database connection failed"**
   - Verify MySQL service is running
   - Check database credentials in `db.php`
   - Ensure database `hrdo_resign_records` exists

2. **"Page not found" errors**
   - Check if Apache is running
   - Verify project is in correct folder
   - Check folder name matches URLs

3. **Login not working**
   - Ensure default users are imported from SQL dump
   - Verify password hashing is working
   - Check session configuration in PHP

4. **Charts not displaying**
   - Check internet connection (CDN resources)
   - Verify Chart.js is loading
   - Check browser console for errors

5. **DataTables not working**
   - Ensure jQuery is loaded before DataTables
   - Check for JavaScript errors in console
   - Verify CDN resources are accessible

## 🔄 System Requirements

### Minimum Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.4+
- Apache 2.4+
- 512MB RAM
- Modern web browser (Chrome, Firefox, Edge, Safari)

### Recommended Requirements
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Apache 2.4+
- 1GB+ RAM
- Latest version of modern web browsers

## 📝 Usage Guidelines

### For Administrators
1. Regularly review audit logs
2. Manage user accounts and permissions
3. Maintain department list
4. Monitor system usage
5. Export reports as needed

### For HR Staff
1. Record employee resignations accurately
2. Update records when necessary
3. Keep explanations detailed
4. Review dashboard regularly
5. Maintain data quality

## 🔐 Best Practices

1. **Security**
   - Change default passwords immediately
   - Use strong passwords (min 12 characters)
   - Log out when finished
   - Don't share credentials

2. **Data Entry**
   - Double-check information before saving
   - Provide detailed explanations
   - Use consistent naming conventions
   - Verify dates are correct

3. **System Maintenance**
   - Regular database backups
   - Monitor audit logs
   - Remove inactive users
   - Update system regularly

## 📞 Support

For issues, questions, or suggestions:
- Check the troubleshooting section
- Review audit logs for errors
- Contact system administrator

## 📄 License

This system is proprietary software for HRDO-SEAIT use only.

## 👥 Credits

Developed for HRDO-SEAIT Employee Resignation Records Management

---

**Version**: 1.0.0  
**Last Updated**: November 2025  
**Status**: Production Ready
