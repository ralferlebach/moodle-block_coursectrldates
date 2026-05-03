# Course Control Hub – Dates Block (`block_coursectrldates`)

A companion block plugin for [local_coursectrl](https://github.com/ralferlebach/moodle-local_coursectrl) that displays a compact course-date overview directly on the Moodle course page.

The block shows teachers:

- an optional mini calendar for the next 1–6 weeks,
- a chronological list of upcoming activity events (open, close, due dates),
- shift-date shortcut buttons that link into `local_coursectrl`,
- and a splash screen when the course has been newly created, reset, or has newly imported time-limited content.

All date calculations and bulk-date changes remain in `local_coursectrl`. This block is a read-only frontend and navigation entry point.

## Requirements

- Moodle 4.5 or later
- PHP 8.2 or later
- `local_coursectrl` installed

## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code.
3. Check the plugin validation report and finish the installation.

## Installing manually

Copy the contents of this directory to:

    {your/moodle/dirroot}/blocks/coursectrldates

Then log in as admin and go to _Site administration > Notifications_ to complete the installation, or run:

    $ php admin/cli/upgrade.php

## Development status

This plugin is currently in **ALPHA**. The stub codebase is installable but renders placeholder content only. See `docs/materials/Lastenheft_Pflichtenheft_Blueprint.md` for the full specification.

## License

2026 Ralf Erlebach

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
