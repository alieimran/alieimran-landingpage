import Alpine from 'alpinejs';
import { compressImageInputs } from './compress-images';

window.Alpine = Alpine;

Alpine.start();

compressImageInputs();

const profilePhotoDialog = document.getElementById('profile-photo-dialog');
const profilePhotoButton = document.querySelector('[data-profile-photo-open]');

if (profilePhotoDialog && profilePhotoButton) {
    profilePhotoButton.addEventListener('click', () => {
        profilePhotoDialog.showModal();
        document.documentElement.classList.add('overflow-hidden');
    });

    profilePhotoDialog.addEventListener('close', () => {
        document.documentElement.classList.remove('overflow-hidden');
        profilePhotoButton.focus();
    });

    profilePhotoDialog.addEventListener('click', (event) => {
        const bounds = profilePhotoDialog.getBoundingClientRect();
        if (event.target === profilePhotoDialog && (
            event.clientX < bounds.left || event.clientX > bounds.right ||
            event.clientY < bounds.top || event.clientY > bounds.bottom
        )) {
            profilePhotoDialog.close();
        }
    });
}

// Maintenance suggestions use listeners so they work with the existing CSP.
const maintenanceReason = document.getElementById('maintenance_reason');
document.querySelectorAll('[data-maintenance-reason]').forEach((button) => {
    button.addEventListener('click', () => {
        if (maintenanceReason) {
            maintenanceReason.value = button.dataset.maintenanceReason;
            maintenanceReason.focus();
        }
    });
});
const maintenanceDuration = document.getElementById('maintenance_duration');
const maintenanceUntil = document.getElementById('maintenance_until');
if (maintenanceDuration && maintenanceUntil) {
    maintenanceDuration.addEventListener('change', () => {
        const minutes = Number(maintenanceDuration.value);
        if (!minutes) return;
        const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Asia/Kuala_Lumpur', year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
        }).formatToParts(new Date(Date.now() + minutes * 60000)).map(({type, value}) => [type, value]));
        maintenanceUntil.value = parts.year + '-' + parts.month + '-' + parts.day + 'T' + parts.hour + ':' + parts.minute;
    });
    maintenanceUntil.addEventListener('input', () => { maintenanceDuration.value = ''; });
}

const countdown = document.querySelector('[data-maintenance-countdown]');
if (countdown) {
    const display = countdown.querySelector('[data-countdown-display]');
    const until = Date.parse(countdown.dataset.until);
    const serverNow = Date.parse(countdown.dataset.serverNow);
    const started = performance.now();
    let timer;
    const tick = () => {
        // Use server time plus elapsed time so a wrong device clock cannot distort the estimate.
        const seconds = Math.max(0, Math.ceil((until - serverNow - (performance.now() - started)) / 1000));
        if (!Number.isFinite(seconds)) return;
        if (seconds === 0) {
            display.textContent = countdown.dataset.expiredMessage;
            display.classList.remove('text-2xl', 'sm:text-3xl');
            display.classList.add('text-sm');
            clearInterval(timer);
            return;
        }
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor(seconds % 86400 / 3600);
        const minutes = Math.floor(seconds % 3600 / 60);
        const remaining = seconds % 60;
        display.textContent = (days ? days + 'd ' : '') + [hours, minutes, remaining].map((part) => String(part).padStart(2, '0')).join(':');
    };
    timer = setInterval(tick, 1000);
    tick();
    window.addEventListener('pagehide', () => clearInterval(timer), { once: true });
}
