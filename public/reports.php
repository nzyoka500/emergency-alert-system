<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Aggregate data for reports
$type_stats = $pdo->query("SELECT at.name, COUNT(a.id) as count 
                           FROM alert_types at 
                           LEFT JOIN alerts a ON at.id = a.alert_type_id 
                           GROUP BY at.name")->fetchAll();

$monthly_stats = $pdo->query("SELECT DATE_FORMAT(created_at, '%M') as month, COUNT(id) as count 
                              FROM alerts 
                              GROUP BY month 
                              ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="row">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="col-lg-10 p-4">
            <h2 class="fw-bold mb-4">Reports</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">Alerts by Category</div>
                        <div class="card-body">
                            <canvas id="typeReportChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">Monthly Alert Volume</div>
                        <div class="card-body">
                            <canvas id="monthlyReportChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Type Chart
    new Chart(document.getElementById('typeReportChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($type_stats, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($type_stats, 'count')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
            }]
        }
    });

    // Monthly Chart
    new Chart(document.getElementById('monthlyReportChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_reverse(array_column($monthly_stats, 'month'))); ?>,
            datasets: [{
                label: 'Alerts',
                data: <?php echo json_encode(array_reverse(array_column($monthly_stats, 'count'))); ?>,
                borderColor: '#667eea',
                fill: false
            }]
        }
    });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>