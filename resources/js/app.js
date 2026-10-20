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

const scheduleCalendar = document.querySelector('[data-schedule-calendar]');
const scheduleToggle = document.querySelector('[data-schedule-toggle]');
const scheduleDataElement = document.getElementById('schedule-calendar-data');

if (scheduleCalendar && scheduleToggle && scheduleDataElement) {
    const { today, posts } = JSON.parse(scheduleDataElement.textContent);
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const [todayYear, todayMonth] = today.split('-').map(Number);
    let shownYear = todayYear;
    let shownMonth = todayMonth - 1;

    const grid = scheduleCalendar.querySelector('[data-calendar-grid]');
    const title = scheduleCalendar.querySelector('[data-calendar-title]');
    const previousButton = scheduleCalendar.querySelector('[data-calendar-prev]');
    const nextButton = scheduleCalendar.querySelector('[data-calendar-next]');

    const dayDialog = document.getElementById('schedule-day-dialog');
    const dayLabel = dayDialog.querySelector('[data-day-label]');
    const dayList = dayDialog.querySelector('[data-day-list]');
    const dayItemTemplate = document.getElementById('schedule-day-item');

    const formDialog = document.getElementById('schedule-form-dialog');
    const form = formDialog.querySelector('[data-schedule-form]');
    const formTitle = formDialog.querySelector('[data-form-title]');
    const formDate = formDialog.querySelector('[data-form-date]');
    const formSubmit = formDialog.querySelector('[data-form-submit]');
    const methodField = form.querySelector('[data-method-field]');
    const editingIdField = form.querySelector('[data-editing-id]');
    const scheduledAtField = form.querySelector('[data-scheduled-at]');
    const timeField = form.querySelector('[data-schedule-time]');
    const contentField = form.querySelector('#schedule-content');
    const contentCount = form.querySelector('[data-content-count]');
    const mediaLabel = form.querySelector('[data-media-label]');
    const mediaHint = form.querySelector('[data-media-hint]');
    const defaultMediaHint = mediaHint.textContent;

    let selectedDate = scheduledAtField.value ? scheduledAtField.value.slice(0, 10) : today;

    const pad = (number) => String(number).padStart(2, '0');
    const postsOn = (date) => posts.filter((post) => post.date === date);
    const readableTime = (time) => {
        const [hours, minutes] = time.split(':').map(Number);

        return `${hours % 12 || 12}:${pad(minutes)} ${hours < 12 ? 'AM' : 'PM'}`;
    };
    const updateCount = () => {
        contentCount.textContent = `${contentField.value.length} / 1200`;
    };

    const renderCalendar = () => {
        title.textContent = `${monthNames[shownMonth]} ${shownYear}`;
        previousButton.disabled = shownYear === todayYear && shownMonth === todayMonth - 1;
        grid.replaceChildren();

        const firstWeekday = new Date(shownYear, shownMonth, 1).getDay();
        const daysInMonth = new Date(shownYear, shownMonth + 1, 0).getDate();
        const daysInPreviousMonth = new Date(shownYear, shownMonth, 0).getDate();

        for (let index = firstWeekday - 1; index >= 0; index--) {
            const filler = document.createElement('span');
            filler.className = 'calendar-day is-outside';
            filler.textContent = daysInPreviousMonth - index;
            grid.append(filler);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = `${shownYear}-${pad(shownMonth + 1)}-${pad(day)}`;
            const dayPosts = postsOn(date);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'calendar-day';
            button.dataset.date = date;
            button.disabled = date < today;
            button.classList.toggle('is-today', date === today);
            button.setAttribute('aria-label', dayPosts.length
                ? `${date}, ${dayPosts.length} scheduled ${dayPosts.length === 1 ? 'post' : 'posts'}`
                : date);

            const number = document.createElement('span');
            number.textContent = day;
            button.append(number);

            if (dayPosts.length) {
                const badge = document.createElement('span');
                badge.className = 'calendar-badge';
                badge.textContent = 'Post';
                button.append(badge);
            }

            grid.append(button);
        }
    };

    const setCalendarOpen = (isOpen) => {
        scheduleCalendar.hidden = !isOpen;
        scheduleToggle.setAttribute('aria-expanded', String(isOpen));
        scheduleToggle.classList.toggle('is-active', isOpen);
    };

    const openForm = (date, post = null) => {
        selectedDate = date;
        form.reset();
        formDate.textContent = date;
        formTitle.textContent = post ? 'Edit scheduled post' : 'Schedule a post';
        formSubmit.textContent = post ? 'Save changes' : 'Schedule post';
        form.action = post ? post.updateUrl : form.dataset.storeUrl;
        methodField.disabled = !post;
        editingIdField.value = post ? post.id : '';
        mediaLabel.textContent = post ? 'Replace photos or videos (optional)' : 'Add photo or video (optional)';
        mediaHint.textContent = post && post.mediaCount
            ? `${defaultMediaHint} New files replace the ${post.mediaCount} current ${post.mediaCount === 1 ? 'file' : 'files'}; leave empty to keep them.`
            : defaultMediaHint;
        formDialog.querySelector('.schedule-form-errors')?.remove();
        showCurrentMedia(document.getElementById('schedule-media-preview'), post ? post.media : []);

        if (post) {
            contentField.value = post.text;
            form.querySelector('#schedule-category').value = post.categoryId;
            form.querySelector('#schedule-post-type').value = post.postType ?? 'Discussion';
            form.querySelector('#schedule-hashtags').value = post.hashtag ?? '';
            timeField.value = post.time;
        }

        updateCount();
        formDialog.showModal();
        contentField.focus();
    };

    const openDay = (date) => {
        const dayPosts = postsOn(date);

        if (!dayPosts.length) {
            openForm(date);

            return;
        }

        selectedDate = date;
        dayLabel.textContent = date;
        dayList.replaceChildren(...dayPosts.map((post) => {
            const item = dayItemTemplate.content.firstElementChild.cloneNode(true);
            item.querySelector('[data-item-time]').textContent = readableTime(post.time);
            item.querySelector('[data-item-text]').textContent = post.text;
            item.querySelector('[data-item-hashtag]').textContent = post.hashtag ?? '';
            item.querySelector('[data-item-edit]').addEventListener('click', () => {
                dayDialog.close();
                openForm(date, post);
            });
            item.querySelector('[data-item-delete]').action = post.deleteUrl;

            return item;
        }));
        dayDialog.showModal();
    };

    scheduleToggle.addEventListener('click', (event) => {
        event.preventDefault();
        setCalendarOpen(scheduleCalendar.hidden);
    });

    previousButton.addEventListener('click', () => {
        shownMonth = shownMonth === 0 ? 11 : shownMonth - 1;
        shownYear = shownMonth === 11 ? shownYear - 1 : shownYear;
        renderCalendar();
    });

    nextButton.addEventListener('click', () => {
        shownMonth = shownMonth === 11 ? 0 : shownMonth + 1;
        shownYear = shownMonth === 0 ? shownYear + 1 : shownYear;
        renderCalendar();
    });

    grid.addEventListener('click', (event) => {
        const day = event.target.closest('button.calendar-day');

        if (day && !day.disabled) {
            openDay(day.dataset.date);
        }
    });

    dayDialog.querySelector('[data-day-add]').addEventListener('click', () => {
        dayDialog.close();
        openForm(selectedDate);
    });

    [dayDialog, formDialog].forEach((dialog) => {
        dialog.querySelectorAll('[data-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    document.addEventListener('click', (event) => {
        const clickedInside = [scheduleCalendar, scheduleToggle, dayDialog, formDialog]
            .some((element) => element.contains(event.target));

        if (!scheduleCalendar.hidden && !clickedInside && !dayDialog.open && !formDialog.open) {
            setCalendarOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !scheduleCalendar.hidden && !dayDialog.open && !formDialog.open) {
            setCalendarOpen(false);
        }
    });

    contentField.addEventListener('input', updateCount);

    form.addEventListener('submit', () => {
        scheduledAtField.value = `${selectedDate}T${timeField.value}`;
    });

    const publishDuePosts = () => {
        const duePosts = posts.filter((post) => Date.parse(post.publishAt) <= Date.now());

        if (!duePosts.length) {
            return;
        }

        duePosts.forEach((post) => posts.splice(posts.indexOf(post), 1));
        renderCalendar();

        if (dayDialog.open && duePosts.some((post) => post.date === selectedDate)) {
            dayDialog.close();
        }

        refreshLiveFeed(duePosts.length === 1 ? 'Your scheduled post is now live.' : 'Your scheduled posts are now live.');
    };

    renderCalendar();
    setCalendarOpen(!scheduleCalendar.hidden);
    setInterval(publishDuePosts, 10000);

    if (formDialog.dataset.openOnLoad === 'true') {
        const editingPost = posts.find((post) => String(post.id) === editingIdField.value);
        formDate.textContent = selectedDate;
        setCalendarOpen(true);

        if (editingPost) {
            showCurrentMedia(document.getElementById('schedule-media-preview'), editingPost.media);
            form.action = editingPost.updateUrl;
            methodField.disabled = false;
            formTitle.textContent = 'Edit scheduled post';
            formSubmit.textContent = 'Save changes';
        }

        updateCount();
        formDialog.showModal();
    }
}

/**
 * Photo and video preview slider for post forms (based on pmnw1234's carousel from the post branch).
 * Shows the post's current files first (data-current-media) and the chosen files once the member picks new ones.
 */
function renderMediaCarousel(container, mediaItems) {
    container.replaceChildren();

    if (!mediaItems.length) {
        container.hidden = true;

        return;
    }

    const carousel = document.createElement('div');
    carousel.className = 'media-carousel';
    const track = document.createElement('div');
    track.className = 'media-carousel-track';

    mediaItems.forEach((item) => {
        const slide = document.createElement('div');
        slide.className = 'media-carousel-slide';
        const isVideo = item.type.startsWith('video/');
        const element = document.createElement(isVideo ? 'video' : 'img');
        element.src = item.url;

        if (isVideo) {
            element.controls = true;
            element.playsInline = true;
            element.preload = 'metadata';
        } else {
            element.alt = item.alt ?? '';
            element.loading = 'lazy';
        }

        slide.append(element);
        track.append(slide);
    });
    carousel.append(track);

    if (mediaItems.length > 1) {
        const count = document.createElement('span');
        count.className = 'media-carousel-count';
        count.textContent = `1 / ${mediaItems.length}`;

        const previous = document.createElement('button');
        previous.type = 'button';
        previous.className = 'media-carousel-nav media-carousel-prev';
        previous.setAttribute('aria-label', 'Previous file');
        previous.textContent = '‹';

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'media-carousel-nav media-carousel-next';
        next.setAttribute('aria-label', 'Next file');
        next.textContent = '›';

        const dots = document.createElement('div');
        dots.className = 'media-carousel-dots';
        let currentIndex = 0;

        const dotButtons = mediaItems.map((_, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'media-carousel-dot';
            dot.setAttribute('aria-label', `Show file ${index + 1}`);
            dot.setAttribute('aria-current', String(index === 0));
            dots.append(dot);

            return dot;
        });

        const updatePosition = (index) => {
            currentIndex = Math.max(0, Math.min(mediaItems.length - 1, index));
            count.textContent = `${currentIndex + 1} / ${mediaItems.length}`;
            dotButtons.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === currentIndex)));
        };
        const goToSlide = (index) => {
            updatePosition(index);
            track.scrollTo({ left: currentIndex * track.clientWidth, behavior: 'smooth' });
        };

        dotButtons.forEach((dot, index) => dot.addEventListener('click', () => goToSlide(index)));
        previous.addEventListener('click', () => goToSlide(currentIndex - 1));
        next.addEventListener('click', () => goToSlide(currentIndex + 1));
        track.addEventListener('scroll', () => {
            updatePosition(Math.round(track.scrollLeft / track.clientWidth));
        }, { passive: true });

        carousel.append(count, previous, next, dots);
    }

    container.append(carousel);
    container.hidden = false;
}

function showCurrentMedia(container, mediaItems) {
    container.dataset.currentMedia = JSON.stringify(mediaItems);
    renderMediaCarousel(container, mediaItems);
}

document.querySelectorAll('input[type="file"][data-media-preview]').forEach((input) => {
    const container = document.getElementById(input.dataset.mediaPreview);
    const currentMedia = () => JSON.parse(container.dataset.currentMedia || '[]');
    let chosenFileUrls = [];

    const releaseChosenFiles = () => {
        chosenFileUrls.forEach((url) => URL.revokeObjectURL(url));
        chosenFileUrls = [];
    };

    input.addEventListener('change', () => {
        releaseChosenFiles();

        const files = Array.from(input.files);

        if (!files.length) {
            renderMediaCarousel(container, currentMedia());

            return;
        }

        renderMediaCarousel(container, files.map((file) => {
            const url = URL.createObjectURL(file);
            chosenFileUrls.push(url);

            return { url, type: file.type };
        }));
    });

    input.form?.addEventListener('reset', () => {
        releaseChosenFiles();
        renderMediaCarousel(container, currentMedia());
    });

    renderMediaCarousel(container, currentMedia());
});

function initMediaCarousels(root) {
    root.querySelectorAll('[data-media-carousel]').forEach((container) => {
        renderMediaCarousel(container, JSON.parse(container.dataset.mediaCarousel));
    });
}

initMediaCarousels(document);

/**
 * Reload just the post list of the current page (used when a scheduled post goes live while the page is open).
 */
async function refreshLiveFeed(statusText) {
    const feed = document.querySelector('[data-live-feed]');

    if (!feed) {
        return;
    }

    try {
        const response = await fetch(window.location.href, { headers: { Accept: 'text/html' } });

        if (!response.ok) {
            return;
        }

        const freshPage = new DOMParser().parseFromString(await response.text(), 'text/html');
        const freshFeed = freshPage.querySelector('[data-live-feed]');

        if (!freshFeed) {
            return;
        }

        feed.replaceChildren(...freshFeed.childNodes);
        initMediaCarousels(feed);

        let status = document.querySelector('[data-live-feed-status]');

        if (!status) {
            status = document.createElement('p');
            status.className = 'form-status';
            status.setAttribute('role', 'status');
            status.dataset.liveFeedStatus = '';
            feed.before(status);
        }

        status.textContent = statusText;
    } catch (error) {
        // A failed refresh is harmless; the post still shows after the next page load.
    }
}
