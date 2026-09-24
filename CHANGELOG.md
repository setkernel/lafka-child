# Changelog

All notable changes to lafka-child are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/). Older history lives in git
tags + GitHub Releases.

## [6.2.0] — 2026-09-24

### Changed
- `style.css` trimmed to the header + the active page-builder CLS rules
  (12.7 KB → 4.3 KB on every page); commented recipes moved to
  `examples/style-overrides.css.example`, migration history left to git.
  Active rules byte-identical.
- The all-comments `js/lafka-front.js` no longer ships (it was enqueued on
  every page for no effect); it is now `examples/lafka-front.js.example` —
  copy it to `js/lafka-front.js` to opt in.
- Header floors corrected to lafka-theme ≥ 7.0.0 / lafka-plugin ≥ 10.0.0.
- CONTRIBUTING rewritten to match ThinLayerTest and CI; points at the
  roadmap, not the retired audit doc.
- `.wp-env.json` now boots the full stack (WP 7.0 / PHP 8.4 / WC 10.9.1 +
  lafka-plugin) and maps the parent to `themes/lafka` so the child activates.

### Removed
- Unused dev dependencies `brain/monkey` and `yoast/phpunit-polyfills`.
- Test suite cut from 26 to 7 tests: removed ratchets for code deleted long
  ago (promotions/PDP/editorial/perf symbols, cart CLS, RTL stub, retired
  literals), file/function-existence checks and doc-text pins. Kept the guards
  that protect real behavior: the `Template: lafka` header, version SSOT,
  release packaging, the functions.php size limit and style.css ownership.

### Fixed
- The release zip now ships the GPL `LICENSE` (it was excluded).

### Security
- Dev toolchain: PHP_CodeSniffer 3.13.6, WPCS 3.4.1, PHPCSUtils 1.2.3
  (clears three code-execution advisories in the lint toolchain).

## [6.1.0] — 2026-07-07

Phase NX1 coordinated release (with lafka-theme 7.0.0 / lafka-plugin 10.0.0).
The child remains a deliberately thin override layer (enforced by
ThinLayerTest).

### Changed
- CONTRIBUTING.md points at the live audit document (`AUDIT_2026-06-27.md`);
  stale references to retired docs removed (guarded by a test).
- Release zips exclude dev-only files (19 → 5 files: the pure runtime layer).

### Compatibility
- Parent theme lafka-theme ≥ 7.0.0. The parent's legacy-options migration
  writes to the ACTIVE theme's `theme_mods` — on child-active sites (like
  production) this is the child's mods store, which is the correct,
  consistent behavior; no child changes required.
