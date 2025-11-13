<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'Departments - Admin';
$error = '';
$success = '';

// Handle Add Department
if (isset($_POST['add_department'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $department_name = sanitizeInput($_POST['department_name']);
        
        if (empty($department_name)) {
            $error = 'Department name is required.';
        } else {
            try {
                // Check if department already exists
                $stmt = $pdo->prepare("SELECT id FROM departments WHERE department_name = ?");
                $stmt->execute([$department_name]);
                if ($stmt->fetch()) {
                    $error = 'Department already exists.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO departments (department_name) VALUES (?)");
                    $stmt->execute([$department_name]);
                    $success = 'Department added successfully!';
                }
            } catch (PDOException $e) {
                error_log("Add Department Error: " . $e->getMessage());
                $error = 'Failed to add department.';
            }
        }
    }
}

// Handle Update Department
if (isset($_POST['update_department'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $department_id = (int)$_POST['department_id'];
        $department_name = sanitizeInput($_POST['department_name']);
        
        if (empty($department_name)) {
            $error = 'Department name is required.';
        } else {
            try {
                // Check if department name is taken by another department
                $stmt = $pdo->prepare("SELECT id FROM departments WHERE department_name = ? AND id != ?");
                $stmt->execute([$department_name, $department_id]);
                if ($stmt->fetch()) {
                    $error = 'Department name already exists.';
                } else {
                    $stmt = $pdo->prepare("UPDATE departments SET department_name = ? WHERE id = ?");
                    $stmt->execute([$department_name, $department_id]);
                    $success = 'Department updated successfully!';
                }
            } catch (PDOException $e) {
                error_log("Update Department Error: " . $e->getMessage());
                $error = 'Failed to update department.';
            }
        }
    }
}

// Handle Delete Department
if (isset($_POST['delete_department'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $department_id = (int)$_POST['department_id'];
        
        try {
            // Check if department has employees
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE department_id = ?");
            $stmt->execute([$department_id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = "Cannot delete department. It has $count employee(s) assigned.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
                $stmt->execute([$department_id]);
                $success = 'Department deleted successfully!';
            }
        } catch (PDOException $e) {
            error_log("Delete Department Error: " . $e->getMessage());
            $error = 'Failed to delete department.';
        }
    }
}

// Get all departments with employee count
$stmt = $pdo->query("
    SELECT d.*, COUNT(e.id) as employee_count
    FROM departments d
    LEFT JOIN employees e ON d.id = e.department_id
    GROUP BY d.id
    ORDER BY d.department_name ASC
");
$departments = $stmt->fetchAll();

$csrf_token = generateCSRFToken();

include '../components/adminNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-sitemap me-2"></i> Departments</h2>
        <p class="text-muted">Manage organization departments</p>
    </div>
</div>

<?php if ($error): echo errorAlert($error); endif; ?>
<?php if ($success): echo successAlert($success); endif; ?>

<!-- Add Department Button -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
        <i class="fas fa-plus me-2"></i> Add New Department
    </button>
</div>

<!-- Departments Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="departmentsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Employee Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo $dept['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo $dept['employee_count']; ?> employee(s)
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="editDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_name'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($dept['employee_count'] == 0): ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete department with employees">
                                        <i class="fas fa-ban"></i> Delete
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

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="department_name" class="form-control" 
                               placeholder="Enter department name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_department" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="department_id" id="edit_department_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="department_name" id="edit_department_name" 
                               class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_department" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Department Modal -->
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="department_id" id="delete_department_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete department <strong id="delete_department_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_department" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i> Delete Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#departmentsTable').DataTable({
        order: [[1, 'asc']]
    });
});

function editDepartment(id, name) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_department_name').value = name;
    
    new bootstrap.Modal(document.getElementById('editDepartmentModal')).show();
}

function deleteDepartment(id, name) {
    document.getElementById('delete_department_id').value = id;
    document.getElementById('delete_department_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteDepartmentModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
