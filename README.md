# CourseCtrl Dates · `block_coursectrldates`

[![Moodle Plugin CI](https://github.com/ralferlebach/moodle-block_coursectrldates/actions/workflows/moodle-ci.yml/badge.svg?branch=development)](https://github.com/ralferlebach/moodle-block_coursectrldates/actions/workflows/moodle-ci.yml?query=branch%3Adevelopment) [![Moodle 4.5–5.3](https://img.shields.io/badge/Moodle-4.5%E2%80%935.3-informational)](version.php)

[![CourseCtrl Hub](https://img.shields.io/badge/Plugin%20family-CourseCtrl%20Hub-3867d6)](https://github.com/ralferlebach/moodle-local_coursectrl) [![CourseCtrl Dates](https://img.shields.io/badge/Companion-CourseCtrl%20Dates-16a085)](https://github.com/ralferlebach/moodle-block_coursectrldates) [![Learn Tip](https://img.shields.io/badge/Companion-Learn%20Tip-8e44ad)](https://github.com/ralferlebach/moodle-block_coursectrllearntip)

CourseCtrl Dates is a compact Moodle course block showing scheduled activity dates, a mini calendar, and links into the Course Control Hub timeline and date-shift tools.

## Requirements

- **Moodle:** 4.5–5.3 declared by `version.php` (`1.1.1`, `2026101701` on `development`; CI verification remains outstanding).
- **Required plugin:** [`local_coursectrl`](https://github.com/ralferlebach/moodle-local_coursectrl), minimum plugin version `2026051100` as currently declared in `version.php`.
- **Install path:** `blocks/coursectrldates`.
- **PHP/database:** follow requirements for the selected Moodle release; mixed branch versions are not a verified integration target.

## Motivation

Teachers need upcoming course deadlines and date-driven activity entry points without leaving their Moodle course page.

## Installation

1. Install [Course Control Hub](https://github.com/ralferlebach/moodle-local_coursectrl) first in `local/coursectrl`.
2. Install this plugin in `blocks/coursectrldates`.
3. Visit **Site administration → Notifications**; add and configure the block on a course page.

See [Moodle plugin installation](https://docs.moodle.org/en/Installing_plugins).

## Usage & settings

The block provides a **mini calendar**, chronological **activity-date list**, timeline shift links (visible according to CourseCtrl permissions), and a configurable **Termin-Assistent** setup-help card.

Per-instance settings include calendar visibility/weeks, list length/time horizon, help-card triggers and the assistant's time window. The help card offers *Ja* (open management), *Nein* (hide for this page view) and *Abschalten* (disable assistant for the block instance, with permission).

A known frontend defect can falsely proceed after AJAX failure; see [#6](https://github.com/ralferlebach/moodle-block_coursectrldates/issues/6). Configure permissions and validate on your installed combination before a production rollout.

## Capabilities

Defined in [`db/access.php`](db/access.php):
- `block/coursectrldates:view`: see the block.
- `block/coursectrldates:addinstance`: add/configure it, including assistant shutdown.
- `block/coursectrldates:myaddinstance`: system-level personal-dashboard capability, not enabled by default.
- Shift actions additionally require `local/coursectrl:bulkaction`.

## Scheduled tasks

No dedicated scheduled task is currently declared by this block; display data is supplied by the local plugin.

## How this plugin works / pitfalls

The block consumes the local plugin's inventory, date collection, calendar and timeline services. It does **not** execute date shifts itself.

- Shared course-position/view/edit link semantics are being standardised in [Local #64](https://github.com/ralferlebach/moodle-local_coursectrl/issues/64) and [Block #7](https://github.com/ralferlebach/moodle-block_coursectrldates/issues/7).
- Integration and Moodle-5.3 Behat matrix remain tracked in [#10](https://github.com/ralferlebach/moodle-block_coursectrldates/issues/10) and [#11](https://github.com/ralferlebach/moodle-block_coursectrldates/issues/11).
- Individual help-card preferences are handled through Moodle's Privacy API. The block is not intended as an independent source of dates.
- No external project homepage currently exists.

## Theme support

Targeted at Boost/Classic; validate your theme, accessibility and mobile display, especially across Moodle 4.5 (Bootstrap 4) and Moodle 5.x (Bootstrap 5).

## Plugin repositories / related projects

- **Source:** https://github.com/ralferlebach/moodle-block_coursectrldates
- **Course Control Hub:** https://github.com/ralferlebach/moodle-local_coursectrl
- **Planned Learn Tip:** https://github.com/ralferlebach/moodle-block_coursectrllearntip
- **Template:** https://github.com/ralferlebach/moodle-plugintemplate

## Bug reports, support & feature proposals

Use [GitHub Issues](https://github.com/ralferlebach/moodle-block_coursectrldates/issues) or send a [Pull request](https://github.com/ralferlebach/moodle-block_coursectrldates/pulls). Include Moodle version, browser, steps and screenshots.

## Moodle release support

Declared `supported=[405,503]`; completion of the actual CI matrix and release checks is a separate prerequisite to production approval.

## Translating this plugin

English/German source strings in `lang/en/` and `lang/de/`; other languages via [AMOS](https://lang.moodle.org/).

## Right-to-left support

RTL browser testing is not yet complete.

## Maintainer, copyright & licence

Maintainer: **Ralf Erlebach**. Copyright © 2026 contributors as credited in the code. **GNU GPL v3 or later**.
