# Changelog

All notable changes to `block_coursectrldates` are documented here.

## [1.0.0] – 2026-05-11

First stable release.

### Added
- Mini calendar (1–6 configurable weeks from today) via `local_coursectrl` calendar grid builder
- Chronological event list grouped by day → time slot → activity entry
- Shift buttons (slot, following, per-entry) that open `local_coursectrl/timeline.php` with the shift dialog pre-opened; gated by `local/coursectrl:bulkaction` capability
- Setup-help notification with three configurable triggers (course newly created, course reset, time-dependent activities added), configurable time window, and Ja/Später/Nein dismiss UX
- Privacy API: `user_preference_provider` exporting splash-dismissed preferences per user
- PHPUnit test suite: `config_reader_test`, `setup_help_detector_test`, `splash_state_test`, `privacy_provider_test`, `event_list_test`
- Behat test suite: block visibility, access control, setup-help dismiss flows
- GitHub Actions CI: development workflow (push to non-main) and RC prechecks (push to main)

### Security
- `action.php` validates block instance against course context (cross-course protection)
- Dismiss action uses POST with FormData (sesskey in POST body, not URL)
- Shift buttons rendered only when user holds `local/coursectrl:bulkaction`
- Unknown actions in `action.php` throw `moodle_exception` rather than silently returning ok

### Fixed
- `PARAM_ALPHAEXT` used for action parameter (previously `PARAM_ALPHA` stripped underscores, breaking `dismiss_help`)
- `$data['showhelp']` written to template context (previously omitted, making setup-help permanently invisible)
- Calendar range limited to configured weeks (previously used full course date range)
- Boolean config defaults match `edit_form.php` (previously all false for empty config)

### Changed
- `list_count()` capped at `MAX_LIST_COUNT = 100`
- `local_coursectrl` dependency pinned to minimum version `2026051100` (was `ANY_VERSION`)
- Privacy provider replaced `null_provider` with proper metadata + preference export
- Capability lang strings corrected (`coursectrldates:*` not `blockcoursetrldates:*`)

## [0.1.x] – 2026-05-03 to 2026-05-04

Alpha development. Initial stub codebase, event list rendering, AMD module,
privacy provider skeleton, PHPUnit skeleton, session 001–002.
