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

// The gear account menus (member feed and admin dashboard) close when clicking anywhere else or pressing Escape.
document.addEventListener('click', (event) => {
    document.querySelectorAll('details.account-settings[open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('details.account-settings[open]').forEach((menu) => {
            menu.open = false;
        });
    }
});

// Filter menus (admin user management) apply as soon as an option is picked.
document.querySelectorAll('select[data-auto-submit]').forEach((select) => {
    select.addEventListener('change', () => select.form?.requestSubmit());
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

    document.addEventListener('schedule:edit-post', (event) => {
        const post = posts.find((item) => item.id === event.detail.postId);

        if (!post) {
            return;
        }

        event.detail.handled = true;
        setCalendarOpen(true);
        openForm(post.date, post);
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
 * Reload just the post list of the current page (when a scheduled post goes live, or the member pulls down / presses refresh).
 * Returns whether the list was replaced; a failed refresh is harmless, the posts still show after the next page load.
 */
async function refreshLiveFeed(statusText = '') {
    const feed = document.querySelector('[data-live-feed]');

    if (!feed) {
        return false;
    }

    try {
        const response = await fetch(window.location.href, { headers: { Accept: 'text/html' }, cache: 'no-store' });

        if (!response.ok) {
            return false;
        }

        const freshPage = new DOMParser().parseFromString(await response.text(), 'text/html');
        const freshFeed = freshPage.querySelector('[data-live-feed]');

        if (!freshFeed) {
            return false;
        }

        feed.replaceChildren(...freshFeed.childNodes);
        initMediaCarousels(feed);
        watchPostViews(feed);
        filterFeed();
        showFeedStatus(statusText);

        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Show a short message above the post list, or remove the old one when the text is empty.
 */
function showFeedStatus(statusText) {
    const feed = document.querySelector('[data-live-feed]');
    let status = document.querySelector('[data-live-feed-status]');

    if (!statusText) {
        status?.remove();

        return;
    }

    if (!status && feed) {
        status = document.createElement('p');
        status.className = 'form-status';
        status.setAttribute('role', 'status');
        status.dataset.liveFeedStatus = '';
        feed.before(status);
    }

    if (status) {
        status.textContent = statusText;
    }
}

/**
 * Pull down at the top of the feed on a touch screen, or press the refresh button, to load the latest posts.
 * The browser's own pull-to-refresh (which reloads the whole page) is turned off on feed pages in app.css.
 */
const pullIndicator = document.querySelector('[data-pull-refresh]');
const PULL_TRIGGER_DISTANCE = 70;
const PULL_MAX_DISTANCE = 110;
let pullStartY = null;
let pullDistance = 0;
let isFeedRefreshing = false;

function setPullDistance(distance) {
    pullDistance = distance;

    if (!pullIndicator) {
        return;
    }

    pullIndicator.style.setProperty('--pull', `${distance}px`);
    pullIndicator.style.setProperty('--pull-turn', `${distance * 3}deg`);
    pullIndicator.classList.toggle('is-visible', distance > 0);
    pullIndicator.classList.toggle('is-ready', distance >= PULL_TRIGGER_DISTANCE);
}

function hasUnsentComment(feed) {
    return [...feed.querySelectorAll('textarea')].some((box) => box.value !== box.defaultValue);
}

async function runFeedRefresh() {
    const feed = document.querySelector('[data-live-feed]');

    if (!feed || isFeedRefreshing) {
        return;
    }

    if (hasUnsentComment(feed)) {
        setPullDistance(0);
        showFeedStatus('Send or clear your comment first, then refresh.');

        return;
    }

    const refreshButtons = document.querySelectorAll('[data-feed-refresh]');

    isFeedRefreshing = true;
    refreshButtons.forEach((button) => {
        button.disabled = true;
        button.classList.add('is-refreshing');
    });
    pullIndicator?.classList.add('is-refreshing');

    // A pull keeps the icon spinning just below the top bar; the button spins its own icon instead.
    if (pullDistance > 0) {
        setPullDistance(PULL_TRIGGER_DISTANCE);
    }

    // Keep the spinner for at least half a second so a fast answer still shows that something happened.
    const [isRefreshed] = await Promise.all([refreshLiveFeed(), new Promise((resolve) => setTimeout(resolve, 500))]);

    if (!isRefreshed) {
        showFeedStatus('The feed could not be refreshed. Please try again.');
    }

    isFeedRefreshing = false;
    refreshButtons.forEach((button) => {
        button.disabled = false;
        button.classList.remove('is-refreshing');
    });
    pullIndicator?.classList.remove('is-refreshing');
    setPullDistance(0);
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-feed-refresh]')) {
        runFeedRefresh();
    }
});

if (pullIndicator) {
    document.addEventListener('touchstart', (event) => {
        pullStartY = null;

        if (isFeedRefreshing
            || event.touches.length !== 1
            || window.scrollY > 0
            || !document.querySelector('[data-live-feed]')
            || event.target.closest('dialog, textarea, input, select, .media-carousel-track, [data-schedule-calendar]')) {
            return;
        }

        pullStartY = event.touches[0].clientY;
        pullIndicator.style.top = `${document.querySelector('.topbar')?.getBoundingClientRect().bottom ?? 0}px`;
    }, { passive: true });

    document.addEventListener('touchmove', (event) => {
        if (pullStartY === null) {
            return;
        }

        if (window.scrollY > 0) {
            pullStartY = null;
            pullIndicator.classList.remove('is-pulling');
            setPullDistance(0);

            return;
        }

        // The icon moves at half the finger's speed, so it feels like pulling against a spring.
        pullIndicator.classList.add('is-pulling');
        setPullDistance(Math.min(Math.max(0, event.touches[0].clientY - pullStartY) / 2, PULL_MAX_DISTANCE));
    }, { passive: true });

    ['touchend', 'touchcancel'].forEach((type) => {
        document.addEventListener(type, (event) => {
            if (pullStartY === null) {
                return;
            }

            pullStartY = null;
            pullIndicator.classList.remove('is-pulling');

            if (event.type === 'touchend' && pullDistance >= PULL_TRIGGER_DISTANCE) {
                runFeedRefresh();
            } else {
                setPullDistance(0);
            }
        });
    });
}

/**
 * Search box, topic filter and hashtag links: hide the post cards on this page that do not match.
 * Words starting with # only match posts tagged with exactly that hashtag (#test does not match "test" in the text or #testing);
 * other words search post text, hashtags, usernames and topics. Clicking a hashtag searches for it.
 * Pages without a post list send the search to the home page (?search= or ?hashtag=).
 */
const feedSearchForm = document.querySelector('[data-feed-search-form]');
const feedSearchInput = document.querySelector('[data-feed-search]');
const feedCategoryFilter = document.querySelector('[data-feed-category]');

function normalizeHashtag(tag) {
    return `#${String(tag ?? '').trim().replace(/^#+/, '')}`.toLowerCase();
}

function filterFeed() {
    const feed = document.querySelector('[data-live-feed]');

    if (!feed || !feedSearchInput) {
        return;
    }

    const words = feedSearchInput.value.trim().toLowerCase().split(/[\s,]+/).filter((word) => word !== '' && word !== '#');
    const hashtags = words.filter((word) => word.startsWith('#')).map(normalizeHashtag);
    const textWords = words.filter((word) => !word.startsWith('#'));
    const category = feedCategoryFilter?.value ?? '';
    const cards = [...feed.querySelectorAll('[data-post-card]')];

    cards.forEach((card) => {
        const postHashtags = card.dataset.searchHashtags.split(' ');
        const matchesSearch = hashtags.every((tag) => postHashtags.includes(tag))
            && textWords.every((word) => card.dataset.searchText.includes(word));
        const matchesCategory = category === '' || card.dataset.searchCategory === category;

        card.hidden = !(matchesSearch && matchesCategory);
    });

    let emptyMessage = feed.querySelector('[data-feed-search-empty]');

    if (cards.length > 0 && cards.every((card) => card.hidden)) {
        if (!emptyMessage) {
            emptyMessage = document.createElement('p');
            emptyMessage.className = 'panel feed-search-empty';
            emptyMessage.dataset.feedSearchEmpty = '';
            emptyMessage.textContent = 'No posts match your search.';
            feed.append(emptyMessage);
        }
    } else {
        emptyMessage?.remove();
    }
}

function searchHashtag(tag) {
    feedSearchInput.value = normalizeHashtag(tag);

    if (feedCategoryFilter) {
        feedCategoryFilter.value = '';
    }

    filterFeed();
    document.querySelector('[data-live-feed]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

if (feedSearchForm && feedSearchInput) {
    const hasFeed = () => document.querySelector('[data-live-feed]') !== null;

    feedSearchForm.addEventListener('submit', (event) => {
        if (hasFeed()) {
            event.preventDefault();
        }
    });

    feedSearchInput.addEventListener('input', filterFeed);

    feedCategoryFilter?.addEventListener('change', filterFeed);

    // The topic filter is only visible while "By topic" is picked, so leaving that option clears it.
    document.getElementById('post-sort')?.addEventListener('change', (event) => {
        if (feedCategoryFilter && event.target.value !== 'category') {
            feedCategoryFilter.value = '';
            filterFeed();
        }
    });

    document.addEventListener('click', (event) => {
        const hashtagLink = event.target.closest('[data-search-hashtag]');

        if (!hashtagLink) {
            return;
        }

        event.preventDefault();

        if (hasFeed()) {
            searchHashtag(hashtagLink.dataset.searchHashtag);
        } else {
            window.location.href = `${feedSearchForm.action}?hashtag=${encodeURIComponent(hashtagLink.dataset.searchHashtag)}`;
        }
    });

    const searchFromUrl = new URLSearchParams(window.location.search);

    if (searchFromUrl.get('hashtag')) {
        searchHashtag(searchFromUrl.get('hashtag'));
    } else if (searchFromUrl.get('search')) {
        feedSearchInput.value = searchFromUrl.get('search');
        filterFeed();
    }
}

/**
 * Reactions, comments, replies and view counts on post cards.
 * Each action sends the form with fetch() and updates the card from the server's answer, so the page never reloads.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function sendFormInBackground(form, submitter = null) {
    const response = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form, submitter),
    });
    const data = await response.json().catch(() => ({}));

    return { ok: response.ok, data };
}

function setCountText(element, count) {
    if (element) {
        element.textContent = `${count} ${count === 1 ? element.dataset.singular : element.dataset.plural}`;
    }
}

function firstErrorMessage(data) {
    const messages = Object.values(data.errors ?? {}).flat();

    return messages[0] ?? data.message ?? 'Something went wrong. Please try again.';
}

/* Reactions */

function updateReactionControl(control, viewerReaction) {
    const options = [...control.querySelectorAll('.reaction-option')];
    const mainButton = control.querySelector('[data-reaction-main]');
    const chosen = options.find((option) => option.value === viewerReaction);

    options.forEach((option) => option.setAttribute('aria-pressed', String(option === chosen)));
    mainButton.value = viewerReaction ?? 'like';
    mainButton.className = `post-action reaction-main${chosen ? ` is-reacted reaction-${viewerReaction}` : ''}`;
    mainButton.setAttribute('aria-pressed', String(Boolean(chosen)));
    mainButton.querySelector('[data-reaction-main-emoji]').textContent = chosen ? chosen.textContent.trim() : '👍';
    mainButton.querySelector('[data-reaction-main-label]').textContent = chosen ? chosen.title : 'Like';
}

function closeReactionPickers(except = null) {
    document.querySelectorAll('[data-reaction-control].is-open').forEach((control) => {
        if (control !== except) {
            control.classList.remove('is-open');
        }
    });
}

document.addEventListener('submit', async (event) => {
    const control = event.target.closest('[data-reaction-control]');

    if (!control || event.defaultPrevented) {
        return;
    }

    event.preventDefault();
    closeReactionPickers();

    const card = control.closest('[data-post-card]');
    const { ok, data } = await sendFormInBackground(control, event.submitter);

    if (!ok) {
        return;
    }

    updateReactionControl(control, data.viewerReaction);
    setCountText(card.querySelector('[data-reaction-count]'), data.reactionCount);
    card.querySelector('[data-reaction-emojis]').textContent = data.topReactionTypes
        .map((type) => control.querySelector(`.reaction-option[value="${type}"]`)?.textContent.trim() ?? '')
        .join('');
});

let reactionHoverTimer = null;
let reactionLongPressTimer = null;
let ignoreNextReactionClick = false;

document.addEventListener('pointerover', (event) => {
    const control = event.target.closest('[data-reaction-control]');

    if (!control || event.pointerType !== 'mouse') {
        return;
    }

    clearTimeout(reactionHoverTimer);
    reactionHoverTimer = setTimeout(() => {
        closeReactionPickers(control);
        control.classList.add('is-open');
    }, 400);
});

document.addEventListener('pointerout', (event) => {
    const control = event.target.closest('[data-reaction-control]');

    if (!control || event.pointerType !== 'mouse' || control.contains(event.relatedTarget)) {
        return;
    }

    clearTimeout(reactionHoverTimer);
    reactionHoverTimer = setTimeout(() => control.classList.remove('is-open'), 300);
});

document.addEventListener('pointerdown', (event) => {
    const mainButton = event.target.closest('[data-reaction-main]');

    if (!mainButton || event.pointerType === 'mouse') {
        return;
    }

    clearTimeout(reactionLongPressTimer);
    reactionLongPressTimer = setTimeout(() => {
        const control = mainButton.closest('[data-reaction-control]');
        closeReactionPickers(control);
        control.classList.add('is-open');
        ignoreNextReactionClick = true;
    }, 450);
});

['pointerup', 'pointercancel'].forEach((type) => {
    document.addEventListener(type, () => clearTimeout(reactionLongPressTimer));
});

document.addEventListener('contextmenu', (event) => {
    if (event.target.closest('[data-reaction-main]')) {
        event.preventDefault();
    }
});

document.addEventListener('click', (event) => {
    if (ignoreNextReactionClick && event.target.closest('[data-reaction-main]')) {
        event.preventDefault();
        ignoreNextReactionClick = false;

        return;
    }

    if (!event.target.closest('[data-reaction-control]')) {
        closeReactionPickers();
    }
}, true);

/* Comments and replies */

async function openComments(card, focusInput) {
    const section = card.querySelector('[data-comments]');

    if (!section.dataset.loaded) {
        const response = await fetch(section.dataset.commentsUrl, { headers: { Accept: 'text/html' } });

        if (!response.ok) {
            return;
        }

        section.innerHTML = await response.text();
        section.dataset.loaded = 'true';
    }

    section.hidden = false;
    card.querySelectorAll('[data-comments-toggle]').forEach((toggle) => toggle.setAttribute('aria-expanded', 'true'));

    if (focusInput) {
        section.querySelector('.comment-form:not(.reply-form) input[name="content"]')?.focus();
    }
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-comments-toggle]');

    if (toggle) {
        const card = toggle.closest('[data-post-card]');
        const section = card.querySelector('[data-comments]');

        if (!section.hidden && toggle.classList.contains('post-action')) {
            section.hidden = true;
            card.querySelectorAll('[data-comments-toggle]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
        } else {
            openComments(card, toggle.classList.contains('post-action'));
        }

        return;
    }

    const replyButton = event.target.closest('[data-reply-to]');

    if (replyButton) {
        const section = replyButton.closest('[data-comments]');
        const replyForm = section.querySelector(`[data-reply-form="${replyButton.dataset.replyTo}"]`);
        const input = replyForm.querySelector('input[name="content"]');
        const mention = `@${replyButton.dataset.replyName} `;

        replyForm.hidden = false;

        if (replyButton.hasAttribute('data-reply-mention') && replyButton.dataset.replyName && !input.value.startsWith(mention)) {
            input.value = mention + input.value;
        }

        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);

        return;
    }

    const editButton = event.target.closest('[data-comment-edit]');
    const cancelButton = event.target.closest('[data-comment-edit-cancel]');

    if (editButton || cancelButton) {
        const commentId = (editButton ?? cancelButton).dataset.commentEdit ?? cancelButton.dataset.commentEditCancel;
        const section = (editButton ?? cancelButton).closest('[data-comments]');
        const editForm = section.querySelector(`[data-comment-edit-form="${commentId}"]`);
        const isEditing = Boolean(editButton);

        section.querySelector(`[data-comment-view="${commentId}"]`).hidden = isEditing;
        section.querySelector(`[data-comment-meta="${commentId}"]`).hidden = isEditing;
        editForm.hidden = !isEditing;

        const textarea = editForm.querySelector('textarea');

        if (isEditing) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        } else {
            textarea.value = textarea.defaultValue;
        }
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-comment-action]');

    if (!form || event.defaultPrevented) {
        return;
    }

    event.preventDefault();

    const section = form.closest('[data-comments]');
    const card = form.closest('[data-post-card]');
    const wasMainForm = !form.matches('.reply-form') && form.method.toLowerCase() === 'post' && !form.querySelector('input[name="_method"]');
    const submitButton = form.querySelector('[type="submit"]');
    submitButton.disabled = true;

    try {
        const { ok, data } = await sendFormInBackground(form);

        if (!ok) {
            const error = section.querySelector('[data-comment-error]');
            error.textContent = firstErrorMessage(data);
            error.hidden = false;

            return;
        }

        section.innerHTML = data.html;
        setCountText(card.querySelector('[data-comment-count]'), data.commentCount);

        if (wasMainForm) {
            section.querySelector('.comment-form:not(.reply-form) input[name="content"]')?.focus();
        }
    } finally {
        submitButton.disabled = false;
    }
});

/* View counts: a post counts as viewed once at least half of it stays on screen for one second. */

const viewTimers = new WeakMap();

const viewObserver = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const card = entry.target;

            if (!entry.isIntersecting) {
                clearTimeout(viewTimers.get(card));

                return;
            }

            viewTimers.set(card, setTimeout(async () => {
                viewObserver.unobserve(card);
                card.dataset.viewed = 'true';

                const response = await fetch(card.dataset.viewUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                }).catch(() => null);

                if (response?.ok) {
                    setCountText(card.querySelector('[data-view-count]'), (await response.json()).viewCount);
                }
            }, 1000));
        });
    }, { threshold: 0.5 })
    : null;

function watchPostViews(root) {
    if (!viewObserver) {
        return;
    }

    root.querySelectorAll('[data-post-card][data-view-url]:not([data-own-post]):not([data-viewed])').forEach((card) => {
        viewObserver.observe(card);
    });
}

watchPostViews(document);

/* "Who reacted" and "who viewed" pop-up */

const peopleDialog = document.getElementById('people-dialog');

if (peopleDialog) {
    document.addEventListener('click', async (event) => {
        const opener = event.target.closest('[data-people-url]');

        if (opener) {
            const response = await fetch(opener.dataset.peopleUrl, { headers: { Accept: 'text/html' } });

            if (!response.ok) {
                return;
            }

            peopleDialog.querySelector('[data-people-title]').textContent = opener.dataset.peopleTitle;
            peopleDialog.querySelector('[data-people-body]').innerHTML = await response.text();
            peopleDialog.showModal();

            return;
        }

        const tab = event.target.closest('[data-people-filter]');

        if (tab) {
            const filter = tab.dataset.peopleFilter;

            peopleDialog.querySelectorAll('[data-people-filter]').forEach((button) => {
                button.setAttribute('aria-pressed', String(button === tab));
            });
            peopleDialog.querySelectorAll('[data-people-type]').forEach((row) => {
                row.hidden = filter !== 'all' && row.dataset.peopleType !== filter;
            });
        }
    });

    peopleDialog.querySelector('[data-people-close]').addEventListener('click', () => peopleDialog.close());
    peopleDialog.addEventListener('click', (event) => {
        if (event.target === peopleDialog) {
            peopleDialog.close();
        }
    });
}

/* Notification bell: open the list, mark it read, and refresh the red number every 30 seconds. */

const notificationBell = document.querySelector('[data-notification-bell]');

if (notificationBell) {
    const toggle = notificationBell.querySelector('[data-notification-toggle]');
    const panel = notificationBell.querySelector('[data-notification-panel]');
    const list = notificationBell.querySelector('[data-notification-list]');
    const badge = notificationBell.querySelector('[data-notification-badge]');

    const setUnreadCount = (count) => {
        badge.hidden = count === 0;
        badge.textContent = count > 99 ? '99+' : String(count);
        toggle.setAttribute('aria-label', count ? `Notifications, ${count} unread` : 'Notifications');
    };

    const closePanel = () => {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', async () => {
        if (!panel.hidden) {
            closePanel();

            return;
        }

        const response = await fetch(notificationBell.dataset.listUrl, { headers: { Accept: 'text/html' } });

        if (!response.ok) {
            return;
        }

        list.innerHTML = await response.text();
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');

        if (!badge.hidden) {
            await fetch(notificationBell.dataset.readUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            }).catch(() => null);
            setUnreadCount(0);
        }
    });

    document.addEventListener('click', (event) => {
        if (!panel.hidden && !notificationBell.contains(event.target)) {
            closePanel();
        }
    });

    panel.addEventListener('click', (event) => {
        const editLink = event.target.closest('[data-reminder-edit]');

        if (!editLink) {
            return;
        }

        const detail = { postId: Number(editLink.dataset.reminderEdit), handled: false };
        document.dispatchEvent(new CustomEvent('schedule:edit-post', { detail }));

        if (detail.handled) {
            event.preventDefault();
            closePanel();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            closePanel();
            toggle.focus();
        }
    });

    setInterval(async () => {
        if (document.hidden) {
            return;
        }

        const response = await fetch(notificationBell.dataset.countUrl, { headers: { Accept: 'application/json' } }).catch(() => null);

        if (response?.ok) {
            setUnreadCount((await response.json()).unreadCount);
        }
    }, 30000);
}

/* Arriving from a notification: jump to the post, open its comments and highlight the comment. */

const notificationTarget = new URLSearchParams(window.location.search);

if (notificationTarget.has('open_post')) {
    const targetCard = document.getElementById(`post-${notificationTarget.get('open_post')}`);

    const jumpToTarget = () => {
        targetCard.classList.add('is-highlighted');
        targetCard.scrollIntoView({ block: 'start', behavior: 'instant' });

        if (notificationTarget.get('comment')) {
            openComments(targetCard, false).then(() => {
                const targetComment = targetCard.querySelector(`#comment-${notificationTarget.get('comment')}`);

                targetComment?.classList.add('is-highlighted');
                targetComment?.scrollIntoView({ block: 'center', behavior: 'instant' });
            });
        }
    };

    if (targetCard && document.readyState === 'complete') {
        jumpToTarget();
    } else if (targetCard) {
        window.addEventListener('load', jumpToTarget, { once: true });
    }
}
