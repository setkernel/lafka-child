# Contributing to lafka-child

This is the site-specific child theme of [lafka-theme](../lafka-theme). It only contains presentation tweaks and per-site customizations. **Business logic that should survive a theme switch belongs in [lafka-plugin](../lafka-plugin).** The BOGO promo and delivery-minimum migration called for in `../LAFKA_AUDIT.md` §6 A-HIGH-1 is **done** — those features now live in `../lafka-plugin/incl/promotions/` (`class-lafka-promotions.php` + admin). As a result the child is now genuinely thin: `functions.php` is ~57 lines and there is no `partials/` directory.

## Local development

```bash
npm ci
composer install

# Boot WP + WC + parent theme + this child
npx @wordpress/env start
# WP runs at http://localhost:8885
```

The `.wp-env.json` mounts `../lafka-theme` as a sibling theme so the parent is loaded.

## Before opening a PR

```bash
npm run lint        # ESLint + Stylelint
composer phpcs
composer phpcbf
```

CI runs the same on every PR (see `.github/workflows/ci.yml`).

## What goes here vs. plugin

| In the child theme (this repo) | In the plugin |
|--------------------------------|---------------|
| CSS variable overrides for parent tokens | Anything that registers a CPT or taxonomy |
| Per-site copy / wording | Anything that owns commerce data (orders, products, addons) |
| Template overrides for visual tweaks | Anything that hooks into WC cart/checkout math |
| Front-end JS overrides via `js/lafka-front.js` | Anything that defines a shortcode or widget |
| Site-specific banners / promo UI | Anything that needs admin settings UI |

## Coding standards

- WordPress-Extra (PHPCS).
- Min PHP 8.1, min WP 6.6.
- Text domain: `lafka` (inherited from parent).
- Banner / inline JS / inline CSS in `functions.php` is acceptable for one-off site features but should be extracted to `js/` and `styles/` once it grows past ~30 lines.

## Releases

Tagging `vX.Y.Z` triggers `.github/workflows/release.yml`, which builds an installable zip excluding dev files.

## Security

Email security issues to security@setkernel.com. Never use public issues.
