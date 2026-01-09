<?php
$servername = "localhost";
$username = "flood_user";
$password = "your_password";
$dbname = "flood_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. Fetch the latest single record for the status cards
$latest_sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$latest_result = $conn->query($latest_sql);
$row = $latest_result->fetch_assoc();

// 2. Fetch the last 10 records for the logging table
$log_sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 10";
$log_result = $conn->query($log_sql);

// Flood Status Logic
$is_flooding = ($row['distance_cm'] < 10 || $row['water_analog'] > 2000);
$status_text = $is_flooding ? "FLOOD DETECTED" : "SYSTEM NORMAL";
$status_color = $is_flooding ? "#d9534f" : "#5cb85c"; // Red or Green
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flood System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="5">
    <style>
        body { background-color: #f4f7f6; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .stat-card { background: white; border-radius: 8px; border-left: 5px solid #0275d8; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 20px; }
        .status-banner { background: <?php echo $status_color; ?>; color: white; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 30px; }
        .log-table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .ai-badge { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .location-placeholder { background: #e9ecef; border: 2px dashed #ced4da; height: 250px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Flood Shield Dashboard</h3>
        <div>
            <span class="ai-badge"><i class="fas fa-microchip"></i> AI PROCESSING ACTIVE</span>
        </div>
    </div>

    <div class="status-banner shadow-sm">
        <h2 class="m-0"><?php echo $status_text; ?></h2>
        <small>AI Analysis: <?php echo $is_flooding ? "Emergency protocols active. Gate opened." : "Water levels within safety parameters."; ?></small>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <small class="text-muted text-uppercase fw-bold">Temp</small>
                <h3 class="mb-0"><?php echo number_format($row['temp'], 1); ?>°C</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-left-color: #5bc0de;">
                <small class="text-muted text-uppercase fw-bold">Humidity</small>
                <h3 class="mb-0"><?php echo number_format($row['hum'], 1); ?>%</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-left-color: #f0ad4e;">
                <small class="text-muted text-uppercase fw-bold">Water Level</small>
                <h3 class="mb-0"><?php echo $row['water_analog']; ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-left-color: #6200ea;">
                <small class="text-muted text-uppercase fw-bold">Distance</small>
                <h3 class="mb-0"><?php echo $row['distance_cm']; ?>cm</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">System Activity Logs</h5>
                <span class="text-muted small">Auto-refreshing...</span>
            </div>
            <div class="log-table">
                <table class="table table-hover m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Temp</th>
                            <th>Water</th>
                            <th>Dist</th>
                            <th>Logic Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log = $log_result->fetch_assoc()): ?>
                        <?php 
                            $log_danger = ($log['distance_cm'] < 10 || $log['water_analog'] > 2000);
                        ?>
                        <tr>
                            <td class="small"><?php echo date('H:i:s', strtotime($log['timestamp'])); ?></td>
                            <td><?php echo $log['temp']; ?>°</td>
                            <td><?php echo $log['water_analog']; ?></td>
                            <td><?php echo $log['distance_cm']; ?>cm</td>
                            <td>
                                <span class="badge <?php echo $log_danger ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $log_danger ? 'Alarm' : 'Clear'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-4">
            <h5 class="mb-3">Site Monitoring</h5>
            <div class="location-placeholder">
                <div class="text-center text-muted">
                    <p class="mb-0">Latest Image</p>
                    <small>(Camera Feed Offline)</small>
                </div>
            </div>
            <div class="mt-3 p-3 bg-white rounded shadow-sm border small">
                <strong>Location:</strong> Main Gate Drain B1<br>
                <strong>Last Analysis:</strong> <?php echo $row['timestamp']; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
