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
