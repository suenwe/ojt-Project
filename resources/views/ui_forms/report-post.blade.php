<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowvia | Report Post Preview</title>
    <link rel="stylesheet" href="{{ asset('assets/prototype.css') }}">

    <style>
        /* Report form preview uses the shared Knowvia stylesheet. */
        .rp-main { display: grid; min-width: 0; gap: 17px; }
        .rp-notice { padding: 12px 14px; color: var(--brand); background: var(--brand-soft); border: 1px solid var(--line); border-radius: 7px; font-size: 12px; }
        .rp-example { padding: 18px; }
        .rp-header { display: flex; align-items: flex-start; gap: 10px; }
        .rp-author { flex: 1; min-width: 0; }
        .rp-author strong { display: block; font-size: 13px; }
        .rp-author small { color: var(--muted); font-size: 11px; }
        .rp-menu-wrap { position: relative; margin-left: auto; flex-shrink: 0; }
        .rp-menu-button { display: grid; place-items: center; width: 36px; height: 36px; padding: 0; color: var(--ink); background: transparent; border: 0; border-radius: 7px; font-size: 24px; line-height: 1; }
        .rp-menu-button:hover, .rp-menu-button[aria-expanded="true"] { background: var(--brand-soft); }
        .rp-menu { position: absolute; top: 40px; right: 0; min-width: 160px; z-index: 12; padding: 5px; background: var(--surface); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px rgb(0 0 0 / 12%); }
        .rp-menu button { display: block; width: 100%; padding: 10px 12px; color: var(--ink); background: transparent; border: 0; border-radius: 5px; text-align: left; font-size: 13px; }
        .rp-menu button:hover { background: var(--brand-soft); }
        .rp-post-copy { margin: 16px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; }
        .rp-post-category { display: inline-block; margin-top: 12px; padding: 3px 9px; border-radius: 20px; color: var(--brand); background: var(--brand-soft); font-size: 11px; font-weight: 700; }
        .rp-dialog { width: min(510px, calc(100% - 28px)); max-height: 90vh; overflow-y: auto; padding: 24px; color: var(--ink); background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 16px 45px rgb(0 0 0 / 20%); }
        .rp-dialog::backdrop { background: rgb(10 16 40 / 60%); }
        .rp-dialog-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
        .rp-dialog-heading h2 { margin-bottom: 3px; font-size: 19px; }
        .rp-close { border: 0; background: transparent; color: var(--muted); font-size: 25px; line-height: 1; padding: 2px 6px; }
        .rp-field-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
        .rp-field-group label { font-size: 13px; font-weight: 700; }
        .rp-field-group .field { display: block; width: 100%; min-height: 44px; margin: 0; }
        .rp-field-group textarea.field { min-height: 110px; resize: vertical; }
        .rp-required { color: var(--red); }
        .rp-count { text-align: right; font-size: 11px; color: var(--muted); }
        .rp-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 9px; margin-top: 16px; }
        .rp-actions .btn { min-width: 105px; }
        .rp-privacy { padding: 10px 12px; color: var(--muted); background: var(--page); border-radius: 7px; font-size: 12px; }
        .rp-result { padding: 13px 15px; color: var(--green); background: var(--green-soft); border-radius: 7px; font-size: 12px; }
        .rp-muted-link { display: block; padding: 9px 11px; color: var(--muted); font-weight: 600; }
        [hidden] { display: none !important; }
        @media (max-width: 700px) {
            .rp-dialog { padding: 18px; }
            .rp-actions .btn { flex: 1; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="#report-preview"><span class="brand-mark">V</span> Knowvia</a>
        <div class="top-actions">
            <a class="top-link" href="/ui-preview/create-post">Create Post</a>
            <span class="avatar" aria-label="Signed-in demo user Alex Morgan">AM</span>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="panel side-panel">
                <p class="side-heading">Community</p>
                <nav class="side-nav">
                    <a class="active" href="#report-preview">Home Feed</a>
                    <span class="rp-muted-link">Explore Topics</span>
                    <a href="/ui-preview/edit-profile">My Profile</a>
                    <a href="/ui-preview/schedule-post">Schedule Post</a>
                </nav>
            </div>
            <div class="panel account-card">
                <span class="avatar">AM</span>
                <div><strong>Alex Morgan</strong><small>Community Member</small></div>
            </div>
        </aside>

        <main class="rp-main" id="report-preview">
            <div class="page-heading">
                <div>
                    <h1>Community Feed</h1>
                    <p class="subtext">Connect and share with your community.</p>
                </div>
            </div>

            

            <h2 style="margin: 0">Latest Posts</h2>

            <!-- Example post from ANOTHER user. Do not connect it to your own Create Post menu. -->
            <article class="panel rp-example" aria-label="Example community post from Jamie Lee">
                <div class="rp-header">
                    <span class="avatar" aria-hidden="true">JL</span>
                    <div class="rp-author">
                        <strong>Jamie Lee</strong>
                        <small>Community Member · 2 hours ago</small>
                    </div>

                    <div class="rp-menu-wrap" id="report-menu-wrap">
                        <button
                            type="button"
                            class="rp-menu-button"
                            id="report-menu-toggle"
                            aria-label="Post options"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-controls="report-menu"
                        >&hellip;</button>
                        <div id="report-menu" class="rp-menu" hidden>
                            <button type="button" id="open-report">Report Post</button>
                        </div>
                    </div>
                </div>
                <span class="rp-post-category">General Discussion</span>
                <p class="rp-post-copy">Hello everyone! I'm sharing this sample post so we can test the report feature in Knowvia.</p>
            </article>

            <p class="rp-result" id="report-result" role="status" hidden></p>
        </main>

        <aside class="rightbar">
            <section class="panel right-panel">
                <h3>Community Guidelines</h3>
                <p class="subtext">Use the Report Post option to flag content for review. This page is a frontend demonstration only.</p>
            </section>
        </aside>
    </div>

    <dialog class="rp-dialog" id="report-dialog" aria-labelledby="report-title">
        <div class="rp-dialog-heading">
            <div>
                <h2 id="report-title">Report Post</h2>
                <p class="subtext">Tell us why you're reporting this post.</p>
            </div>
            <button type="button" class="rp-close" id="close-report" aria-label="Close report form">&times;</button>
        </div>

        <form id="report-form">
            <div class="rp-field-group">
                <label for="report-reason">Report Reason <span class="rp-required">*</span></label>
                <select class="field" id="report-reason" name="reason" required>
                    <option value="">Select a reason</option>
                    <option value="Problem involving someone under 18">Problem involving someone under 18</option>
                    <option value="Bullying, harassment or abuse">Bullying, harassment or abuse</option>
                    <option value="Suicide or self-harm">Suicide or self-harm</option>
                    <option value="Violent, hateful or disturbing content">Violent, hateful or disturbing content</option>
                    <option value="Selling or promoting restricted items">Selling or promoting restricted items</option>
                    <option value="Adult content">Adult content</option>
                    <option value="Scam, fraud or false information">Scam, fraud or false information</option>
                    <option value="Intellectual property">Intellectual property</option>
                    <option value="I don&#x27;t want to see this">I don&#x27;t want to see this</option>
                </select>
            </div>

            <div class="rp-field-group">
                <label for="report-details">Additional Details (Optional)</label>
                <textarea
                    class="field"
                    id="report-details"
                    name="details"
                    maxlength="500"
                    placeholder="Describe your concern (optional)..."
                ></textarea>
                <span class="rp-count" id="report-count">0 / 500</span>
            </div>

            <p class="rp-privacy">Reports will be reviewed by the moderation team once the backend is connected. This preview does not send any report.</p>

            <div class="rp-actions">
                <button type="button" class="btn secondary" id="cancel-report">Cancel</button>
                <button type="submit" class="btn">Submit Report</button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            'use strict';

            const toggle = document.getElementById('report-menu-toggle');
            const menu = document.getElementById('report-menu');
            const menuWrap = document.getElementById('report-menu-wrap');
            const openReport = document.getElementById('open-report');
            const dialog = document.getElementById('report-dialog');
            const closeReport = document.getElementById('close-report');
            const cancelReport = document.getElementById('cancel-report');
            const form = document.getElementById('report-form');
            const reason = document.getElementById('report-reason');
            const details = document.getElementById('report-details');
            const count = document.getElementById('report-count');
            const result = document.getElementById('report-result');

            function setMenuOpen(open) {
                menu.hidden = !open;
                toggle.setAttribute('aria-expanded', String(open));
            }

            function hideReport() {
                if (dialog.open) dialog.close();
            }

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                setMenuOpen(menu.hidden);
            });

            document.addEventListener('click', (event) => {
                if (!menuWrap.contains(event.target)) setMenuOpen(false);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setMenuOpen(false);
            });

            openReport.addEventListener('click', () => {
                setMenuOpen(false);
                form.reset();
                count.textContent = '0 / 500';
                dialog.showModal();
                reason.focus();
            });

            closeReport.addEventListener('click', hideReport);
            cancelReport.addEventListener('click', hideReport);

            dialog.addEventListener('click', (event) => {
                // Allow clicking outside the dialog content to dismiss it.
                if (event.target === dialog) hideReport();
            });

            details.addEventListener('input', () => {
                count.textContent = details.value.length + ' / 500';
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (!form.reportValidity()) return;

                // Frontend-only simulation: NO request to Laravel or the database.
                // A real implementation must POST the post ID, reason and details,
                // include CSRF protection, and enforce authorization on the server.
                result.textContent = 'Report form validated successfully (preview only). No report was submitted to the server.';
                result.hidden = false;
                hideReport();
            });

        })();
    </script>
</body>
</html>
