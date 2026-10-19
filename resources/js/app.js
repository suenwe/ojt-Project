const confirmationDialog = document.querySelector('.confirmation-dialog');
const confirmationTitle = confirmationDialog?.querySelector('#confirmation-title');
const confirmationDescription = confirmationDialog?.querySelector('#confirmation-description');
const confirmationSubmit = confirmationDialog?.querySelector('[data-confirm-submit]');
const confirmationCancel = confirmationDialog?.querySelector('[data-confirm-cancel]');
let pendingForm = null;
let pendingSubmitter = null;

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');

    if (!form) {
        return;
    }

    if (!confirmationDialog || typeof confirmationDialog.showModal !== 'function') {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }

        return;
    }

    event.preventDefault();
    pendingForm = form;
    pendingSubmitter = event.submitter;

    confirmationTitle.textContent = form.dataset.confirmTitle || 'Are you sure?';
    confirmationDescription.textContent = form.dataset.confirm;
    confirmationSubmit.textContent = form.dataset.confirmAction || 'Continue';
    confirmationSubmit.classList.toggle('button-danger', form.dataset.confirmAction !== 'Restore access');
    confirmationSubmit.classList.toggle('button-primary', form.dataset.confirmAction === 'Restore access');
    confirmationDialog.showModal();
});

confirmationCancel?.addEventListener('click', () => {
    confirmationDialog.close();
});

confirmationDialog?.addEventListener('close', () => {
    pendingForm = null;
    pendingSubmitter = null;
});

confirmationSubmit?.addEventListener('click', () => {
    const form = pendingForm;
    const submitter = pendingSubmitter;

    if (!form) {
        return;
    }

    const confirmation = form.dataset.confirm;
    delete form.dataset.confirm;
    confirmationDialog.close();

    try {
        if (submitter && submitter.form === form) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    } finally {
        form.dataset.confirm = confirmation;
    }
});

document.querySelectorAll('[data-table-search]').forEach((input) => {
    input.addEventListener('input', () => {
        const query = input.value.trim().toLocaleLowerCase();
        const rows = input.closest('.panel').querySelectorAll('[data-searchable-rows] tr');

        rows.forEach((row) => {
            row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
        });
    });
});

const composerPrompt = document.querySelector('[data-composer-prompt]');
const composerForm = document.querySelector('[data-composer-form]');

if (composerPrompt && composerForm) {
    const openComposer = (focusSelector) => {
        composerPrompt.hidden = true;
        composerForm.hidden = false;
        composerForm.querySelector(focusSelector)?.focus();
    };

    composerPrompt.querySelector('[data-composer-open]').addEventListener('click', () => openComposer('#post-content'));

    composerForm.querySelector('#post-media').addEventListener('change', (event) => {
        if (event.target.files.length > 0) {
            openComposer('#post-content');
        }
    });

    composerForm.querySelector('[data-composer-cancel]').addEventListener('click', (event) => {
        event.preventDefault();
        composerForm.querySelector('form').reset();
        composerForm.hidden = true;
        composerPrompt.hidden = false;
    });
}

const themeToggle = document.querySelector('.theme-toggle');

if (themeToggle) {
    themeToggle.checked = document.documentElement.dataset.theme === 'dark';

    themeToggle.addEventListener('change', () => {
        const theme = themeToggle.checked ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;

        try {
            localStorage.setItem('knowvia-theme', theme);
        } catch (error) {
            // Storage can be blocked; the choice then lasts until the page is left.
        }
    });
}
