<?php
require_once 'config.php';

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

    <link rel="apple-touch-icon" href="./img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="152x152" href="./img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="180x180" href="./img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="167x167" href="./img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="./img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./img/PauseTrackerIcon.png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #313131 0%, #000000 100%);
            min-height: 100vh;
            color: white;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            width: 90%;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .title {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            padding-top: 65px;
        }

        .logo-img {
            width: 50px;
            height: 50px;
        }

        .settings-icon {
            width: 30px;
            height: 30px;
            cursor: pointer;
            transition: transform 0.3s ease;
            opacity: 0.8;
            position: fixed;
             top: 20px;
            right: 20px;
        }

        .settings-icon:hover {
            transform: rotate(90deg);
            opacity: 1;
        }

        .user-info {
            position: fixed;    
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            z-index: 100;
        }

        .username {
            font-size: 14px;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            text-align: center;
            margin-top: 60px;
        }

        .clock {
            font-size: 6rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-family: 'Courier New', monospace;
        }

        .status {
            font-size: 2rem;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .status.lesson {
            background: rgba(255,75,75,0.2);
            border-color: rgba(255,75,75,0.3);
        }

        .status.pause {
            background: rgba(75,255,75,0.2);
            border-color: rgba(75,255,75,0.3);
        }

        .status.feierabend {
            background: rgba(255,215,0,0.2);
            border-color: rgba(255,215,0,0.3);
        }

        .countdown-section {
            margin-top: 50px;
            padding: 30px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }

        .next-info {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: rgba(255,255,255,0.9);
        }

        .countdown-time {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .progress-container {
            width: 100%;
            height: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto 15px auto;
            border: 1px solid rgba(255,255,255,0.2);
            max-width: 600px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ea6666, #a24e4b);
            border-radius: 10px;
            transition: width 1s ease;
        }

        .next-start-time {
            font-size: 1.3rem;
            color: rgba(255,255,255,0.8);
            font-weight: bold;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            color: #333;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .close {
            font-size: 30px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #333;
        }

        .time-input-group {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 10px;
        }

        .time-input-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        .time-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .time-inputs input,
        .time-inputs select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .schedule-list {
            margin-top: 20px;
        }

        .schedule-item {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-item.lesson {
            border-left: 4px solid #dc3545;
        }

        .schedule-item.pause {
            border-left: 4px solid #28a745;
        }
        
        .footer {
            color: #ccc;
            padding: 40px 20px;
            text-align: center;
            font-size: 0.9em;
            width: 100%;
            margin-top: auto;
        }
        
        .footer a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: #fff;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .title {
                font-size: 2rem;
            }

            .clock {
                font-size: 3.5rem;
            }

            .status {
                font-size: 1.5rem;
                padding: 15px;
            }

            .countdown-time {
                font-size: 2rem;
            }

            .next-info {
                font-size: 1.2rem;
            }

            .next-start-time {
                font-size: 1.1rem;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }

            .time-inputs {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
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