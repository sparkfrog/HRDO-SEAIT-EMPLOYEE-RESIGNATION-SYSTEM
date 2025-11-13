<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';
//recordsEmployee.php
// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'Employee Records - Admin';
$error = '';
$success = '';

// Handle Update Employee
if (isset($_POST['update_employee'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $employee_id = (int)$_POST['employee_id'];
        $name = sanitizeInput($_POST['name']);
        $department_id = (int)$_POST['department_id'];
        $employee_status = sanitizeInput($_POST['employee_status']);
        $rendered_years = floatval($_POST['rendered_years']);
        $date_of_separation = sanitizeInput($_POST['date_of_separation']);
        $explanation = sanitizeInput($_POST['explanation']);
        $updated_by = $_SESSION['user_id'];
        
        if (empty($name) || empty($department_id) || empty($employee_status) || 
            empty($rendered_years) || empty($date_of_separation)) {
            $error = 'All required fields must be filled.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Get old values
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->execute([$employee_id]);
                $old_values = $stmt->fetch();
                
                // Update employee
                $stmt = $pdo->prepare("
                    UPDATE employees 
                    SET name = ?, department_id = ?, employee_status = ?, rendered_years = ?, 
                        date_of_separation = ?, explanation = ?, updated_by = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $department_id, $employee_status, $rendered_years, 
                    $date_of_separation, $explanation, $updated_by, $employee_id
                ]);
                
                // Log audit
                $new_values = [
                    'name' => $name,
                    'department_id' => $department_id,
                    'employee_status' => $employee_status,
                    'rendered_years' => $rendered_years,
                    'date_of_separation' => $date_of_separation,
                    'explanation' => $explanation
                ];
                
                logAudit($pdo, $employee_id, 'UPDATE', $updated_by, $old_values, $new_values);
                
                $pdo->commit();
                $success = 'Employee record updated successfully!';
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Update Employee Error: " . $e->getMessage());
                $error = 'Failed to update employee record.';
            }
        }
    }
}

// Handle Delete Employee
if (isset($_POST['delete_employee'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $employee_id = (int)$_POST['employee_id'];
        
        try {
            $pdo->beginTransaction();
            
            // Get old values
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            $old_values = $stmt->fetch();
            
            // Log audit before deleting
            logAudit($pdo, $employee_id, 'DELETE', $_SESSION['user_id'], $old_values, null);
            
            // Delete employee
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            
            $pdo->commit();
            $success = 'Employee record deleted successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Delete Employee Error: " . $e->getMessage());
            $error = 'Failed to delete employee record.';
        }
    }
}

// Get filter parameters
$filter_department = $_GET['department'] ?? '';
$filter_year = $_GET['year'] ?? '';

// Build WHERE clause
$where_clause = "WHERE 1=1";
$params = [];

if (!empty($filter_department)) {
    $where_clause .= " AND e.department_id = ?";
    $params[] = $filter_department;
}

if (!empty($filter_year)) {
    $where_clause .= " AND YEAR(e.date_of_separation) = ?";
    $params[] = $filter_year;
}

// Get all employees
$stmt = $pdo->prepare("
    SELECT e.*, d.department_name, 
           u1.full_name as created_by_name,
           u2.full_name as updated_by_name
    FROM employees e
    JOIN departments d ON e.department_id = d.id
    LEFT JOIN system_users u1 ON e.created_by = u1.id
    LEFT JOIN system_users u2 ON e.updated_by = u2.id
    $where_clause
    ORDER BY e.created_at DESC
");
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Get departments for filter
$departments = getDepartments($pdo);

// Get available years
$stmt = $pdo->query("
    SELECT DISTINCT YEAR(date_of_separation) as year 
    FROM employees 
    ORDER BY year DESC
");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

$csrf_token = generateCSRFToken();

include '../components/adminNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-folder-open me-2"></i> Employee Records</h2>
        <p class="text-muted">View and manage employee resignation records</p>
    </div>
</div>

<?php if ($error): echo errorAlert($error); endif; ?>
<?php if ($success): echo successAlert($success); endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-building me-1"></i> Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-calendar me-1"></i> Year</label>
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $filter_year == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Apply Filter
                </button>
                <a href="recordsEmployee.php" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Add Employee Button -->
<div class="mb-3">
    <a href="addEmployee.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Add New Employee
    </a>
</div>

<!-- Employees Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="employeesTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Rendered Years</th>
                        <th>Date of Separation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?php echo $emp['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($emp['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($emp['department_name']); ?></td>
                            <td>
                                <?php
                                $badge_class = 'bg-info';
                                if ($emp['employee_status'] == 'Terminated') $badge_class = 'bg-danger';
                                if ($emp['employee_status'] == 'Retired') $badge_class = 'bg-success';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($emp['employee_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                echo is_null($emp['rendered_years']) ? 'NULL' : $emp['rendered_years'] . ' years'; 
                                ?>
                            </td>
                            <td><?php echo formatDate($emp['date_of_separation']); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="viewEmployee(<?php echo htmlspecialchars(json_encode($emp)); ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="editEmployee(<?php echo htmlspecialchars(json_encode($emp)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteEmployee(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars($emp['name'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i> Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user me-2"></i> Name:</strong>
                        <p id="view_name"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-building me-2"></i> Department:</strong>
                        <p id="view_department"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-briefcase me-2"></i> Status:</strong>
                        <p id="view_status"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-clock me-2"></i> Rendered Years:</strong>
                        <p id="view_years"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-calendar me-2"></i> Date of Separation:</strong>
                        <p id="view_date"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user-plus me-2"></i> Created By:</strong>
                        <p id="view_created_by"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-comment me-2"></i> Explanation:</strong>
                        <p id="view_explanation"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="employee_id" id="edit_employee_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="edit_department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="employee_status" id="edit_status" class="form-select" required>
                                    <option value="Resigned">Resigned</option>
                                    <option value="Retired">Retired</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Contract Ended">Contract Ended</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rendered Years <span class="text-danger">*</span></label>
                                <input type="number" name="rendered_years" id="edit_years" 
                                       class="form-control" step="0.1" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Separation <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_separation" id="edit_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Explanation</label>
                        <textarea name="explanation" id="edit_explanation" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_employee" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="employee_id" id="delete_employee_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete the record for <strong id="delete_employee_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_employee" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i> Delete Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({
        order: [[0, 'desc']]
    });
});

function viewEmployee(emp) {
    document.getElementById('view_name').textContent = emp.name;
    document.getElementById('view_department').textContent = emp.department_name;
    document.getElementById('view_status').innerHTML = '<span class="badge bg-info">' + emp.employee_status + '</span>';
    document.getElementById('view_years').textContent = emp.rendered_years === null ? 'NULL' : emp.rendered_years + ' years';
    document.getElementById('view_date').textContent = new Date(emp.date_of_separation).toLocaleDateString();
    document.getElementById('view_created_by').textContent = emp.created_by_name || 'N/A';
    document.getElementById('view_explanation').textContent = emp.explanation || 'No explanation provided';
    
    new bootstrap.Modal(document.getElementById('viewEmployeeModal')).show();
}

function editEmployee(emp) {
    document.getElementById('edit_employee_id').value = emp.id;
    document.getElementById('edit_name').value = emp.name;
    document.getElementById('edit_department_id').value = emp.department_id;
    document.getElementById('edit_status').value = emp.employee_status;
    document.getElementById('edit_years').value = emp.rendered_years;
    document.getElementById('edit_date').value = emp.date_of_separation;
    document.getElementById('edit_explanation').value = emp.explanation || '';
    
    new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
}

function deleteEmployee(id, name) {
    document.getElementById('delete_employee_id').value = id;
    document.getElementById('delete_employee_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteEmployeeModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
