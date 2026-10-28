# Changelog

All notable changes to `block_coursectrldates` are documented here.

## [1.2.1] – Re-audit fixes (2026102100)

### Fixed
- The Scheduling Assistant no longer treats failed requests as success: navigation and reload
  happen only after the server confirmed the change; 403, 500, exceptions, offline and timeouts
  show a localised, focused message and allow a retry (#6).
- The assistant is only offered to users who may shift dates.

### Changed
- Event entries follow the family URL contract: name to the course position, magnifier to the
  activity, cog to its settings, each only when the viewer may use it (#7).
- Requires `local_coursectrl` 1.4.0 (2026102100); the family is released together.

## [1.2.0] – Template CI and user stories (2026101800)

### Changed
- CI replaced by the plugin template pipelines (`moodle-plugin-ci-dev.yml`,
  `moodle-plugin-ci-main.yml`, `playwright.yml`), identical for all CourseCtrl plugins;
  `local_coursectrl` is registered once via `add-plugin`; no time-based runs.
- Database services in CI without passwords; MariaDB 11.4 health check fixed. This
  repairs the failing release workflow (`fe_sendauth: no password supplied`,
  `Access denied for user 'root'`).
- PHPUnit metadata as attributes (no PHPUnit 11 deprecations on Moodle 5.3).
- Block renders through the page renderer instead of the global `$OUTPUT`.

### Fixed
- Month links in the calendar header: contrast 4.13:1 → body colour with underline (WCAG AA).

### Added
- Playwright user stories: upcoming dates, Scheduling Assistant via keyboard, 360 px and axe.
- `db/removed_files.txt`, `tests/coverage.php`, `tools/coverage_gate.php`, `phpcs.xml`.

## [1.1.1] – Moodle 5.3 support (2026101701)

### Fixed
- `styles.css`: Bootstrap 5 utilities used by the templates (`gap-*`, `fw-*`,
  `lh-1`) are reproduced inside the block, so spacing and emphasis match on
  Moodle 4.5 (Bootstrap 4.6) and 5.x (CCH-B04).
- `classes/external/block_action.php`: imports `core_external\external_api`,
  `external_function_parameters` and `external_value` instead of the legacy
  global aliases, which are not loaded in AJAX requests. "Ja" and "Abschalten"
  on the Termin-Assistent failed silently before.
- `classes/external/block_action.php`: `allowed_classes` now uses
  `\stdClass::class`; `'\stdClass'` produced an incomplete object, so
  `disable_help` could not write `show_help`.

### Added
- `tests/external/block_action_test.php`: PHPUnit coverage for the service.

### Changed
- `version.php`: `requires` set to Moodle 4.5 (2024100700), `supported = [405, 503]`.
- Behat features aligned with the English UI and core steps.
- CI: Moodle 5.3 in the PHPUnit matrix; MariaDB 11.4 / PostgreSQL 17 services.

## [1.1.0] – Moodle Plugin Directory Review

### Added
- `classes/external/block_action.php`: Moodle External Service
  (`block_coursectrldates_block_action`) handling `dismiss_help` and `disable_help`
  splash actions
- `db/services.php`: service registration with `ajax => true`, `loginrequired => true`

### Changed
- `amd/src/block.js`: splash actions now use `core/ajax` instead of raw `fetch()`
- `templates/splash.mustache`: `data-action-url` and `data-sesskey` removed;
  only `data-instanceid` and `data-courseid` remain
- `block_coursectrldates.php`: `$actionurl` and `sesskey()` removed from template context
- `action.php`: reduced to `reset_help` only (browser GET from block config form)

### Fixed
- Issue #2: global PHP function `update_block_show_help()` eliminated; logic moved
  into the External Service class as a private static method (Frankenstyle compliance)
- Issue #3: legacy custom AJAX endpoint replaced by a registered Moodle External Service

---

## [1.0.0] – Stable Release

Release candidate series. See git history for individual patch notes.

### Added
- Mini calendar (1–6 configurable weeks from today) via `local_coursectrl` calendar grid builder
- Chronological event list grouped by day → time slot → activity entry
- Shift buttons (slot, following, per-entry) that open `local_coursectrl/timeline.php` with the shift dialog pre-opened; gated by `local/coursectrl:bulkaction` capability
- Setup-help notification with three configurable triggers (course newly created, course reset, time-dependent activities added), configurable time window, and Ja/Später/Nein dismiss UX
- Privacy API: `user_preference_provider` exporting splash-dismissed preferences per user
- PHPUnit test suite: `config_reader_test`, `setup_help_detector_test`, `splash_state_test`, `privacy_provider_test`, `event_list_test`
- Behat test suite: block visibility, access control, setup-help dismiss flows
- GitHub Actions CI: development workflow (push to non-main) and RC prechecks (push to main)