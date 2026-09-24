# Contributing to lafka-child

This is the site-specific child theme of [lafka-theme](https://github.com/setkernel/lafka-theme). It holds only per-install presentation overrides. **Business logic that should survive a theme switch belongs in [lafka-plugin](https://github.com/setkernel/lafka-plugin); default feature styling belongs in lafka-theme.** `ThinLayerTest` enforces this: `functions.php` stays under 120 lines, and `style.css` holds no parent-owned selectors or hardcoded hex colors outside a `:root { --lafka-*: … }` override block.

The BOGO promo and delivery-minimum features live in the plugin's Promotions module (`incl/promotions/`), which is default-OFF — flip **Lafka → Modules → Promotions** on any site that relied on the old child implementation.

## Local development

```bash
npm ci
composer install

# Boot WP + WooCommerce + lafka-plugin + the parent theme + this child
npx @wordpress/env start
# WP runs at http://localhost:8885
```

`.wp-env.json` expects `lafka-theme` and `lafka-plugin` checked out as siblings of this repo.

## Before opening a PR

```bash
npm run lint        # ESLint + Stylelint
composer phpcs      # WordPress-Extra (composer phpcbf auto-fixes)
composer test       # PHPUnit
```

CI (`.github/workflows/ci.yml`) runs the version-SSOT check plus all three; the `.githooks/pre-push` hook runs the three locally.

## What goes here vs. elsewhere

| In the child theme (this repo) | In lafka-theme | In lafka-plugin |
|---|---|---|
| `--lafka-*` token overrides in `style.css` | Default styling for anything the parent or plugin emits | CPTs, taxonomies, shortcodes, widgets |
| Per-install CSS for page-builder content on this site | Templates and template overrides | Commerce data, cart/checkout math, promotions |
| Small filter/action tweaks in `functions.php` | Customizer controls, presets, fonts | Admin settings screens |
| Opt-in JS overrides (`js/lafka-front.js`) | | |

Copy-ready recipes: `examples/customizations.php.example` (PHP hooks), `examples/style-overrides.css.example` (CSS), `examples/lafka-front.js.example` (JS — copy to `js/lafka-front.js` to activate; it is only enqueued when present).

## Coding standards

- WordPress-Extra (PHPCS).
- Min PHP 8.1, min WP 6.6.
- Text domain: `lafka` (inherited from parent).
- If a tweak grows past a few dozen lines, it is probably a feature — move it to lafka-theme or lafka-plugin.

## Releases

`npm version <patch|minor|major>` bumps `package.json` (canonical) and syncs the `style.css` header. Pushing the `vX.Y.Z` tag runs `.github/workflows/release.yml`, which builds an installable zip excluding dev files.

## Security

Email security issues to security@setkernel.com. Never use public issues.
