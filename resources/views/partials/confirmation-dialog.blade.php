{{-- The "Are you sure?" pop-up used by every form with data-confirm (filled in by app.js). --}}
<dialog class="confirmation-dialog member-confirm" aria-labelledby="confirmation-title" aria-describedby="confirmation-description">
    <div class="confirmation-content">
        <span class="confirmation-icon" aria-hidden="true">!</span>
        <h2 id="confirmation-title">Are you sure?</h2>
        <p id="confirmation-description"></p>
        <div class="confirmation-actions">
            <button class="btn secondary" type="button" data-confirm-cancel>Cancel</button>
            <button class="btn danger" type="button" data-confirm-submit>Continue</button>
        </div>
    </div>
</dialog>
