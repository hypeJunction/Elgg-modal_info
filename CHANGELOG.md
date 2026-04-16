# Changelog

## 4.0.0 (Elgg 4.x)

### Breaking Changes
- Requires Elgg 4.0+ (`elgg/elgg: ^4.0`, PHP ≥ 7.4)
- `manifest.xml` removed; plugin metadata now in `elgg-plugin.php` (`'plugin'` key) and `composer.json`
- `start.php` removed; all initialization in `Bootstrap` class

### Changes
- Bootstrap hook callbacks updated to Elgg 4.x single-parameter `\Elgg\Hook` signature
- `preload.php`: raw SQL replaced with `QueryBuilder` subquery for DB portability
- `preload.php`: only shows modals to logged-in non-admin users (prevents lightbox on admin pages)
- JS `modal_info.js`: `elgg.action()` replaced with `elgg/Ajax` module (Elgg 4.x)
- `composer.json`: proper 4.x metadata, `composer/installers ^2.0` in `require`

### Developer Notes
- PHPUnit integration tests run with `ELGG_DB_PREFIX=elgg_` inside the per-plugin Docker stack
- Playwright tests use `ELGG_BASE_URL: http://elgg` (Docker network) with per-test DB cleanup
