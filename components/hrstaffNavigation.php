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

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --sidebar-width: 250px;
    --sidebar-collapsed-width: 70px;
    --primary-color: #20c997;
    --secondary-color: #0dcaf0;
    --bg-color: #f8f9fa;
    --text-color: #343a40;
    --card-radius: 12px;
}

/* Body & Fonts */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--bg-color);
    margin: 0;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    transition: width 0.3s ease;
    color: white;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.sidebar-logo {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: white;
    padding: 4px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.sidebar.collapsed .sidebar-text {
    display: none;
}

.sidebar.collapsed .sidebar-header {
    justify-content: center;
}

/* Sidebar Menu */
.sidebar-menu {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 20px;
    color: rgba(255,255,255,0.9);
    font-weight: 500;
    text-decoration: none;
    border-radius: 8px;
    margin: 4px 10px;
    transition: 0.3s;
}

.sidebar-menu a i {
    min-width: 25px;
    text-align: center;
}

.sidebar-menu a:hover, .sidebar-menu a.active {
    background-color: rgba(255,255,255,0.15);
    color: white;
    transform: translateX(5px);
}

.sidebar.collapsed .sidebar-menu a span {
    display: none;
}

/* Main Content */
.main-content {
    margin-left: var(--sidebar-width);
    transition: margin-left 0.3s ease;
    min-height: 100vh;
}

.sidebar.collapsed ~ .main-content {
    margin-left: var(--sidebar-collapsed-width);
}

/* Top Navbar */
/* Top Navbar */
.top-navbar {
    position: sticky;
    top: 0;
    z-index: 1020; /* lower than Bootstrap modal (1050) */
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 12px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 12px 12px;
}

/* Ensure modal is above navbar */
.modal {
    z-index: 1055; /* Bootstrap default is 1050 */
}

/* Optional: modal-backdrop */
.modal-backdrop.show {
    z-index: 1050; /* just below modal content */
}


.toggle-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-color);
    cursor: pointer;
    transition: 0.2s;
}

.toggle-btn:hover {
    transform: rotate(90deg);
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

/* Content Wrapper */
.content-wrapper {
    padding: 30px;
}

/* Cards */
.card {
    border: none;
    border-radius: var(--card-radius);
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.card-header {
    background: #fff;
    border-bottom: 2px solid #eaeaea;
    padding: 20px;
    font-weight: 600;
    border-radius: var(--card-radius) var(--card-radius) 0 0;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border: none;
    transition: 0.3s ease;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        left: -100%;
        position: fixed;
        transition: left 0.3s ease;
    }

    .sidebar.show {
        left: 0;
    }

    .main-content {
        margin-left: 0;
    }

    .sidebar.collapsed ~ .main-content {
        margin-left: 0;
    }
}
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/seaitLogo.png" alt="SEAIT Logo" class="sidebar-logo me-2">
        <div class="sidebar-text">
            <h4 class="mb-0">HRDO System</h4>
            <small style="opacity: 0.8;">HR Staff Panel</small>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <a href="addEmployee.php" class="<?php echo $current_page == 'addEmployee.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-plus"></i>
            <span>Add Employee</span>
        </a>
        <a href="recordsEmployee.php" class="<?php echo $current_page == 'recordsEmployee.php' ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i>
            <span>Employee Records</span>
        </a>
        <a href="profilesettings.php" class="<?php echo $current_page == 'profilesettings.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            <span>Profile Settings</span>
        </a>
        <a href="../auth/logout.php" style="margin-top: 20px; color: #ff6b6b;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="top-navbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="user-info">
            <div>
                <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong><br>
                <small class="text-muted"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($_SESSION['role']); ?></small>
            </div>
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'],0,1)); ?></div>
        </div>
    </div>

    <div class="content-wrapper">
