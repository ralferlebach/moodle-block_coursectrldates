# Course Dates Block (`block_coursectrldates`)

A companion block for the [Course Control Hub](https://github.com/ralferlebach/moodle-local_coursectrl) (`local_coursectrl`) Moodle plugin. The block displays upcoming course activity dates directly on the course page and lets editing teachers open the Course Control Hub shift workflow without leaving the course.

## Features

- **Mini calendar** — configurable 1–6 week view from today, highlighting days with upcoming activity dates
- **Chronological event list** — groups upcoming dates by day → time slot → activity entry, sorted chronologically
- **Shift buttons** — slot, following, and per-entry shift links that open `local_coursectrl/timeline.php` with the shift dialog pre-opened (only shown to users with `local/coursectrl:bulkaction` capability)
- **Termin-Assistent** — context-aware notification card that appears when new course content is detected (course newly created or imported, reset, or time-dependent activities added); configurable triggers, time window, and dismiss behaviour

## Requirements

- Moodle 4.5 – 5.2
- PHP 8.1 or later
- `local_coursectrl` 1.2.0 or later (version `2026051100`+)

## Installation

1. Copy or clone this repository into `blocks/coursectrldates/` inside your Moodle installation.
2. Ensure `local_coursectrl` is installed and up to date.
3. Visit **Site administration → Notifications** to trigger the plugin installation.
4. Add the block to a course page via the block drawer.

## Configuration

Each block instance can be configured independently:

| Setting | Description | Default |
|---|---|---|
| Show calendar | Show or hide the mini calendar | Yes |
| Number of weeks (calendar) | Calendar range from today | 4 |
| Display mode | Show a fixed number of events or a time window | Time window |
| Time window (event list) | Weeks of events to display | 4 |
| Number of events | Fixed event count (count mode, max 100) | 10 |
| Enable Scheduling Assistant | Show the Termin-Assistent notification | Yes |
| Trigger: newly created | Fire when course was created in the time window | Yes |
| Trigger: course reset | Fire when course was reset in the time window | Yes |
| Trigger: time-dependent activities | Fire when time-limited activities were added | Yes |
| Time window (assistant) | How far back triggers look | 4 weeks |

## Termin-Assistent behaviour

When a trigger fires and the notification has not been dismissed, the block shows **only** the Termin-Assistent card — calendar and event list are hidden. Three actions are available:

| Button | Effect |
|---|---|
| **Ja** | Dismisses permanently for the current user; navigates to `local_coursectrl/manage.php` |
| **Nein** | Hides the card for this page view; calendar and event list appear immediately without a reload |
| **Abschalten** | Requires editing rights (`addinstance` capability); dismisses permanently and deactivates the Termin-Assistent for the entire block instance |

The **Termin-Assistent jetzt anzeigen** button in the block settings reactivates the Termin-Assistent and force-shows it on the next page load.

## Architecture

The block is a thin frontend — all date data, calendar rendering, and URL construction for the shift workflow come from `local_coursectrl` APIs. The block itself does not modify any course data.

```
block_coursectrldates
  → local_coursectrl\local\inventory\inventory_service
  → local_coursectrl\local\analysis\date_collector
  → local_coursectrl\local\analysis\calendar_grid_builder
  → local_coursectrl\manager\calendar_manager
```

Shift actions are handled entirely by `local_coursectrl/timeline.php`.

## Development status

`MATURITY_STABLE` — validated by PHPUnit (48 tests, 114 assertions), Behat, PHPCS, and ESLint. Suitable for production use.

## Privacy

The block stores no personal data of its own. Two Moodle user preferences per block instance are used to track dismiss and force-show state:

- `block_coursectrldates_splash_dismissed_<instanceid>` — permanent dismiss flag per user
- `block_coursectrldates_force_show_<instanceid>` — one-shot force-display flag per user

Both preferences are deleted when the block instance is removed. They are exported via Moodle's Privacy API.

## License

GNU General Public License v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html).
