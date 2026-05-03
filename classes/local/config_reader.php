<?php
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

namespace block_coursectrldates\local;

/**
 * Reads and validates per-instance block configuration.
 *
 * Provides typed accessors for every block setting so that the rest of
 * the codebase never has to touch the raw config object directly.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Config reader for block_coursectrldates instances.
 */
class config_reader {

    /** @var int Default number of calendar weeks to display. */
    public const DEFAULT_CALENDAR_WEEKS = 4;

    /** @var int Default number of event-list weeks for time-window mode. */
    public const DEFAULT_LIST_WEEKS = 4;

    /** @var int Default fixed event count for count mode. */
    public const DEFAULT_LIST_COUNT = 10;

    /** @var string List mode: show all events within a time window. */
    public const MODE_TIMEWINDOW = 'timewindow';

    /** @var string List mode: show a fixed number of upcoming events. */
    public const MODE_COUNT = 'count';

    /** @var object|null Raw block instance config from Moodle. */
    private ?object $config;

    /**
     * Constructor.
     *
     * @param object|null $config Raw block instance config (may be null or empty).
     */
    public function __construct(?object $config) {
        $this->config = $config ?? new \stdClass();
    }

    /**
     * Whether the calendar section should be shown.
     *
     * @return bool
     */
    public function show_calendar(): bool {
        return !empty($this->config->show_calendar);
    }

    /**
     * Number of weeks the calendar should cover (1–6).
     *
     * @return int
     */
    public function calendar_weeks(): int {
        $weeks = (int) ($this->config->calendar_weeks ?? self::DEFAULT_CALENDAR_WEEKS);
        return max(1, min(6, $weeks));
    }

    /**
     * Event-list display mode: MODE_TIMEWINDOW or MODE_COUNT.
     *
     * @return string
     */
    public function list_mode(): string {
        $mode = $this->config->list_mode ?? self::MODE_TIMEWINDOW;
        if (!in_array($mode, [self::MODE_TIMEWINDOW, self::MODE_COUNT], true)) {
            return self::MODE_TIMEWINDOW;
        }
        return $mode;
    }

    /**
     * Number of weeks for time-window mode (1–6).
     *
     * @return int
     */
    public function list_weeks(): int {
        $weeks = (int) ($this->config->list_weeks ?? self::DEFAULT_LIST_WEEKS);
        return max(1, min(6, $weeks));
    }

    /**
     * Fixed event count for count mode (minimum 1).
     *
     * @return int
     */
    public function list_count(): int {
        $count = (int) ($this->config->list_count ?? self::DEFAULT_LIST_COUNT);
        return max(1, $count);
    }

    /**
     * Whether the splash screen feature is enabled.
     *
     * @return bool
     */
    public function show_splash(): bool {
        return !empty($this->config->show_splash);
    }

    /**
     * Whether the splash state should be reset on the next page load.
     *
     * @return bool
     */
    public function reset_splash(): bool {
        return !empty($this->config->reset_splash);
    }
}
