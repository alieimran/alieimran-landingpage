// Ticking a checklist item updates the page in place; the change is saved in the background.
// Without JavaScript (or if the request fails) the form posts normally and the page reloads, so nothing is lost.
const DONE = ['border-emerald-600', 'bg-emerald-600', 'text-white'];
const TODO = ['border-gray-400', 'dark:border-gray-600', 'hover:border-emerald-500'];
const TITLE_DONE = ['text-gray-500', 'dark:text-gray-500', 'line-through'];
const TITLE_TODO = ['text-gray-900', 'dark:text-gray-100'];
const PILL_DONE = ['bg-emerald-100', 'text-emerald-800', 'dark:bg-emerald-950', 'dark:text-emerald-300'];
const PILL_TODO = ['bg-gray-100', 'text-gray-700', 'dark:bg-gray-800', 'dark:text-gray-300'];
const CHECK = '<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>';

function swap(el, remove, add) {
    el.classList.remove(...remove);
    el.classList.add(...add);
}

function paintItem(button, done) {
    const item = button.closest('li');
    button.setAttribute('aria-checked', done ? 'true' : 'false');
    button.setAttribute('aria-label', (done ? 'Mark not done' : 'Mark done') + ': ' + button.dataset.title);
    swap(button, done ? TODO : DONE, done ? DONE : TODO);
    button.innerHTML = done ? CHECK : '';
    const title = item.querySelector('[data-item-title]');
    if (title) swap(title, done ? TITLE_TODO : TITLE_DONE, done ? TITLE_DONE : TITLE_TODO);
}

function paintProgress(section, progress) {
    const total = progress ? progress.total : section.querySelectorAll('button[role="checkbox"]').length;
    const done = progress ? progress.done : section.querySelectorAll('button[role="checkbox"][aria-checked="true"]').length;
    const percent = progress ? progress.percent : (total ? Math.round((done / total) * 100) : 0);
    const text = section.querySelector('[data-progress-text]');
    const bar = section.querySelector('[data-progress-bar]');
    const fill = section.querySelector('[data-progress-fill]');
    if (text) {
        text.textContent = done + '/' + total;
        const complete = total > 0 && done === total;
        swap(text, complete ? PILL_TODO : PILL_DONE, complete ? PILL_DONE : PILL_TODO);
    }
    if (bar) bar.setAttribute('aria-valuenow', String(percent));
    if (fill) fill.style.width = percent + '%';
}

function initCopyButtons() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) return;
        const text = button.dataset.copy;
        try {
            await navigator.clipboard.writeText(text);
        } catch (error) {
            // Older browsers / non-secure contexts: select-and-copy fallback.
            const area = document.createElement('textarea');
            area.value = text;
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            try { document.execCommand('copy'); } catch (e) { /* nothing more to try */ }
            area.remove();
        }
        const original = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => { button.textContent = original; }, 1600);
    });
}

export default function initChecklists() {
    initCopyButtons();
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-checklist-toggle]');
        if (!form || !window.fetch) return;

        const button = form.querySelector('button[role="checkbox"]');
        if (!button) return;
        event.preventDefault();
        if (button.dataset.busy) return;
        button.dataset.busy = '1';

        const section = form.closest('section, article');
        const wasDone = button.getAttribute('aria-checked') === 'true';

        // Optimistic: show the new state immediately, reconcile with the server's answer.
        paintItem(button, !wasDone);
        paintProgress(section, null);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || '',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ _method: 'PATCH' }),
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            paintItem(button, data.is_done);
            paintProgress(section, data.progress);
        } catch (error) {
            // Undo the optimistic change, then fall back to a normal form post so the user still gets a result.
            paintItem(button, wasDone);
            paintProgress(section, null);
            delete button.dataset.busy;
            form.submit();
            return;
        }
        delete button.dataset.busy;
    });
}
