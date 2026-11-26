/*!
 * Announcements – bars (dismiss, rotation, countdown) and popups.
 */
(function () {
    'use strict';

    if (window.Announcements) {
        return;
    }

    var config = {texts: {}};
    var configElement = document.getElementById('announcements-config');

    try {
        config = JSON.parse(configElement.textContent) || config;
    } catch (e) {
        // Use the defaults.
    }

    var texts = config.texts || {};

    /* ------------------------------------------------------------------ */
    /* Storage helpers (private browsing may throw)                        */
    /* ------------------------------------------------------------------ */

    function storageGet(storage, key) {
        try {
            return window[storage].getItem(key);
        } catch (e) {
            return null;
        }
    }

    function storageSet(storage, key, value) {
        try {
            window[storage].setItem(key, value);
        } catch (e) {
            // Ignored: the announcement will simply be displayed again.
        }
    }

    /* ------------------------------------------------------------------ */
    /* Countdowns                                                          */
    /* ------------------------------------------------------------------ */

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function formatRemaining(ms) {
        var total = Math.floor(ms / 1000);
        var days = Math.floor(total / 86400);
        var hours = Math.floor((total % 86400) / 3600);
        var minutes = Math.floor((total % 3600) / 60);
        var seconds = total % 60;
        var parts = [];

        if (days > 0) {
            parts.push(days + (texts.days || 'd'));
        }

        parts.push(pad(hours) + (texts.hours || 'h'));
        parts.push(pad(minutes) + (texts.minutes || 'm'));
        parts.push(pad(seconds) + (texts.seconds || 's'));

        return (texts.prefix ? texts.prefix + ' ' : '') + parts.join(' ');
    }

    function updateCountdowns() {
        var now = Date.now();
        var elements = document.querySelectorAll('[data-announcements-countdown]');

        Array.prototype.forEach.call(elements, function (element) {
            var end = Date.parse(element.getAttribute('data-announcements-countdown'));

            if (isNaN(end)) {
                return;
            }

            var remaining = end - now;

            if (remaining <= 0) {
                element.textContent = texts.ended || '';
                var bar = element.closest('[data-announcements-bar]');

                if (bar) {
                    removeBar(bar);
                }

                element.removeAttribute('data-announcements-countdown');

                return;
            }

            element.textContent = formatRemaining(remaining);
            element.setAttribute('aria-label', element.textContent);
        });

        return elements.length > 0;
    }

    /* ------------------------------------------------------------------ */
    /* Bars                                                                */
    /* ------------------------------------------------------------------ */

    var container = document.getElementById('announcements-bars');
    var rotation = {timer: null, index: 0, paused: false};

    function bars() {
        return container ? Array.prototype.slice.call(container.querySelectorAll('[data-announcements-bar]')) : [];
    }

    function removeBar(bar) {
        if (bar.parentNode) {
            bar.parentNode.removeChild(bar);
        }

        if (container && bars().length === 0) {
            stopRotation();
            container.parentNode && container.parentNode.removeChild(container);
            container = null;

            return;
        }

        if (isRotating()) {
            show(Math.min(rotation.index, bars().length - 1));
        }
    }

    function isRotating() {
        return container && container.getAttribute('data-announcements-mode') === 'rotate';
    }

    function show(index, animate) {
        var list = bars();

        if (list.length === 0) {
            return null;
        }

        rotation.index = (index + list.length) % list.length;

        list.forEach(function (bar, i) {
            var active = i === rotation.index;

            bar.classList.toggle('d-none', !active);

            if (active && animate) {
                // Replay the Bootstrap fade transition.
                bar.classList.remove('show');
                void bar.offsetWidth;
                bar.classList.add('show');
            }

            var pager = bar.querySelector('[data-announcements-pager]');

            if (pager) {
                pager.hidden = list.length < 2;
                pager.querySelector('[data-announcements-position]').textContent = (i + 1) + '/' + list.length;
            }
        });

        return list[rotation.index];
    }

    function stopRotation() {
        if (rotation.timer) {
            window.clearInterval(rotation.timer);
            rotation.timer = null;
        }
    }

    function startRotation() {
        if (!isRotating()) {
            return;
        }

        // Also hides the pagers when the other bars were dismissed.
        show(0);

        var delay = parseInt(container.getAttribute('data-announcements-delay'), 10) || 6;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (bars().length < 2 || reduceMotion) {
            return;
        }

        rotation.timer = window.setInterval(function () {
            if (!rotation.paused && !document.hidden) {
                show(rotation.index + 1, true);
            }
        }, delay * 1000);

        ['mouseenter', 'focusin'].forEach(function (name) {
            container.addEventListener(name, function () {
                rotation.paused = true;
            });
        });

        ['mouseleave', 'focusout'].forEach(function (name) {
            container.addEventListener(name, function () {
                rotation.paused = false;
            });
        });
    }

    if (container) {
        container.addEventListener('click', function (event) {
            var button = event.target.closest('[data-announcements-close], [data-announcements-prev], [data-announcements-next]');

            if (!button) {
                return;
            }

            var bar = button.closest('[data-announcements-bar]');

            if (button.hasAttribute('data-announcements-close')) {
                storageSet('localStorage', 'announcements-dismissed-' + bar.getAttribute('data-announcements-id'), bar.getAttribute('data-announcements-version'));
                removeBar(bar);

                return;
            }

            var step = button.hasAttribute('data-announcements-prev') ? -1 : 1;
            var shown = show(rotation.index + step, true);
            var target = shown && shown.querySelector(button.hasAttribute('data-announcements-prev') ? '[data-announcements-prev]' : '[data-announcements-next]');

            // The clicked bar is now hidden: keep the focus on the same button of the new bar.
            if (target) {
                target.focus();
            }
        });

        startRotation();
    }

    /* ------------------------------------------------------------------ */
    /* Popup (at most one per page load), displayed with a Bootstrap modal */
    /* ------------------------------------------------------------------ */

    var popup = null;
    var modal = null;
    var lastFocus = null;

    function alreadySeen(element) {
        var key = 'announcements-seen-' + element.getAttribute('data-announcements-id');
        var version = element.getAttribute('data-announcements-version');

        switch (element.getAttribute('data-announcements-frequency')) {
            case 'once':
                return storageGet('localStorage', key) === version;
            case 'session':
                return storageGet('sessionStorage', key) === version;
            default:
                return false;
        }
    }

    function markSeen(element) {
        var key = 'announcements-seen-' + element.getAttribute('data-announcements-id');
        var version = element.getAttribute('data-announcements-version');
        var frequency = element.getAttribute('data-announcements-frequency');

        if (frequency === 'once') {
            storageSet('localStorage', key, version);
        } else if (frequency === 'session') {
            storageSet('sessionStorage', key, version);
        }
    }

    function openPopup() {
        if (!window.bootstrap || !window.bootstrap.Modal) {
            return; // The theme does not load the Bootstrap script.
        }

        lastFocus = document.activeElement;
        modal = window.bootstrap.Modal.getOrCreateInstance(popup);
        modal.show();
        markSeen(popup);
    }

    function closePopup() {
        if (modal) {
            modal.hide();
        }
    }

    var candidates = document.querySelectorAll('[data-announcements-popup]');

    for (var i = 0; i < candidates.length; i++) {
        if (!popup && !alreadySeen(candidates[i])) {
            popup = candidates[i];
        } else {
            candidates[i].parentNode.removeChild(candidates[i]);
        }
    }

    if (popup) {
        // Bootstrap modals must be at the top level of the page.
        document.body.appendChild(popup);

        popup.addEventListener('hidden.bs.modal', function () {
            if (lastFocus && typeof lastFocus.focus === 'function' && document.body.contains(lastFocus)) {
                lastFocus.focus();
            }

            document.dispatchEvent(new CustomEvent('announcements:popup-closed'));
        });

        window.setTimeout(openPopup, 600);
    }

    /* ------------------------------------------------------------------ */

    if (updateCountdowns()) {
        window.setInterval(updateCountdowns, 1000);
    }

    window.Announcements = {
        closePopup: closePopup
    };
})();
