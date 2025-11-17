<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'Departments & Totals - Admin';
$error = '';
$success = '';

// ============================
// Existing Department CRUD
// ============================

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

// ============================
// CRUD for Department Employee Totals
// ============================


// Handle Add Employee Total
if (isset($_POST['add_total'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $department_id = (int)$_POST['department_id'];
        $year = (int)$_POST['year'];
        $semester = $_POST['semester'];
        $total_employees = (int)$_POST['total_employees'];
        $semester_start = $_POST['semester_start'] ?? null;
        $semester_end = $_POST['semester_end'] ?? null;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO department_employee_totals
                (department_id, year, semester, semester_start, semester_end, total_employees)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$department_id, $year, $semester, $semester_start, $semester_end, $total_employees]);
            $success = 'Employee total added successfully!';
        } catch (PDOException $e) {
            error_log("Add Employee Total Error: " . $e->getMessage());
            $error = 'Failed to add employee total.';
        }
    }
}

// Handle Update Employee Total
if (isset($_POST['update_total'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $id = (int)$_POST['total_id'];
        $year = (int)$_POST['year'];
        $semester = $_POST['semester'];
        $total_employees = (int)$_POST['total_employees'];
        $semester_start = $_POST['semester_start'] ?? null;
        $semester_end = $_POST['semester_end'] ?? null;

        try {
            $stmt = $pdo->prepare("
                UPDATE department_employee_totals
                SET year = ?, semester = ?, semester_start = ?, semester_end = ?, total_employees = ?
                WHERE id = ?
            ");
            $stmt->execute([$year, $semester, $semester_start, $semester_end, $total_employees, $id]);
            $success = 'Employee total updated successfully!';
        } catch (PDOException $e) {
            error_log("Update Employee Total Error: " . $e->getMessage());
            $error = 'Failed to update employee total.';
        }
    }
}

// Handle Delete Employee Total
if (isset($_POST['delete_total'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $id = (int)$_POST['total_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM department_employee_totals WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Employee total deleted successfully!';
        } catch (PDOException $e) {
            error_log("Delete Employee Total Error: " . $e->getMessage());
            $error = 'Failed to delete employee total.';
        }
    }
}

// Get all department totals
$stmt = $pdo->query("
    SELECT t.*, d.department_name
    FROM department_employee_totals t
    JOIN departments d ON d.id = t.department_id
    ORDER BY d.department_name ASC, t.year DESC, t.semester ASC
");
$totals = $stmt->fetchAll();

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
<div class="row mt-5">
    <div class="col-12">
        <h2>Department Employee Totals</h2>
        <p class="text-muted">Add/update total employees per department per semester/year</p>

        <!-- Add Total Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTotalModal">
            <i class="fas fa-plus me-2"></i> Add Employee Total
        </button>

        <!-- Totals Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="totalsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Semester</th>
                                <th>Semester Dates</th>
                                <th>Total Employees</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totals as $t): ?>
                                <tr>
                                    <td><?php echo $t['id']; ?></td>
                                    <td><?php echo htmlspecialchars($t['department_name']); ?></td>
                                    <td><?php echo $t['year']; ?></td>
                                    <td><?php echo $t['semester']; ?></td>
                                    <td><?php echo $t['semester_start'] . ' to ' . $t['semester_end']; ?></td>
                                    <td><?php echo $t['total_employees']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning"
                                                onclick="editTotal(<?php echo $t['id']; ?>, <?php echo $t['department_id']; ?>, '<?php echo $t['year']; ?>', '<?php echo $t['semester']; ?>', <?php echo $t['total_employees']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="deleteTotal(<?php echo $t['id']; ?>)">
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
    </div>
</div>

<!-- Add Total Modal -->
<div class="modal fade" id="addTotalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add Employee Total</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" placeholder="Enter year" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-control" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Start</label>
                        <input type="date" name="semester_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester End</label>
                        <input type="date" name="semester_end" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Employees</label>
                        <input type="number" name="total_employees" class="form-control" placeholder="Enter total employees" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_total" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Total Modal -->
<div class="modal fade" id="editTotalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="total_id" id="edit_total_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Employee Total</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" id="edit_department_id" class="form-control" required disabled>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" id="edit_year" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" id="edit_semester" class="form-control" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Start</label>
                        <input type="date" name="semester_start" id="edit_semester_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester End</label>
                        <input type="date" name="semester_end" id="edit_semester_end" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Employees</label>
                        <input type="number" name="total_employees" id="edit_total_employees" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_total" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Total Modal -->
<div class="modal fade" id="deleteTotalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="total_id" id="delete_total_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this employee total?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_total" class="btn btn-danger"><i class="fas fa-trash me-2"></i> Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#departmentsTable, #totalsTable').DataTable({
        order: [[1, 'asc']]
    });
});

function editTotal(id, dept_id, year, semester, total, start, end) {
    document.getElementById('edit_total_id').value = id;
    document.getElementById('edit_department_id').value = dept_id;
    document.getElementById('edit_year').value = year;
    document.getElementById('edit_semester').value = semester;
    document.getElementById('edit_total_employees').value = total;
    document.getElementById('edit_semester_start').value = start;
    document.getElementById('edit_semester_end').value = end;

    new bootstrap.Modal(document.getElementById('editTotalModal')).show();
}


function deleteTotal(id) {
    document.getElementById('delete_total_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteTotalModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
