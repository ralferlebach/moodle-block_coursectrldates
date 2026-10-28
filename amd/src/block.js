// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module for block_coursectrldates.
 *
 * Handles three responsibilities:
 *   1. Scroll the mini-calendar strip to the current month on page load.
 *   2. Scroll the event list to a specific day when a calendar cell is clicked.
 *   3. Manage Termin-Assistent notification actions (Ja / Nein / Abschalten).
 *
 * @module     block_coursectrldates/block
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/str'], function(Ajax, Str) {

    'use strict';

    /** @var {number} Default time in ms after which a pending request counts as failed. */
    var DEFAULT_TIMEOUT = 15000;

    /**
     * Call the block_action external service for a given action.
     *
     * Resolves only when the server confirms the change (result === true).
     * Rejects with an Error whose kind is 'offline', 'timeout', 'permission' or 'server' otherwise,
     * so callers never treat a failed request as success.
     *
     * @param {string} action     Action name: 'dismiss_help' or 'disable_help'.
     * @param {string} instanceid Block instance ID (string from data-attribute).
     * @param {string} courseid   Course ID (string from data-attribute).
     * @param {number} timeout    Time in ms after which the request counts as failed.
     * @returns {Promise} Resolves when the server confirmed the change.
     */
    var callBlockAction = function(action, instanceid, courseid, timeout) {
        if (typeof navigator !== 'undefined' && navigator.onLine === false) {
            return Promise.reject(failure('offline'));
        }
        var request = Ajax.call([{
            methodname: 'block_coursectrldates_block_action',
            args: {
                instanceid: parseInt(instanceid, 10),
                courseid:   parseInt(courseid, 10),
                action:     action,
            },
        }])[0];
        var timer = null;
        var timed = new Promise(function(resolve, reject) {
            timer = window.setTimeout(function() {
                reject(failure('timeout'));
            }, timeout);
        });
        return Promise.race([Promise.resolve(request), timed]).then(function(result) {
            window.clearTimeout(timer);
            if (result !== true) {
                throw failure('server', result);
            }
            return true;
        }, function(error) {
            window.clearTimeout(timer);
            if (error && error.kind) {
                throw error;
            }
            throw failure(classifyError(error), error);
        });
    };

    /**
     * Build a failure error with its kind.
     *
     * @param {string} kind  offline | timeout | permission | server.
     * @param {*}      cause Original rejection value.
     * @returns {Error}
     */
    var failure = function(kind, cause) {
        var error = new Error(kind);
        error.kind = kind;
        error.cause = cause;
        return error;
    };

    /**
     * Classify a core/ajax rejection.
     *
     * @param {object} error Rejection value of core/ajax.
     * @returns {string} 'offline', 'permission' or 'server'.
     */
    var classifyError = function(error) {
        if (typeof navigator !== 'undefined' && navigator.onLine === false) {
            return 'offline';
        }
        // A JSON exception carries an errorcode; a failed HTTP request rejects with the status text
        // (Moodle 4.5, jQuery) or an Error "HTTP 403: Forbidden" (Moodle 5.x, fetch).
        var code = '';
        if (typeof error === 'string') {
            code = error;
        } else if (error && (error.errorcode || (error.exception && error.exception.errorcode))) {
            code = error.errorcode || error.exception.errorcode;
        } else if (error && error.message) {
            code = error.message;
        }
        var permission = /nopermission|requirecapability|requirelogin|login|sesskey|invalidtoken|accessdenied/i;
        if (permission.test(code) || /forbidden|unauthori[sz]ed|HTTP 40[13]/i.test(code)) {
            return 'permission';
        }
        return 'server';
    };

    /**
     * Show a localised, focusable error in the splash and re-enable its buttons.
     *
     * @param {Element} root  Block root element.
     * @param {string}  kind  Failure kind.
     * @returns {Promise}
     */
    var showError = function(root, kind) {
        var keys = {
            offline: 'action_error_network',
            timeout: 'action_error_timeout',
            permission: 'action_error_permission',
            server: 'action_error_generic',
        };
        var key = keys[kind] || keys.server;
        return Str.get_string(key, 'block_coursectrldates').then(function(text) {
            var region = root.querySelector('[data-region="coursectrldates-splash-error"]');
            if (region) {
                region.textContent = text;
                region.hidden = false;
                region.focus();
            }
            return null;
        }).catch(function() {
            return null;
        });
    };

    /**
     * Mark the splash buttons as busy (or not).
     *
     * @param {Element} root Block root element.
     * @param {Element|null} active The button whose request is pending.
     * @param {boolean} busy Whether a request is pending.
     * @returns {void}
     */
    var setBusy = function(root, active, busy) {
        var splash = root.querySelector('[data-region="coursectrldates-splash"]');
        if (!splash) {
            return;
        }
        splash.setAttribute('aria-busy', busy ? 'true' : 'false');
        splash.querySelectorAll('[data-action]').forEach(function(el) {
            if (busy) {
                el.setAttribute('aria-disabled', 'true');
                el.classList.add('disabled');
                if (el.tagName === 'BUTTON') {
                    el.disabled = true;
                }
            } else {
                el.removeAttribute('aria-disabled');
                el.classList.remove('disabled');
                if (el.tagName === 'BUTTON') {
                    el.disabled = false;
                }
            }
        });
        if (!busy && active) {
            active.focus();
        }
        if (busy) {
            var region = root.querySelector('[data-region="coursectrldates-splash-error"]');
            if (region) {
                region.hidden = true;
                region.textContent = '';
            }
        }
    };

    /**
     * Run a server action; continue only on confirmed success, report every failure.
     *
     * @param {Element}  root      Block root element.
     * @param {Element}  button    Clicked button or link.
     * @param {string}   action    Server action.
     * @param {Function} onsuccess Called after the server confirmed the change.
     * @returns {Promise}
     */
    var runAction = function(root, button, action, onsuccess) {
        if (button.getAttribute('aria-disabled') === 'true') {
            return Promise.resolve(null);
        }
        var timeout = parseInt(root.dataset.ajaxTimeout || '', 10) || DEFAULT_TIMEOUT;
        setBusy(root, button, true);
        return callBlockAction(action, button.dataset.instanceid || '', button.dataset.courseid || '', timeout)
            .then(function() {
                onsuccess();
                return null;
            })
            .catch(function(failure) {
                setBusy(root, button, false);
                return showError(root, failure && failure.kind ? failure.kind : 'server');
            });
    };

    /**
     * Attach delegated handlers for all three help-notification actions.
     *
     *   data-action="dismiss-help-and-go"  — dismiss permanently, then navigate to managepageurl.
     *   data-action="defer-help"           — remove from DOM, no server call.
     *   data-action="disable-help"         — disable Termin-Assistent for all users, then reload.
     *
     * Navigation and reload happen only after the server confirmed the change; a failure
     * keeps the splash, shows an accessible message and allows a retry.
     *
     * @param {Element} root Block root element.
     * @returns {void}
     */
    var attachHelpHandlers = function(root) {
        root.addEventListener('click', function(e) {
            var helpcard = root.querySelector('[data-region="coursectrldates-splash"]');

            // "Ja" — dismiss permanently and navigate to managepageurl.
            var btnYes = e.target.closest('[data-action="dismiss-help-and-go"]');
            if (btnYes) {
                e.preventDefault();
                var targeturl = btnYes.getAttribute('href');
                runAction(root, btnYes, 'dismiss_help', function() {
                    window.location.href = targeturl;
                });
                return;
            }

            // "Nein" — remove from DOM without a server call.
            var btnLater = e.target.closest('[data-action="defer-help"]');
            if (btnLater) {
                e.preventDefault();
                if (helpcard) {
                    helpcard.remove();
                }
                var maincontent = root.querySelector('[data-region="coursectrldates-main"]');
                if (maincontent) {
                    maincontent.classList.remove('block-coursectrldates-hidden');
                }
                return;
            }

            // "Abschalten" — disable Termin-Assistent for all users, then reload.
            var btnOff = e.target.closest('[data-action="disable-help"]');
            if (btnOff) {
                e.preventDefault();
                runAction(root, btnOff, 'disable_help', function() {
                    window.location.reload();
                });
                return;
            }
        });
    };

    /**
     * Scroll the calendar strip to bring the current month into view.
     *
     * Uses a manual scrollLeft calculation rather than scrollIntoView to
     * avoid side effects on the page's vertical scroll position.
     *
     * @param {Element} root Block root element.
     * @returns {void}
     */
    var scrollCalendarToToday = function(root) {
        var calrow = root.querySelector('[data-region="coursectrldates-calrow"]');
        if (!calrow) {
            return;
        }
        var current = calrow.querySelector('.month-current');
        if (!current) {
            return;
        }
        var offset = current.offsetLeft
            - (calrow.offsetWidth / 2)
            + (current.offsetWidth / 2);
        calrow.scrollLeft = Math.max(0, offset);
    };

    /**
     * Attach a delegated click handler for data-action="jump-to-day".
     *
     * Clicking a highlighted calendar cell scrolls the event list to the
     * corresponding day card (id="ccd-day-YYYY-MM-DD").
     *
     * @param {Element} root Block root element.
     * @returns {void}
     */
    var attachJumpToDay = function(root) {
        root.addEventListener('click', function(e) {
            var cell = e.target.closest('[data-action="jump-to-day"]');
            if (!cell) {
                return;
            }
            var daykey = cell.dataset.daykey;
            if (!daykey) {
                return;
            }
            var target = root.querySelector('#ccd-day-' + daykey);
            if (!target) {
                return;
            }
            e.preventDefault();
            target.scrollIntoView({behavior: 'smooth', block: 'start'});
        });
    };

    return {
        /**
         * Initialise all block behaviour.
         *
         * @param {Element} root Block root element.
         * @returns {void}
         */
        init: function(root) {
            if (!root) {
                return;
            }
            scrollCalendarToToday(root);
            attachJumpToDay(root);
            attachHelpHandlers(root);
        }
    };
});
