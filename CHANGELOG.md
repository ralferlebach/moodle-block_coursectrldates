# Changelog

All notable changes to `block_coursectrldates` are documented here.

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