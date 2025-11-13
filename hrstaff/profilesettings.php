<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

$pageTitle = 'Profile Settings - HR Staff';
$error = '';
$success = '';

// Get current user info
$stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $current_password = $_POST['current_password'];
        $new_username = sanitizeInput($_POST['username']);
        $new_full_name = sanitizeInput($_POST['full_name']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (empty($new_username) || empty($new_full_name)) {
            $error = 'Username and full name are required.';
        } else {
            // Check if new password is provided
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters.';
                } else {
                    // Update with new password
                    try {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            UPDATE system_users 
                            SET username = ?, full_name = ?, password = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$new_username, $new_full_name, $hashed_password, $_SESSION['user_id']]);
                        
                        // Update session
                        $_SESSION['username'] = $new_username;
                        $_SESSION['full_name'] = $new_full_name;
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        
                        $success = 'Profile updated successfully!';
                    } catch (PDOException $e) {
                        error_log("Profile Update Error: " . $e->getMessage());
                        
                        if ($e->getCode() == 23000) {
                            $error = 'Username already exists.';
                        } else {
                            $error = 'Failed to update profile.';
                        }
                    }
                }
            } else {
                // Update without new password
                try {
                    $stmt = $pdo->prepare("
                        UPDATE system_users 
                        SET username = ?, full_name = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$new_username, $new_full_name, $_SESSION['user_id']]);
                    
                    // Update session
                    $_SESSION['username'] = $new_username;
                    $_SESSION['full_name'] = $new_full_name;
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    
                    $success = 'Profile updated successfully!';
                } catch (PDOException $e) {
                    error_log("Profile Update Error: " . $e->getMessage());
                    
                    if ($e->getCode() == 23000) {
                        $error = 'Username already exists.';
                    } else {
                        $error = 'Failed to update profile.';
                    }
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();

include '../components/hrstaffNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-user-cog me-2"></i> Profile Settings</h2>
        <p class="text-muted">Update your account information</p>
    </div>
</div>

<?php if ($error): echo errorAlert($error); endif; ?>
<?php if ($success): echo successAlert($success); endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Profile Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2"></i> Current Profile Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-id-badge me-2"></i> User ID:</strong>
                        <p class="mb-0"><?php echo $user['id']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user-tie me-2"></i> Role:</strong>
                        <p class="mb-0">
                            <span class="badge bg-info">
                                <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-calendar-plus me-2"></i> Account Created:</strong>
                        <p class="mb-0"><?php echo formatDateTime($user['created_at']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-clock me-2"></i> Last Login:</strong>
                        <p class="mb-0"><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Update Profile Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i> Update Profile
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> You must enter your current password to make any changes.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-key me-1"></i> Current Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="current_password" class="form-control" 
                               placeholder="Enter your current password" required>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-1"></i> Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-id-card me-1"></i> Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Change Password:</strong> Leave these fields blank if you don't want to change your password.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock me-1"></i> New Password
                        </label>
                        <input type="password" name="new_password" class="form-control" 
                               placeholder="Enter new password (leave blank to keep current)">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock me-1"></i> Confirm New Password
                        </label>
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Confirm new password">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Profile
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
