![Pausen Tracker](assets/img/README/PausenTracker_slayt.png)

#### Diese Webanwendung ist eine funktionale „Pausentracker“-App, die zur Verfolgung und Verwaltung von Pausenzeiten entwickelt wurde.

* **Backend:** Die Anwendung wurde serverseitig mit **PHP** entwickelt.
* **Datenmanagement:** Benutzerdaten und Aufgabenlisten sind auf einer **MySQL-Datenbank** optimiert.
* **Umfang:** Diese Arbeit wurde als **beispielhaftes Projekt** erstellt und verdeutlicht die Logik von Datenbankmanagement sowie dynamischer Content-Erstellung.

## DEMO: [https://pausentracker.infinityfree.me](https://pausentracker.infinityfree.me)

---

# Pausen Tracker - Installationsanleitung

## Voraussetzungen

- PHP 7.4 oder höher
- MariaDB/MySQL 5.7 oder höher
- Apache/Nginx Webserver
- XAMPP, WAMP, MAMP oder ähnliche lokale Entwicklungsumgebung

## Installation

### 1. Dateien einrichten

Kopieren Sie alle Projektdateien in Ihr lokales Webserver-Verzeichnis:

- Bei XAMPP: `C:\xampp\htdocs\PausenTracker\`
- Bei WAMP: `C:\wamp64\www\PausenTracker\`
- Bei MAMP: `/Applications/MAMP/htdocs/PausenTracker/`

### 2. Datenbank erstellen

**Option A: Mit phpMyAdmin**

1. Öffnen Sie phpMyAdmin (normalerweise unter `http://localhost/phpmyadmin`)
2. Klicken Sie auf "SQL" im oberen Menü
3. Kopieren Sie den gesamten Inhalt der Datei `database.sql`
4. Fügen Sie ihn in das SQL-Feld ein und klicken Sie auf "OK"

**Option B: Mit MySQL-Kommandozeile**

```bash
mysql -u root -p < database.sql
```

### 3. Datenbankkonfiguration anpassen (falls nötig)

Öffnen Sie die Datei `config.php` und passen Sie bei Bedarf die Datenbankzugangsdaten an:

```php
define('DB_HOST', 'localhost');   // oder 'localhost:3306' oder ihr Port
define('DB_NAME', 'pausen_tracker');
define('DB_USER', 'root');        // Ihr MySQL-Benutzername
define('DB_PASS', '');            // Ihr MySQL-Passwort
```

### 4. Anwendung starten

1. Starten Sie Ihren lokalen Webserver (Apache) und MySQL/MariaDB
2. Öffnen Sie Ihren Browser
3. Navigieren Sie zu: `http://localhost/PausenTracker/`

## DB Design

![DB Design](assets/img/README/pausentrckr_DB_design.png)
