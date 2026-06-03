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
define(['core/ajax'], function(Ajax) {

    'use strict';

    /**
     * Call the block_action external service for a given action.
     *
     * @param {string} action     Action name: 'dismiss_help' or 'disable_help'.
     * @param {string} instanceid Block instance ID (string from data-attribute).
     * @param {string} courseid   Course ID (string from data-attribute).
     * @returns {Promise} Resolves when the server call is complete.
     */
    var callBlockAction = function(action, instanceid, courseid) {
        var requests = Ajax.call([{
            methodname: 'block_coursectrldates_block_action',
            args: {
                instanceid: parseInt(instanceid, 10),
                courseid:   parseInt(courseid, 10),
                action:     action,
            },
        }]);
        return requests[0].catch(function() {
            // Network or server error: state may not be saved.
            // Swallow so the caller can still proceed with UI updates.
            return null;
        });
    };

    /**
     * Attach delegated handlers for all three help-notification actions.
     *
     *   data-action="dismiss-help-and-go"  — dismiss permanently, then navigate to managepageurl.
     *   data-action="defer-help"           — remove from DOM, no server call.
     *   data-action="disable-help"         — disable Termin-Assistent for all users, then reload.
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
                var instanceid = btnYes.dataset.instanceid || '';
                var courseid = btnYes.dataset.courseid || '';
                callBlockAction('dismiss_help', instanceid, courseid).then(function() {
                    window.location.href = targeturl;
                    return null;
                }).catch(function() {
                    window.location.href = targeturl;
                    return null;
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
                var offinstanceid = btnOff.dataset.instanceid || '';
                var offcourseid = btnOff.dataset.courseid || '';
                callBlockAction('disable_help', offinstanceid, offcourseid).then(function() {
                    window.location.reload();
                    return null;
                }).catch(function() {
                    window.location.reload();
                    return null;
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
