<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

$pageTitle = 'Add Employee - HR Staff';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        $name = sanitizeInput($_POST['name']);
        $department_id = (int)$_POST['department_id'];
        $employee_status = !empty($_POST['employee_status']) ? sanitizeInput($_POST['employee_status']) : null;
        $rendered_years = !empty($_POST['rendered_years']) ? floatval($_POST['rendered_years']) : null;
        $date_of_separation = sanitizeInput($_POST['date_of_separation']);
        $explanation = !empty($_POST['explanation']) ? sanitizeInput($_POST['explanation']) : null;
        $created_by = $_SESSION['user_id'];
        
        if (empty($name) || empty($department_id) || empty($date_of_separation)) {
            $error = 'All required fields must be filled.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Insert employee
                $stmt = $pdo->prepare("
                    INSERT INTO employees (name, department_id, employee_status, rendered_years, 
                                          date_of_separation, explanation, created_by, updated_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $department_id, $employee_status, $rendered_years, 
                    $date_of_separation, $explanation, $created_by, $created_by
                ]);
                
                $employee_id = $pdo->lastInsertId();
                
                // Log audit
                $new_values = [
                    'name' => $name,
                    'department_id' => $department_id,
                    'employee_status' => $employee_status,
                    'rendered_years' => $rendered_years,
                    'date_of_separation' => $date_of_separation,
                    'explanation' => $explanation
                ];
                
                logAudit($pdo, $employee_id, 'INSERT', $created_by, null, $new_values);
                
                $pdo->commit();
                $success = 'Employee record added successfully!';
                
                // Clear form
                $_POST = [];
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Add Employee Error: " . $e->getMessage());
                $error = 'Failed to add employee record.';
            }
        }
    }
}

// Get all departments
$departments = getDepartments($pdo);

$csrf_token = generateCSRFToken();

include '../components/hrstaffNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-user-plus me-2"></i> Add Employee Resignation Record</h2>
        <p class="text-muted">Record a new employee separation</p>
    </div>
</div>

<?php if ($error): echo errorAlert($error); endif; ?>
<?php if ($success): echo successAlert($success); endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt me-2"></i> Employee Information
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-1"></i> Employee Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="Enter employee full name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-building me-1"></i> Department <span class="text-danger">*</span>
                        </label>
                        <select name="department_id" class="form-select" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                        <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-briefcase me-1"></i> Employee Status
                                </label>
                                <select name="employee_status" class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="Resigned" <?php echo (isset($_POST['employee_status']) && $_POST['employee_status'] == 'Resigned') ? 'selected' : ''; ?>>Resigned</option>
                                    <option value="Retired" <?php echo (isset($_POST['employee_status']) && $_POST['employee_status'] == 'Retired') ? 'selected' : ''; ?>>Retired</option>
                                    <option value="Terminated" <?php echo (isset($_POST['employee_status']) && $_POST['employee_status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                                    <option value="Contract Ended" <?php echo (isset($_POST['employee_status']) && $_POST['employee_status'] == 'Contract Ended') ? 'selected' : ''; ?>>Contract Ended</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock me-1"></i> Rendered Years
                                </label>
                                <input type="number" name="rendered_years" class="form-control" 
                                       step="0.1" min="0" max="99.9"
                                       placeholder="e.g., 5.5"
                                       value="<?php echo htmlspecialchars($_POST['rendered_years'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar me-1"></i> Date of Separation <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_of_separation" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['date_of_separation'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-comment me-1"></i> Explanation / Reason
                        </label>
                        <textarea name="explanation" class="form-control" rows="4" 
                                  placeholder="Enter reason for separation or additional details"><?php echo htmlspecialchars($_POST['explanation'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Record
                        </button>
                        <a href="recordsEmployee.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
