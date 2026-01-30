<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Login 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Benutzername und Passwort sind erforderlich.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Benutzername oder Passwort falsch.';
        }
    }
}

// Register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $password_confirm = $_POST['reg_password_confirm'];
    
    if (empty($username) || empty($password)) {
        $error = 'Benutzername und Passwort sind erforderlich.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwörter stimmen nicht überein.';
    } elseif (strlen($password) < 6) {
        $error = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
    } else {
        // Benutzername-Kontrolle
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = 'Dieser Benutzername wird bereits verwendet.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.';
            } else {
                $error = 'Während der Registrierung ist ein Fehler aufgetreten.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pausen Tracker - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background:  linear-gradient(180deg, #203e5c 0%, #6f7b87 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
            margin: 0;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex: 1;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 500px;
            margin-top: 50px;
        }

        .login-left {
            flex: 1;
            background: white;
            padding: 60px 40px;
            color: #1d3757;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left img {
            width: 120px;
            height: 120px;
            margin-bottom: 30px;
        }

        .login-left h1 {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .login-left p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
        }

        .tab-buttons {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-button {
            flex: 1;
            padding: 12px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: #999;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .tab-button.active {
            color: #667eea;
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
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
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
	<link rel="apple-touch-icon" sizes="152x152" href="assets/img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/PauseTrackerIcon.png">
	<link rel="apple-touch-icon" sizes="167x167" href="assets/img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="assets/img/PauseTrackerIcon.png">
	<link rel="icon" type="image/png" sizes="16x16" href="assets/img/PauseTrackerIcon.png">
</head>
<body>
    <div class="content-wrapper">
        <div class="login-container">
            <div class="login-left">
            <img src="assets/img/PauseTrackerIcon.png" alt=" Icon">
            <h1>Pausen Tracker</h1>
            <p>Pausenzeiten und Unterrichtszeiten verfolgen. Ihre persönliche Zeitmanagement-Lösung.</p>
        </div>

        <div class="login-right">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('login')">Anmelden</button>
                <button class="tab-button" onclick="switchTab('register')">Registrieren</button>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <div id="login-tab" class="tab-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Benutzername</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Passwort</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn">Anmelden</button>
                </form>
            </div>

            <!-- Register Form -->
            <div id="register-tab" class="tab-content">
                <form method="POST">
                    <div class="form-group">
                        <label for="reg_username">Benutzername</label>
                        <input type="text" id="reg_username" name="reg_username" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_email">E-Mail (optional)</label>
                        <input type="email" id="reg_email" name="reg_email">
                    </div>
                    <div class="form-group">
                        <label for="reg_password">Passwort</label>
                        <input type="password" id="reg_password" name="reg_password" required>
                    </div>
                    <div class="form-group">
                        <label for="reg_password_confirm">Passwort bestätigen</label>
                        <input type="password" id="reg_password_confirm" name="reg_password_confirm" required>
                    </div>
                    <button type="submit" name="register" class="btn">Registrieren</button>
                </form>
            </div>
        </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Tab-Buttons aktualisieren
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Tab-Inhalte aktualisieren
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Aktiven Tab anzeigen
            if (tab === 'login') {
                document.querySelector('.tab-button:first-child').classList.add('active');
                document.getElementById('login-tab').classList.add('active');
            } else {
                document.querySelector('.tab-button:last-child').classList.add('active');
                document.getElementById('register-tab').classList.add('active');
            }
        }
    </script>
    <footer class="footer">
        <div class="footer-content">
            <p>2025 Pausen Tracker</p>
            <p>
                <small>Diese Seite wurde als persönliches Bildungsprojekt konzipiert. <u>Alle Daten werden in der Datenbank gespeichert.</u></small>
            </p>
             <p>
                <small>© caneroktay.com All Rights Received.</small>
            </p>
        </div>
    </footer>
</body>
</html>
