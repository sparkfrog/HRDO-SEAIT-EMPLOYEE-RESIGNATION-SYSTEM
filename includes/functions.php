<?php
/**
 * Utility Functions
 */

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/index.php");
        exit();
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!hasRole('Admin')) {
        header("Location: /HRDO-SEAIT-EMPLOYEE-RESIGNATION-SYSTEM/index.php");
        exit();
    }
}

/**
 * Format date
 */
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('F d, Y g:i A', strtotime($datetime));
}

/**
 * Log audit action
 */
function logAudit($pdo, $employee_id, $action_type, $changed_by, $old_values = null, $new_values = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO employee_audit_logs (employee_id, action_type, changed_by, old_values, new_values)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;
        
        $stmt->execute([$employee_id, $action_type, $changed_by, $old_json, $new_json]);
        return true;
    } catch (PDOException $e) {
        error_log("Audit Log Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all departments
 */
function getDepartments($pdo) {
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY department_name ASC");
    return $stmt->fetchAll();
}

/**
 * Get user by ID
 */
function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Success message alert
 */
function successAlert($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Error message alert
 */
function errorAlert($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Warning message alert
 */
function warningAlert($message) {
    return '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}
