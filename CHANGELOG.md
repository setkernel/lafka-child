# Changelog

All notable changes to lafka-child are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/). Older history lives in git
tags + GitHub Releases.

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
