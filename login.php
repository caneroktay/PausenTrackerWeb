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
    <link rel="stylesheet" href="css/main.css">
    <link rel="apple-touch-icon" href="assets/img/PauseTrackerIcon.png">
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

    <?php require_once 'components/footerComponent.php'; ?>
</body>
<script src="js/main.js"></script>
</html>
