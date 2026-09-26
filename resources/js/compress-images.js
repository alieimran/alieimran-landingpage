// Shrinks photos in the browser before upload, for file inputs marked
// with `data-compress-images`. Full-size phone photos (often 5–10 MB
// each) would otherwise blow through the host's upload/POST size limit
// when several are sent at once. The server still re-compresses every
// image, so this is purely about getting them there. It also turns
// formats the server can't read (e.g. iPhone HEIC, where the browser
// can decode it) into JPEG.

const MAX_EDGE = 2560;
const QUALITY = 0.9;

async function shrink(file) {
    if (!file.type.startsWith('image/') || file.type === 'image/gif') {
        return file;
    }

    try {
        const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
        const scale = Math.min(1, MAX_EDGE / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', QUALITY));

        if (!blob || (blob.size >= file.size && /^image\/(jpeg|png|webp)$/.test(file.type))) {
            return file;
        }

        const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';

        return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
    } catch {
        // Browser can't decode it; send the original and let the
        // server's validation report the problem.
        return file;
    }
}

export function compressImageInputs() {
    document.querySelectorAll('input[type=file][data-compress-images]').forEach((input) => {
        const submit = input.form?.querySelector('button[type=submit], button:not([type])');

        input.addEventListener('change', async () => {
            if (!input.files.length || typeof DataTransfer === 'undefined') {
                return;
            }

            if (submit) submit.disabled = true;

            try {
                const files = await Promise.all([...input.files].map(shrink));
                const transfer = new DataTransfer();
                files.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            } finally {
                if (submit) submit.disabled = false;
            }
        });
    });
}
