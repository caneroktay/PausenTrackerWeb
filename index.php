<?php
require_once 'core/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = getCurrentUser();

if (isset($_GET['logout'])) {
    logout();
}

$stmt = $pdo->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_name = 'time_windows'");

$stmt->execute([$user['id']]);
$saved_settings = $stmt->fetch();
$time_windows = $saved_settings ? $saved_settings['setting_value'] : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = $_POST['time_windows'];
    
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, setting_name, setting_value) VALUES (?, 'time_windows', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$user['id'], $settings, $settings]);
    
    $time_windows = $settings;
    echo "<script>alert('Einstellungen gespeichert!');</script>";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pausen Tracker</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="apple-touch-icon" href="assets/img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="152x152" href="assets/img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="167x167" href="assets/img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/PauseTrackerIcon.png">
</head>
<body>
    <div class="content-wrapper" id="app" data-time-windows='<?= json_encode($time_windows ?? []) ?>'>
        <div class="container">
            <div class="header">
                <div></div>
                <div class="user-info">
                    <span class="username"><?= htmlspecialchars($user['username']) ?></span>
                    <a href="?logout=1" class="logout-btn">Abmelden</a>
                </div>
                <h1 class="title">Pausen Tracker</h1>

                <svg class="settings-icon" onclick="openModal()" viewBox="0 0 24 24" fill="white">
                    <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M10,22C9.75,22 9.54,21.82 9.5,21.58L9.13,18.93C8.5,18.68 7.96,18.34 7.44,17.94L4.95,18.95C4.73,19.03 4.46,18.95 4.34,18.73L2.34,15.27C2.21,15.05 2.27,14.78 2.46,14.63L4.57,12.97L4.5,12L4.57,11.03L2.46,9.37C2.27,9.22 2.21,8.95 2.34,8.73L4.34,5.27C4.46,5.05 4.73,4.96 4.95,5.05L7.44,6.05C7.96,5.66 8.5,5.32 9.13,5.07L9.5,2.42C9.54,2.18 9.75,2 10,2H14C14.25,2 14.46,2.18 14.5,2.42L14.87,5.07C15.5,5.32 16.04,5.66 16.56,6.05L19.05,5.05C19.27,4.96 19.54,5.05 19.66,5.27L21.66,8.73C21.79,8.95 21.73,9.22 21.54,9.37L19.43,11.03L19.5,12L19.43,12.97L21.54,14.63C21.73,14.78 21.79,15.05 21.66,15.27L19.66,18.73C21.54,18.95 19.27,19.04 19.05,18.95L16.56,17.95C16.04,18.34 15.5,18.68 14.87,18.93L14.5,21.58C14.46,21.82 14.25,22 14,22H10M11.25,4L10.88,6.61C9.68,6.86 8.62,7.5 7.85,8.39L5.44,7.35L4.69,8.65L6.8,10.2C6.4,11.37 6.4,12.64 6.8,13.8L4.68,15.36L5.43,16.66L7.86,15.62C8.63,16.5 9.68,17.14 10.87,17.38L11.24,20H12.76L13.13,17.39C14.32,17.14 15.37,16.5 16.14,15.62L18.57,16.66L19.32,15.36L17.2,13.81C17.6,12.64 17.6,11.37 17.2,10.2L19.31,8.65L18.56,7.35L16.15,8.39C15.38,7.5 14.32,6.86 13.12,6.62L12.75,4H11.25Z"/>
                </svg>
            </div>

            <div class="main-content">
                <div class="clock" id="clock">00:00:00</div>
                <div class="status" id="status">Jetzt ist Pausezeit</div>
            </div>

            <div class="countdown-section">
                <div class="next-info" id="nextInfo">Nächste Aktivität:</div>
                <div class="countdown-time" id="countdownTime">--:--:--</div>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar" style="width: 0"></div>
                </div>
                <div class="next-start-time" id="nextStartTime">Beginn: --:--</div>
            </div>
        </div>

        <!-- Modal -->
        <div id="settingsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Zeiteinstellungen</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>

                <button class="btn btn-primary" onclick="setGFNStandard()">GFN Standard</button>

                <div class="time-input-group">
                    <label>Neues Zeitfenster hinzufügen:</label>
                    <div class="time-inputs">
                        <select id="timeType">
                            <option value="lesson">Unterricht</option>
                            <option value="pause">Pause</option>
                        </select>
                        <input type="time" id="startTime" placeholder="Beginn">
                        <input type="time" id="endTime" placeholder="Ende">
                        <button class="btn btn-success" onclick="addTimeSlot()">Hinzufügen</button>
                    </div>
                </div>

                <div class="schedule-list">
                    <h3>Aktuelle Zeitfenster:</h3>
                    <div id="scheduleDisplay"></div>
                </div>

                <button class="btn btn-primary" onclick="saveSettings()">Speichern</button>
            </div>
        </div>
    </div>

    <?php require_once 'components/footerComponent.php'; ?>

</body>
<script src="js/main.js"></script>
</html>