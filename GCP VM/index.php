<?php
/**
 * Flood Shield Dashboard - Final Optimized Edition
 * Location: /var/www/html/index.php
 */

// --- CONFIGURATION ---
date_default_timezone_set('Asia/Kuala_Lumpur');
$upload_dir = "uploads/";

// Database Configuration
$servername = "localhost";
$username = "flood_user";
$password = "your_password"; 
$dbname = "flood_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// 1. Fetch latest sensor data
$latest_sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$latest_result = $conn->query($latest_sql);
$row = $latest_result->fetch_assoc();

// 2. Fetch log history (Table)
$log_sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 10";
$log_result = $conn->query($log_sql);

// 3. Fetch Chart Data (Last 20 records)
$chart_sql = "SELECT timestamp, water_analog, distance_cm FROM sensor_data ORDER BY timestamp DESC LIMIT 20";
$chart_res = $conn->query($chart_sql);
$chart_data = [];
while($c = $chart_res->fetch_assoc()) { 
    $chart_data[] = $c; 
}
$chart_data = array_reverse($chart_data);

// Prepare data for JS
$labels = json_encode(array_column($chart_data, 'timestamp'));
$waters = json_encode(array_column($chart_data, 'water_analog'));
$dists  = json_encode(array_column($chart_data, 'distance_cm'));

// Flood Status Logic
$is_flooding = ($row['distance_cm'] < 6 || $row['water_analog'] > 2000);
$status_text = $is_flooding ? "FLOOD DETECTED" : "SYSTEM NORMAL";
$status_color = $is_flooding ? "#d9534f" : "#28a745"; 

// 4. Camera Logic
$files = glob($upload_dir . "*.jpg");
$latest_image = null;
if ($files) {
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $latest_image = $files[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flood Shield Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta http-equiv="refresh" content="10"> 
    
    <style>
        :root {
            --bg-body: #f0f2f5;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { background-color: var(--bg-body); color: #333; font-family: 'Inter', system-ui, sans-serif; }
        
        .ai-badge { 
            background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; 
            padding: 6px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; 
        }
        
        .status-banner { 
            background: <?php echo $status_color; ?>; color: white; padding: 20px; 
            border-radius: 15px; text-align: center; margin-bottom: 25px;
        }

        .stat-card { 
            background: white; border-radius: 12px; border: none;
            box-shadow: var(--card-shadow); padding: 15px;
            border-left: 6px solid #0d6efd;
            height: 100%;
        }

        /* FIXED: Chart Container to stop "extending" */
        .chart-wrapper {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: var(--card-shadow); margin-bottom: 30px;
            position: relative; 
            height: 350px; /* Force height */
            width: 100%;   /* Force width */
        }

        .camera-box { 
            background: #1a1a1a; min-height: 300px; display: flex; 
            align-items: center; justify-content: center; border-radius: 12px; 
            overflow: hidden; border: 1px solid #333; position: relative;
            box-shadow: var(--card-shadow);
        }
        .camera-box img { width: 100%; height: auto; object-fit: cover; }
        
        .live-tag {
            position: absolute; top: 15px; left: 15px; background: rgba(220, 53, 69, 0.9);
            color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: bold;
        }

        .table-container { 
            background: white; border-radius: 12px; overflow: hidden; 
            box-shadow: var(--card-shadow); 
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0 text-primary"><i class="fas fa-droplet me-2"></i>Flood Shield</h3>
            <p class="text-muted small m-0">Station: Main Drain B1</p>
        </div>
        <span class="ai-badge"><i class="fas fa-microchip me-1"></i> AI MONITOR ACTIVE</span>
    </div>

    <div class="status-banner shadow">
        <h2 class="fw-bold m-0"><?php echo $status_text; ?></h2>
        <small class="opacity-75"><?php echo $is_flooding ? "Emergency Protocol Initiated" : "Water levels stable"; ?></small>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="chart-wrapper">
                <h6 class="fw-bold text-muted mb-3">SENSORS TREND (LAST 20 READINGS)</h6>
                <canvas id="floodChart"></canvas>
            </div>
        </div>
    </div>
    

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <small class="text-muted">WATER</small>
                        <div class="h4 fw-bold mb-0"><?php echo $row['water_analog']; ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="border-left-color: #6610f2;">
                        <small class="text-muted">DISTANCE</small>
                        <div class="h4 fw-bold mb-0"><?php echo $row['distance_cm']; ?>cm</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="border-left-color: #0dcaf0;">
                        <small class="text-muted">TEMP</small>
                        <div class="h4 fw-bold mb-0"><?php echo number_format($row['temp'], 1); ?>°</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="border-left-color: #20c997;">
                        <small class="text-muted">HUMID</small>
                        <div class="h4 fw-bold mb-0"><?php echo number_format($row['hum'], 0); ?>%</div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover m-0 small">
                    <thead class="table-light">
                        <tr><th>Time</th><th>Water</th><th>Dist</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($log = $log_result->fetch_assoc()): 
                            $log_danger = ($log['distance_cm'] < 6 || $log['water_analog'] > 2000);
                        ?>
                        <tr>
                            <td><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></td>
                            <td class="fw-bold"><?php echo $log['water_analog']; ?></td>
                            <td><?php echo $log['distance_cm']; ?>cm</td>
                            <td><span class="badge bg-<?php echo $log_danger ? 'danger':'success'; ?>"><?php echo $log_danger ? 'Alarm':'Clear'; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="camera-box">
                <?php if ($latest_image): ?>
                    <div class="live-tag">● LIVE FEED</div>
                    <img src="<?php echo $latest_image; ?>?v=<?php echo time(); ?>" alt="Camera">
                <?php else: ?>
                    <div class="text-white opacity-25 text-center"><i class="fas fa-video-slash fa-2x"></i><br>NO SIGNAL</div>
                <?php endif; ?>
            </div>
            <div class="bg-white p-3 rounded-3 mt-3 shadow-sm border small">
                <i class="fas fa-info-circle text-primary me-2"></i> <strong>Site Info:</strong> Main Gate Drain B1<br>
                <i class="fas fa-clock text-muted me-2"></i> <strong>Last Update:</strong> <?php echo date("H:i:s"); ?>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('floodChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo $labels; ?>.map(t => t.split(' ')[1]),
        datasets: [{
            label: 'Water Analog',
            data: <?php echo $waters; ?>,
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            fill: true,
            tension: 0.3,
            yAxisID: 'y'
        }, {
            label: 'Distance (cm)',
            data: <?php echo $dists; ?>,
            borderColor: '#6610f2',
            borderDash: [5, 5],
            tension: 0.3,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // This is key to prevent extending
        scales: {
            y: { type: 'linear', position: 'left', beginAtZero: true },
            y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});
</script>
</body>
</html>
