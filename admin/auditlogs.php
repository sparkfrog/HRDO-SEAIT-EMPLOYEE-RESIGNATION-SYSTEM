<?php
session_start();
require_once '../db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireAdmin();

$pageTitle = 'Audit Logs - Admin';

// Get all audit logs with related information
$stmt = $pdo->query("
    SELECT 
        al.*,
        e.name as employee_name,
        u.full_name as changed_by_name
    FROM employee_audit_logs al
    LEFT JOIN employees e ON al.employee_id = e.id
    LEFT JOIN system_users u ON al.changed_by = u.id
    ORDER BY al.change_timestamp DESC
");
$audit_logs = $stmt->fetchAll();

include '../components/adminNavigation.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-history me-2"></i> Audit Logs</h2>
        <p class="text-muted">View all employee record changes and actions</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="auditLogsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Employee</th>
                        <th>Changed By</th>
                        <th>Timestamp</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td>
                                <?php
                                $badge_class = 'bg-info';
                                $icon = 'fa-plus';
                                if ($log['action_type'] == 'UPDATE') {
                                    $badge_class = 'bg-warning';
                                    $icon = 'fa-edit';
                                } elseif ($log['action_type'] == 'DELETE') {
                                    $badge_class = 'bg-danger';
                                    $icon = 'fa-trash';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <i class="fas <?php echo $icon; ?> me-1"></i>
                                    <?php echo $log['action_type']; ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($log['employee_name'] ?? 'Deleted Employee'); ?></strong>
                                <br>
                                <small class="text-muted">ID: <?php echo $log['employee_id']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($log['changed_by_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo formatDateTime($log['change_timestamp']); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="viewDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Action Type:</strong>
                        <p id="detail_action"></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Employee:</strong>
                        <p id="detail_employee"></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Changed By:</strong>
                        <p id="detail_changed_by"></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Timestamp:</strong>
                        <p id="detail_timestamp"></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6" id="old_values_section">
                        <h6 class="text-danger"><i class="fas fa-minus-circle me-2"></i> Old Values</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre id="old_values" class="mb-0"></pre>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" id="new_values_section">
                        <h6 class="text-success"><i class="fas fa-plus-circle me-2"></i> New Values</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre id="new_values" class="mb-0"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#auditLogsTable').DataTable({
        order: [[0, 'desc']]
    });
});

function viewDetails(log) {
    // Set basic info
    const actionBadges = {
        'INSERT': '<span class="badge bg-info"><i class="fas fa-plus me-1"></i> INSERT</span>',
        'UPDATE': '<span class="badge bg-warning"><i class="fas fa-edit me-1"></i> UPDATE</span>',
        'DELETE': '<span class="badge bg-danger"><i class="fas fa-trash me-1"></i> DELETE</span>'
    };
    
    document.getElementById('detail_action').innerHTML = actionBadges[log.action_type] || log.action_type;
    document.getElementById('detail_employee').textContent = log.employee_name || 'Deleted Employee (ID: ' + log.employee_id + ')';
    document.getElementById('detail_changed_by').textContent = log.changed_by_name || 'Unknown';
    document.getElementById('detail_timestamp').textContent = new Date(log.change_timestamp).toLocaleString();
    
    // Handle old values
    const oldValuesSection = document.getElementById('old_values_section');
    const oldValuesElement = document.getElementById('old_values');
    
    if (log.old_values) {
        try {
            const oldData = JSON.parse(log.old_values);
            oldValuesElement.textContent = JSON.stringify(oldData, null, 2);
            oldValuesSection.style.display = 'block';
        } catch (e) {
            oldValuesElement.textContent = 'Error parsing old values';
        }
    } else {
        oldValuesSection.style.display = 'none';
    }
    
    // Handle new values
    const newValuesSection = document.getElementById('new_values_section');
    const newValuesElement = document.getElementById('new_values');
    
    if (log.new_values) {
        try {
            const newData = JSON.parse(log.new_values);
            newValuesElement.textContent = JSON.stringify(newData, null, 2);
            newValuesSection.style.display = 'block';
        } catch (e) {
            newValuesElement.textContent = 'Error parsing new values';
        }
    } else {
        newValuesSection.style.display = 'none';
    }
    
    // Adjust layout based on action type
    if (log.action_type === 'INSERT') {
        oldValuesSection.classList.remove('col-md-6');
        oldValuesSection.style.display = 'none';
        newValuesSection.classList.remove('col-md-6');
        newValuesSection.classList.add('col-md-12');
    } else if (log.action_type === 'DELETE') {
        newValuesSection.classList.remove('col-md-6');
        newValuesSection.style.display = 'none';
        oldValuesSection.classList.remove('col-md-6');
        oldValuesSection.classList.add('col-md-12');
    } else {
        oldValuesSection.classList.remove('col-md-12');
        oldValuesSection.classList.add('col-md-6');
        newValuesSection.classList.remove('col-md-12');
        newValuesSection.classList.add('col-md-6');
    }
    
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
</script>

<style>
pre {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    font-size: 12px;
    max-height: 400px;
    overflow-y: auto;
}
</style>

<?php include '../includes/footer.php'; ?>
