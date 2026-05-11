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
 *   3. Manage setup-help notification actions (Ja / Später / Nein).
 *
 * @module     block_coursectrldates/block
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function () {

    'use strict';

    /**
     * Dismiss the setup-help notification via AJAX.
     *
     * @param {string}  url       Dismiss endpoint URL.
     * @param {Element} helpcard  Help card element to remove on success.
     * @returns {void}
     */
    var dismissHelp = function (url, helpcard) {
        fetch(url, {
            method: 'GET',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        })
        .then(function (response) {
            if (response.ok && helpcard) {
                helpcard.remove();
            }
            return null;
        })
        .catch(function () {
            // Network or server error: preference was not stored.
            // Leave the helpcard visible so the user can retry on next page load.
            return null;
        });
    };

    /**
     * Attach delegated handlers for all three help-notification actions.
     *
     *   data-action="dismiss-help-and-go"  — permanently dismiss + navigate.
     *   data-action="defer-help"           — remove from DOM, no server call.
     *   data-action="dismiss-help"         — permanently dismiss, no navigate.
     *
     * @param {Element} root Block root element.
     * @returns {void}
     */
    var attachHelpHandlers = function (root) {
        root.addEventListener('click', function (e) {
            var helpcard = root.querySelector('[data-region="coursectrldates-splash"]');

            // "Ja" — dismiss permanently and let default href navigation proceed.
            var btnYes = e.target.closest('[data-action="dismiss-help-and-go"]');
            if (btnYes) {
                var dismissurl = btnYes.dataset.dismissUrl || '';
                if (dismissurl) {
                    // Fire and forget — page navigates away immediately.
                    fetch(dismissurl, {
                        method: 'GET',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                    }).catch(function () {});
                }
                return; // Default href navigation proceeds.
            }

            // "Später" — remove from DOM without a server call.
            var btnLater = e.target.closest('[data-action="defer-help"]');
            if (btnLater) {
                e.preventDefault();
                if (helpcard) {
                    helpcard.remove();
                }
                return;
            }

            // "Nein" — permanently dismiss without navigation.
            var btnNo = e.target.closest('[data-action="dismiss-help"]');
            if (btnNo) {
                e.preventDefault();
                dismissHelp(btnNo.getAttribute('href'), helpcard);
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
    var scrollCalendarToToday = function (root) {
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
    var attachJumpToDay = function (root) {
        root.addEventListener('click', function (e) {
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
        init: function (root) {
            if (!root) {
                return;
            }
            scrollCalendarToToday(root);
            attachJumpToDay(root);
            attachHelpHandlers(root);
        }
    };
});
