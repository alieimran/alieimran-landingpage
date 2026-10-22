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
