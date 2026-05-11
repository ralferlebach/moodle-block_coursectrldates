# Course Dates Block (`block_coursectrldates`)

A companion block for the [Course Control Hub](https://github.com/ralferlebach/moodle-local_coursectrl) (`local_coursectrl`) Moodle plugin. The block displays upcoming course activity dates directly on the course page and lets editing teachers jump into the Course Control Hub shift workflow without leaving the course.

## Features

- **Mini calendar** — configurable 1–6 week view from today, highlighting days with upcoming activity dates
- **Chronological event list** — groups upcoming dates by day → time slot → activity entry
- **Shift buttons** — slot, following, and per-entry shift links that open the `local_coursectrl` timeline with the shift dialog pre-opened (only shown to users with `local/coursectrl:bulkaction` capability)
- **Setup-help notification** — context-aware banner that appears when a new-start event is detected (course newly created, reset, or time-dependent activities added); configurable triggers and time window

## Requirements

- Moodle 4.5 or later
- PHP 8.2 or later
- `local_coursectrl` (Course Control Hub) installed and active

## Installation

1. Copy or clone this repository into `blocks/coursectrldates/` inside your Moodle installation.
2. Visit **Site administration → Notifications** to trigger the plugin installation.
3. Add the block to a course page via the block drawer.

## Configuration

Each block instance can be configured independently:

| Setting | Description | Default |
|---|---|---|
| Show calendar | Show or hide the mini calendar | Yes |
| Number of weeks (calendar) | Calendar range from today | 4 |
| Display mode | Show a fixed number of events or a time window | Time window |
| Time window (event list) | Weeks of events to display | 4 |
| Number of events | Fixed event count (count mode) | 10 |
| Offer setup help | Enable the setup-help notification | Yes |
| Trigger: newly created | Fire when course was created in the time window | Yes |
| Trigger: course reset | Fire when course was reset in the time window | Yes |
| Trigger: time-dependent activities | Fire when time-limited activities were added | Yes |
| Time window (help) | How far back triggers look | 4 weeks |

## Architecture

The block is a thin frontend: all date data, calendar rendering, and URL construction for the shift workflow come from `local_coursectrl` APIs. The block itself does not modify any course data.

```
block_coursectrldates
  → local_coursectrl\local\inventory\inventory_service
  → local_coursectrl\local\analysis\date_collector
  → local_coursectrl\local\analysis\calendar_grid_builder
  → local_coursectrl\manager\calendar_manager
```

Shift actions are handled entirely by `local_coursectrl/timeline.php`.

## Development status

`MATURITY_ALPHA` — functional but not yet fully test-covered. See the open issues for the path to Beta/RC.

## License

GNU General Public License v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html).
