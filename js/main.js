const appElement = document.getElementById('app');
const timeWindowsData = appElement?.dataset.timeWindows;

let schedule = [];

if (timeWindowsData) {
    try {
        schedule = JSON.parse(timeWindowsData);
    } catch (e) {
        console.error('Fehler beim Parsen der time_windows:', e);
    }
}

// GFN Standardzeiten
const gfnStandard = [
    { type: 'lesson', start: '08:30', end: '10:00' },
    { type: 'pause', start: '10:00', end: '10:30' },
    { type: 'lesson', start: '10:30', end: '12:00' },
    { type: 'pause', start: '12:00', end: '13:00' },
    { type: 'lesson', start: '13:00', end: '14:30' },
    { type: 'pause', start: '14:30', end: '15:00' },
    { type: 'lesson', start: '15:00', end: '16:30' },
];

// Wenn schedule leer ist, GFN-Standard laden
if (schedule.length === 0) {
    schedule = [...gfnStandard];
}

function timeToMinutes(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
}

function formatDuration(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function isAfterAllActivities() {
    if (schedule.length === 0) return false;

    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    const lastActivity = schedule[schedule.length - 1];
    const lastEndTime = timeToMinutes(lastActivity.end);

    return currentMinutes >= lastEndTime;
}

function getNextActivity() {
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    if (isAfterAllActivities()) {
        return null;
    }

    for (let i = 0; i < schedule.length; i++) {
        const slot = schedule[i];
        const startMinutes = timeToMinutes(slot.start);

        if (currentMinutes < startMinutes) {
            return {
                activity: slot,
                startMinutes: startMinutes,
                isToday: true
            };
        }
    }

    if (schedule.length > 0) {
        return {
            activity: schedule[0],
            startMinutes: timeToMinutes(schedule[0].start) + 24 * 60,
            isToday: false
        };
    }

    return null;
}

function getCurrentActivity() {
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    for (let slot of schedule) {
        const startMinutes = timeToMinutes(slot.start);
        const endMinutes = timeToMinutes(slot.end);

        if (currentMinutes >= startMinutes && currentMinutes < endMinutes) {
            return {
                activity: slot,
                startMinutes: startMinutes,
                endMinutes: endMinutes,
                totalDuration: endMinutes - startMinutes,
                elapsed: currentMinutes - startMinutes
            };
        }
    }
    return null;
}

function updateCountdown() {
    const nextActivity = getNextActivity();
    const currentActivity = getCurrentActivity();

    if (nextActivity === null && isAfterAllActivities()) {
        document.getElementById('nextInfo').textContent = 'Feierabend! 🎉';
        document.getElementById('countdownTime').textContent = 'Schönen Feierabend!';
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('nextStartTime').textContent = 'Bis morgen! 👋';
        return;
    }

    if (!nextActivity) {
        document.getElementById('nextInfo').textContent = 'Keine geplante Aktivität gefunden';
        document.getElementById('countdownTime').textContent = '--:--:--';
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('nextStartTime').textContent = 'Beginn: --:--';
        return;
    }

    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes() + now.getSeconds() / 60;

    const remainingMinutes = nextActivity.startMinutes - currentMinutes;
    const remainingSeconds = Math.max(0, Math.floor(remainingMinutes * 60));

    const activityTypeText = nextActivity.activity.type === 'lesson' ? 'Unterricht' : 'Pause';
    const dayText = nextActivity.isToday ? '' : ' (Morgen)';

    document.getElementById('nextInfo').textContent = `Nächster ${activityTypeText}${dayText}:`;
    document.getElementById('countdownTime').textContent = formatDuration(remainingSeconds);
    document.getElementById('nextStartTime').textContent = `Beginn: ${nextActivity.activity.start}`;

    let progressPercentage = 0;

    if (currentActivity) {
        progressPercentage = (currentActivity.elapsed / currentActivity.totalDuration) * 100;
    } else {
        let previousEndTime = 0;

        for (let i = 0; i < schedule.length; i++) {
            const startMinutes = timeToMinutes(schedule[i].start);
            if (startMinutes > currentMinutes) {
                if (i > 0) {
                    previousEndTime = timeToMinutes(schedule[i-1].end);
                }
                break;
            }
        }

        if (previousEndTime > 0) {
            const totalBreakTime = nextActivity.startMinutes - previousEndTime;
            const elapsedBreakTime = currentMinutes - previousEndTime;
            progressPercentage = Math.min(100, (elapsedBreakTime / totalBreakTime) * 100);
        }
    }

    document.getElementById('progressBar').style.width = `${Math.max(0, Math.min(100, progressPercentage))}%`;
}

function updateClock() {
    const now = new Date();
    const timeString = now.toTimeString().split(' ')[0];
    document.getElementById('clock').textContent = timeString;

    const currentTime = now.getHours() * 60 + now.getMinutes();
    const statusElement = document.getElementById('status');

    let currentStatus = 'Jetzt ist Pausezeit';
    let statusClass = 'pause';

    if (isAfterAllActivities()) {
        currentStatus = 'Feierabend! 🎉';
        statusClass = 'feierabend';
    } else {
        for (let slot of schedule) {
            const [startHour, startMin] = slot.start.split(':').map(Number);
            const [endHour, endMin] = slot.end.split(':').map(Number);
            const startTime = startHour * 60 + startMin;
            const endTime = endHour * 60 + endMin;

            if (currentTime >= startTime && currentTime < endTime) {
                if (slot.type === 'lesson') {
                    currentStatus = 'Jetzt ist Unterrichtszeit';
                    statusClass = 'lesson';
                } else {
                    currentStatus = 'Jetzt ist Pausezeit';
                    statusClass = 'pause';
                }
                break;
            }
        }
    }

    statusElement.textContent = currentStatus;
    statusElement.className = 'status ' + statusClass;

    updateCountdown();
}

function openModal() {
    document.getElementById('settingsModal').style.display = 'block';
    displaySchedule();
}

function closeModal() {
    document.getElementById('settingsModal').style.display = 'none';
}

function setGFNStandard() {
    schedule = [...gfnStandard];
    displaySchedule();
    alert('GFN Standardzeiten wurden geladen!');
}

function addTimeSlot() {
    const type = document.getElementById('timeType').value;
    const start = document.getElementById('startTime').value;
    const end = document.getElementById('endTime').value;

    if (!start || !end) {
        alert('Bitte geben Sie Beginn- und Endzeiten ein!');
        return;
    }

    schedule.push({ type, start, end });
    schedule.sort((a, b) => a.start.localeCompare(b.start));

    document.getElementById('startTime').value = '';
    document.getElementById('endTime').value = '';

    displaySchedule();
}

function removeTimeSlot(index) {
    schedule.splice(index, 1);
    displaySchedule();
}

function displaySchedule() {
    const display = document.getElementById('scheduleDisplay');
    display.innerHTML = '';

    schedule.forEach((slot, index) => {
        const div = document.createElement('div');
        div.className = `schedule-item ${slot.type}`;
        div.innerHTML = `
                    <span>${slot.type === 'lesson' ? 'Unterricht' : 'Pause'}: ${slot.start} - ${slot.end}</span>
                    <button class="btn btn-danger" onclick="removeTimeSlot(${index})">Löschen</button>
                `;
        display.appendChild(div);
    });
}

function saveSettings() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
                <input type="hidden" name="save_settings" value="1">
                <input type="hidden" name="time_windows" value='${JSON.stringify(schedule)}'>
            `;
    document.body.appendChild(form);
    form.submit();
}

window.onclick = function(event) {
    const modal = document.getElementById('settingsModal');
    if (event.target === modal) {
        closeModal();
    }
}

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

setInterval(updateClock, 1000);
updateClock();