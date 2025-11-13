# User Guide
## HRDO Employee Resignation System

A comprehensive guide for using the system effectively.

---

## 📖 Table of Contents

1. [Getting Started](#getting-started)
2. [Admin User Guide](#admin-user-guide)
3. [HR Staff User Guide](#hr-staff-user-guide)
4. [Common Tasks](#common-tasks)
5. [Tips & Best Practices](#tips--best-practices)
6. [FAQ](#faq)

---

## 🚀 Getting Started

### Accessing the System

1. Open your web browser
2. Navigate to: `http://localhost/HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/`
3. Enter your username and password
4. Click "Login"

### First Time Login

**Default Credentials:**
- Admin: `admin` / `password`
- HR Staff: `hrstaff` / `password`

⚠️ **IMPORTANT:** Change your password immediately after first login!

### Changing Your Password

1. Click on your name in the top right
2. Select "Profile Settings"
3. Enter your current password
4. Enter and confirm new password
5. Click "Update Profile"

---

## 👨‍💼 Admin User Guide

### Dashboard Overview

The admin dashboard provides:
- **Total Statistics**: Employee count, departments, active users
- **Pie Chart**: Distribution of resignations by department
- **Bar Chart**: Monthly resignation trends
- **Recent Records**: Last 5 resignation entries
- **Filters**: Filter data by department and year

**How to Use Filters:**
1. Select a department from the dropdown
2. Select a year
3. Click "Apply Filter"
4. Click "Reset" to clear filters

---

### Managing System Users

#### Adding a New User

1. Go to **System Users** from the sidebar
2. Click **"Add New User"** button
3. Fill in the form:
   - Username (unique, no spaces)
   - Full Name
   - Password (minimum 6 characters)
   - Role (Admin or HR Staff)
   - Active status (checked = active)
4. Click **"Save User"**

#### Editing a User

1. Find the user in the table
2. Click the **"Edit"** button
3. Modify the information
4. Click **"Update User"**

**Note:** Leave password blank to keep existing password

#### Deleting a User

1. Find the user in the table
2. Click the **"Delete"** button
3. Confirm deletion
4. Click **"Delete User"**

**Restrictions:**
- Cannot delete your own account
- Deleted users cannot be recovered

#### User Status

- **Active**: User can log in
- **Inactive**: User account is disabled

---

### Managing Departments

#### Adding a Department

1. Go to **Departments** from sidebar
2. Click **"Add New Department"**
3. Enter department name
4. Click **"Save Department"**

#### Editing a Department

1. Find the department in the table
2. Click **"Edit"**
3. Modify the name
4. Click **"Update Department"**

#### Deleting a Department

1. Ensure no employees are assigned to the department
2. Click **"Delete"**
3. Confirm deletion

**Note:** Cannot delete departments with assigned employees

---

### Managing Employee Records

#### Adding an Employee Record

1. Click **"Add Employee"** from sidebar or records page
2. Fill in the form:
   - **Employee Name** (required)
   - **Department** (required)
   - **Employee Status** (required):
     - Resigned
     - Retired
     - Terminated
     - Contract Ended
   - **Rendered Years** (required): e.g., 5.5
   - **Date of Separation** (required)
   - **Explanation**: Details about the separation
3. Click **"Save Record"**

#### Viewing Employee Details

1. Go to **Employee Records**
2. Find the employee
3. Click **"View"** button
4. Modal displays complete information

#### Editing an Employee Record

1. Go to **Employee Records**
2. Find the employee
3. Click **"Edit"** button
4. Modify the information
5. Click **"Update Employee"**

#### Deleting an Employee Record

1. Find the employee in the records
2. Click **"Delete"** button
3. Confirm deletion (this cannot be undone)
4. Click **"Delete Record"**

#### Filtering Records

1. Use the filter section above the table
2. Select department and/or year
3. Click **"Apply Filter"**
4. Click **"Reset"** to show all records

#### Using the Table Features

- **Search**: Type in the search box to find records
- **Sort**: Click column headers to sort
- **Pagination**: Use controls at bottom to navigate pages
- **Show Entries**: Change how many records per page

---

### Viewing Audit Logs

The audit log tracks all changes to employee records.

1. Go to **Audit Logs** from sidebar
2. View all actions:
   - **INSERT**: New record added
   - **UPDATE**: Record modified
   - **DELETE**: Record removed
3. Click **"View Details"** to see:
   - Old values (what it was before)
   - New values (what it changed to)
   - Who made the change
   - When the change occurred

**Audit Log Information:**
- Action type (color-coded badge)
- Employee name and ID
- User who made the change
- Timestamp
- Detailed JSON comparison

---

### Profile Settings

Update your account information:

1. Go to **Profile Settings**
2. View current account information
3. Enter current password (required)
4. Update username or full name
5. Optionally change password
6. Click **"Update Profile"**

---

## 👩‍💼 HR Staff User Guide

### Dashboard

HR Staff dashboard shows:
- Total resignations and departments
- Charts filtered by your access
- Recent resignation records
- Filter options

**Using the Dashboard:**
1. View statistics at top
2. Use filters to narrow data
3. Review charts for insights
4. Check recent records table

---

### Adding Employee Records

Same process as Admin (see Admin section above)

**Your Responsibilities:**
- Ensure data accuracy
- Provide complete information
- Use proper status codes
- Add detailed explanations

---

### Managing Records

You can:
- ✅ View all employee records
- ✅ Edit records
- ✅ Delete records (if authorized)
- ✅ Filter and search

**Best Practices:**
- Double-check dates
- Keep explanations professional
- Update records promptly
- Use filters to find records quickly

---

### Your Profile

Update your information:
1. Go to **Profile Settings**
2. Change username or name
3. Update password if needed
4. Save changes

**Security Note:** You cannot change your own role

---

## 📋 Common Tasks

### Task 1: Record a New Resignation

1. Go to **Add Employee**
2. Enter employee details
3. Select appropriate status
4. Add separation date
5. Provide explanation
6. Save record

### Task 2: Update an Existing Record

1. Go to **Employee Records**
2. Search for the employee
3. Click **Edit**
4. Make necessary changes
5. Save updates

### Task 3: Find Records from Specific Department

1. Go to **Employee Records**
2. Use department filter dropdown
3. Select department
4. Click **Apply Filter**

### Task 4: Generate a Report

1. Go to **Employee Records**
2. Apply desired filters
3. Use table search for specific criteria
4. Use DataTables export features:
   - Copy to clipboard
   - Export to Excel
   - Export to PDF
   - Print view

### Task 5: Check Who Modified a Record

1. Go to **Audit Logs** (Admin only)
2. Find the employee record
3. View change details
4. See user and timestamp

---

## 💡 Tips & Best Practices

### Data Entry

✅ **Do:**
- Enter complete and accurate information
- Use consistent date formats
- Provide detailed explanations
- Double-check before saving
- Update records when needed

❌ **Don't:**
- Leave required fields empty
- Use abbreviations in names
- Enter future dates
- Duplicate entries
- Delete records unnecessarily

### Security

✅ **Do:**
- Use strong passwords (12+ characters)
- Log out when finished
- Change password regularly
- Keep credentials confidential
- Report suspicious activity

❌ **Don't:**
- Share your password
- Leave computer unattended while logged in
- Use simple passwords
- Write down passwords
- Access from public computers

### System Usage

✅ **Do:**
- Use filters to narrow searches
- Take advantage of table sorting
- Review recent entries regularly
- Check audit logs periodically (Admin)
- Keep browser updated

❌ **Don't:**
- Open multiple sessions
- Refresh during form submission
- Use browser back button on forms
- Delete system files
- Modify database directly

---

## ❓ FAQ

### General Questions

**Q: How do I reset my password if I forgot it?**  
A: Contact your system administrator to reset your password.

**Q: Can I access the system from home?**  
A: This depends on your network configuration. Contact IT support.

**Q: Why can't I see certain menu items?**  
A: Menu items are role-based. HR Staff has limited access compared to Admin.

**Q: How long does my session last?**  
A: Sessions typically last for your work period. Log out manually when done.

### Data Management

**Q: Can I delete a record?**  
A: Yes, but deletions are permanent and logged in the audit trail.

**Q: Can I recover a deleted record?**  
A: No. Deletions are permanent. However, audit logs retain the information.

**Q: How far back does the system track records?**  
A: Indefinitely. All records and changes are preserved.

**Q: Can I export the data?**  
A: Yes, use the DataTables export features on any table.

### Technical Issues

**Q: The charts aren't loading. What should I do?**  
A: Check your internet connection. Charts load from CDN resources.

**Q: I'm getting an error message. What should I do?**  
A: Note the exact error message and contact your system administrator.

**Q: The page is loading slowly. Is this normal?**  
A: Large datasets may load slowly. Use filters to reduce data volume.

**Q: Can I use this on my mobile phone?**  
A: Yes, the system is responsive and works on mobile devices.

### Administrative Questions

**Q: How do I add a new department?** (Admin)  
A: Go to Departments page and click "Add New Department".

**Q: Can I change a user's role?** (Admin)  
A: Yes, edit the user and change their role in System Users.

**Q: How do I deactivate a user without deleting them?** (Admin)  
A: Edit the user and uncheck the "Active" checkbox.

**Q: Where can I see who changed a record?** (Admin)  
A: Check the Audit Logs page for complete change history.

---

## 🆘 Getting Help

If you need assistance:

1. **Check this guide** for common tasks
2. **Review the README.md** for technical information
3. **Check INSTALLATION.md** for setup issues
4. **Contact your system administrator** for account issues
5. **Report bugs** to IT support with details

### What to Include in Support Requests

- Your username (never password)
- What you were trying to do
- Exact error message (if any)
- Browser you're using
- Steps to reproduce the issue

---

## 📞 Contact Information

**System Administrator:** Contact your IT department  
**Technical Support:** Contact HRDO-SEAIT IT  
**System Version:** 1.0.0

---

## 📝 Quick Reference Card

### Keyboard Shortcuts

- `Ctrl + F`: Search within page
- `Esc`: Close modal windows
- `Tab`: Navigate between form fields
- `Enter`: Submit forms

### Common Actions

| Action | Admin | HR Staff |
|--------|-------|----------|
| View Dashboard | ✅ | ✅ |
| Add Employee | ✅ | ✅ |
| Edit Employee | ✅ | ✅ |
| Delete Employee | ✅ | ✅ |
| Manage Users | ✅ | ❌ |
| Manage Departments | ✅ | ❌ |
| View Audit Logs | ✅ | ❌ |
| Update Own Profile | ✅ | ✅ |

### Status Codes

- **Resigned**: Employee voluntarily left
- **Retired**: Employee reached retirement
- **Terminated**: Employment terminated by company
- **Contract Ended**: Fixed-term contract concluded

---

**Last Updated:** November 13, 2025  
**Document Version:** 1.0.0
