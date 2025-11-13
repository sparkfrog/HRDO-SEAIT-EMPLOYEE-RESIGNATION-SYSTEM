<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'Dashboard - Admin';

// Get filter parameters
$filter_department = $_GET['department'] ?? '';
$filter_year = $_GET['year'] ?? date('Y');

// Build WHERE clause for filters
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

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM employees e $where_clause");
$stmt->execute($params);
$total_employees = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
$total_departments = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM system_users WHERE active = 1");
$total_users = $stmt->fetchColumn();

// Get employees by department for pie chart
$stmt = $pdo->prepare("
    SELECT d.department_name, COUNT(e.id) as count
    FROM employees e
    JOIN departments d ON e.department_id = d.id
    $where_clause
    GROUP BY d.id, d.department_name
    ORDER BY count DESC
");
$stmt->execute($params);
$dept_data = $stmt->fetchAll();

$dept_labels = [];
$dept_counts = [];
foreach ($dept_data as $row) {
    $dept_labels[] = $row['department_name'];
    $dept_counts[] = $row['count'];
}

// Get resignation trends by month for current year
$stmt = $pdo->prepare("
    SELECT MONTH(date_of_separation) as month, COUNT(*) as count
    FROM employees
    WHERE YEAR(date_of_separation) = ?
    GROUP BY MONTH(date_of_separation)
    ORDER BY month
");
$stmt->execute([$filter_year]);
$monthly_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthly_counts = [];
for ($i = 1; $i <= 12; $i++) {
    $monthly_counts[] = $monthly_data[$i] ?? 0;
}

// Get recent resignations
$stmt = $pdo->prepare("
    SELECT e.*, d.department_name, u.full_name as created_by_name
    FROM employees e
    JOIN departments d ON e.department_id = d.id
    LEFT JOIN system_users u ON e.created_by = u.id
    ORDER BY e.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_resignations = $stmt->fetchAll();

// Get all departments for filter
$departments = getDepartments($pdo);

// Get available years
$stmt = $pdo->query("
    SELECT DISTINCT YEAR(date_of_separation) as year 
    FROM employees 
    ORDER BY year DESC
");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

include '../components/adminNavigation.php';
?>


<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="fas fa-chart-line me-2"></i>Dashboard</h2>
            <p class="text-muted mb-0">Overview of employee resignation records</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm rounded-xl">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-building me-1"></i> Department</label>
                    <select name="department" class="form-select form-select-lg">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= $filter_department == $dept['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-calendar me-1"></i> Year</label>
                    <select name="year" class="form-select form-select-lg">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $filter_year == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg flex-grow-1">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <?php
        $stats = [
            ['icon'=>'fas fa-users', 'color'=>'primary', 'count'=>$total_employees, 'label'=>'Total Resignations'],
            ['icon'=>'fas fa-sitemap', 'color'=>'success', 'count'=>$total_departments, 'label'=>'Departments'],
            ['icon'=>'fas fa-user-shield', 'color'=>'info', 'count'=>$total_users, 'label'=>'Active Users'],
        ];
        foreach($stats as $stat): ?>
            <div class="col-md-4">
                <div class="card shadow-sm rounded-xl hover-shadow p-4 text-center">
                    <i class="<?= $stat['icon'] ?> fa-3x text-<?= $stat['color'] ?> mb-3"></i>
                    <h3 class="fw-bold mb-1"><?= $stat['count'] ?></h3>
                    <p class="text-muted mb-0"><?= $stat['label'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm rounded-xl">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-chart-pie me-2"></i> Resignations by Department
                </div>
                <div class="card-body">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm rounded-xl">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-chart-bar me-2"></i> Monthly Resignation Trends (<?= $filter_year ?>)
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Resignations -->
    <div class="card shadow-sm rounded-xl">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-clock me-2"></i> Recent Resignations
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Date of Separation</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recent_resignations)): ?>
                            <tr><td colspan="5" class="text-center py-3">No records found</td></tr>
                        <?php else: ?>
                            <?php foreach($recent_resignations as $emp): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($emp['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($emp['department_name']) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($emp['employee_status']) ?></span>
                                    </td>
                                    <td><?= formatDate($emp['date_of_separation']) ?></td>
                                    <td><?= htmlspecialchars($emp['created_by_name'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>  
<script>
// Department Pie Chart
const deptCtx = document.getElementById('deptChart').getContext('2d');
const deptChart = new Chart(deptCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($dept_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($dept_counts); ?>,
            backgroundColor: [
                '#667eea', '#764ba2', '#f093fb', '#4facfe',
                '#43e97b', '#fa709a', '#fee140', '#30cfd0'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Monthly Bar Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Resignations',
            data: <?php echo json_encode($monthly_counts); ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.8)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
