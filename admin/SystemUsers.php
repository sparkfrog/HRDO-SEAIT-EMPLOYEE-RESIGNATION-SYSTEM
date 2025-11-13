<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'System Users - Admin';
$error = '';
$success = '';

// Handle Add User
if (isset($_POST['add_user'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $username = sanitizeInput($_POST['username']);
        $full_name = sanitizeInput($_POST['full_name']);
        $role = sanitizeInput($_POST['role']);
        $password = $_POST['password'];
        $active = isset($_POST['active']) ? 1 : 0;
        
        if (empty($username) || empty($full_name) || empty($password) || empty($role)) {
            $error = 'All fields are required.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM system_users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO system_users (username, full_name, password, role, active)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$username, $full_name, $hashed_password, $role, $active]);
                    $success = 'User added successfully!';
                }
            } catch (PDOException $e) {
                error_log("Add User Error: " . $e->getMessage());
                $error = 'Failed to add user.';
            }
        }
    }
}

// Handle Update User
if (isset($_POST['update_user'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $user_id = (int)$_POST['user_id'];
        $username = sanitizeInput($_POST['username']);
        $full_name = sanitizeInput($_POST['full_name']);
        $role = sanitizeInput($_POST['role']);
        $password = $_POST['password'];
        $active = isset($_POST['active']) ? 1 : 0;
        
        if (empty($username) || empty($full_name) || empty($role)) {
            $error = 'Username, full name, and role are required.';
        } else {
            try {
                // Check if username is taken by another user
                $stmt = $pdo->prepare("SELECT id FROM system_users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $user_id]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists.';
                } else {
                    if (!empty($password)) {
                        // Update with new password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            UPDATE system_users 
                            SET username = ?, full_name = ?, password = ?, role = ?, active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$username, $full_name, $hashed_password, $role, $active, $user_id]);
                    } else {
                        // Update without password
                        $stmt = $pdo->prepare("
                            UPDATE system_users 
                            SET username = ?, full_name = ?, role = ?, active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$username, $full_name, $role, $active, $user_id]);
                    }
                    $success = 'User updated successfully!';
                }
            } catch (PDOException $e) {
                error_log("Update User Error: " . $e->getMessage());
                $error = 'Failed to update user.';
            }
        }
    }
}

// Handle Delete User
if (isset($_POST['delete_user'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $user_id = (int)$_POST['user_id'];
        
        // Prevent deleting self
        if ($user_id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM system_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $success = 'User deleted successfully!';
            } catch (PDOException $e) {
                error_log("Delete User Error: " . $e->getMessage());
                $error = 'Failed to delete user. User may have related records.';
            }
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM system_users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$csrf_token = generateCSRFToken();

include '../components/adminNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-users me-2"></i> System Users</h2>
        <p class="text-muted">Manage system users and their roles</p>
    </div>
</div>

<?php if ($error): echo errorAlert($error); endif; ?>
<?php if ($success): echo successAlert($success); endif; ?>

<!-- Add User Button -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus me-2"></i> Add New User
    </button>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <?php if ($user['role'] == 'Admin'): ?>
                                    <span class="badge bg-danger"><i class="fas fa-shield-alt"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info"><i class="fas fa-user-tie"></i> HR Staff</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="HR STAFF">HR Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="active" class="form-check-input" id="activeAdd" checked>
                            <label class="form-check-label" for="activeAdd">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="Admin">Admin</option>
                            <option value="HR STAFF">HR Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="active" class="form-check-input" id="edit_active">
                            <label class="form-check-label" for="edit_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i> Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        order: [[0, 'desc']]
    });
});

function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_active').checked = user.active == 1;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(id, username) {
    document.getElementById('delete_user_id').value = id;
    document.getElementById('delete_username').textContent = username;
    
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
