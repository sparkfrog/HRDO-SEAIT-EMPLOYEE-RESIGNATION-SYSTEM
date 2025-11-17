<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pageTitle ?? 'HRDO Resignation System'; ?></title>
<link rel="icon" href="../img/seaitLogo.png">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --sidebar-width: 250px;
    --sidebar-collapsed-width: 80px;
    --primary-color: #20c997;
    --secondary-color: #0dcaf0;
    --bg-color: #f1f3f5;
    --text-color: #343a40;
    --card-radius: 14px;
}

/* Body */
body {
    font-family: "Segoe UI", sans-serif;
    background-color: var(--bg-color);
    margin: 0;
    overflow-x: hidden;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: white;
    border-right: 1px solid #e5e5e5;
    transition: 0.3s ease;
    z-index: 1000;
    padding-top: 15px;
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    border-bottom: 1px solid #ececec;
}

.sidebar-logo {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.sidebar.collapsed .sidebar-text {
    display: none;
}

.sidebar.collapsed .sidebar-header {
    justify-content: center;
}

/* Sidebar Menu */
.sidebar-menu {
    margin-top: 15px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 13px 20px;
    color: #555;
    font-weight: 500;
    text-decoration: none;
    border-radius: 10px;
    margin: 6px 12px;
    transition: 0.25s ease;
}

.sidebar-menu a i {
    min-width: 25px;
    text-align: center;
    font-size: 18px;
}

.sidebar-menu a:hover {
    background: rgba(32, 201, 151, 0.15);
    color: var(--primary-color);
    transform: translateX(4px);
}

.sidebar-menu a.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white !important;
    box-shadow: 0 4px 12px rgba(32, 201, 151, 0.3);
}

.sidebar.collapsed a span {
    display: none;
}

/* MAIN CONTENT */
.main-content {
    margin-left: var(--sidebar-width);
    transition: 0.3s ease;
}

.sidebar.collapsed ~ .main-content {
    margin-left: var(--sidebar-collapsed-width);
}

/* Top Navbar */
.top-navbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    background: #fff;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e5e5;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* Navbar toggle */
.toggle-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-color);
    cursor: pointer;
    transition: 0.3s ease;
}

.toggle-btn:hover {
    transform: rotate(90deg);
}

/* User info */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

/* Content Wrapper */
.content-wrapper {
    padding: 30px;
}

/* Cards */
.card {
    border: none;
    border-radius: var(--card-radius);
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    transition: 0.25s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.10);
}

.card-header {
    background: #fff;
    padding: 18px;
    border-radius: var(--card-radius) var(--card-radius) 0 0;
    font-weight: 600;
}

/* Modal fix */
.modal {
    z-index: 1056 !important;
}
.modal-backdrop {
    z-index: 1055 !important;
}

/* Mobile */
@media (max-width: 768px) {
    .sidebar {
        left: -100%;
        position: fixed;
    }

    .sidebar.show {
        left: 0;
    }

    .main-content {
        margin-left: 0 !important;
    }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/seaitLogo.png" class="sidebar-logo">
        <div class="sidebar-text">
            <h5 class="mb-0 fw-bold">HRDO System</h5>
            <small class="text-muted">Admin Panel</small>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i><span>Dashboard</span>
        </a>
        <a href="SystemUsers.php" class="<?= $current_page=='SystemUsers.php'?'active':'' ?>">
            <i class="fas fa-users"></i><span>System Users</span>
        </a>
        <a href="department.php" class="<?= $current_page=='department.php'?'active':'' ?>">
            <i class="fas fa-sitemap"></i><span>Departments</span>
        </a>
        <a href="addEmployee.php" class="<?= $current_page=='addEmployee.php'?'active':'' ?>">
            <i class="fas fa-user-plus"></i><span>Add Employee</span>
        </a>
        <a href="recordsEmployee.php" class="<?= $current_page=='recordsEmployee.php'?'active':'' ?>">
            <i class="fas fa-folder-open"></i><span>Employee Records</span>
        </a>
        <a href="auditlogs.php" class="<?= $current_page=='auditlogs.php'?'active':'' ?>">
            <i class="fas fa-history"></i><span>Audit Logs</span>
        </a>
        <a href="profilesettings.php" class="<?= $current_page=='profilesettings.php'?'active':'' ?>">
            <i class="fas fa-user-cog"></i><span>Profile Settings</span>
        </a>
        <a href="../auth/logout.php" style="color:#ff4d4d;margin-top:20px;">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="top-navbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

        <div class="user-info">
            <div>
                <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong><br>
                <small class="text-muted"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($_SESSION['role']); ?></small>
            </div>
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'],0,1)); ?></div>
        </div>
    </div>

    <div class="content-wrapper">
