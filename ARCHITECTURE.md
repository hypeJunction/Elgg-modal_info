# modal_info — Plugin Architecture (Elgg 4.x)

## Summary

A utility plugin for site administrators to create and manage modal info screens that pop up for end users. Admins define the content, targeting (all pages or specific URLs), visibility settings (show once, dismissable), and dimensions. Users see the modal on page load; if dismissable, clicking the dismiss button creates a `viewed` relationship that prevents future display.

## Entity Types

| Type | Subtype | Description |
|------|---------|-------------|
| `object` | `modal_info` | A single modal popup screen |

### Key metadata fields

| Field | Description |
|-------|-------------|
| `title` | Modal heading |
| `description` | Modal body (HTML/longtext) |
| `all_pages` | Boolean — show on every page |
| `page_urls` | Array of URL paths to target |
| `width` | Lightbox width in pixels |
| `height` | Lightbox height in pixels |
| `show_once` | Boolean — auto-marks as viewed on first display |
| `can_dismiss` | Boolean — show dismiss button |

### Relationships

| Relationship | Direction | Meaning |
|---|---|---|
| `viewed` | user → modal_info | User has dismissed/viewed this modal; prevents future display |

## Directory Structure

```
modal_info/
├── actions/modal_info/
│   ├── edit.php          # Create or update a modal_info entity
│   └── dismiss.php       # Add 'viewed' relationship for current user
├── classes/hypeJunction/ModalInfo/
│   └── Bootstrap.php     # Plugin bootstrap (init hooks, entity URL, entity menu)
├── views/default/
│   ├── forms/modal_info/
│   │   └── edit.php      # Admin create/edit form
│   ├── modal_info/
│   │   ├── content.php   # Modal div injected into page footer (triggers lightbox)
│   │   └── preload.php   # Footer hook: queries and injects modal for current user
│   ├── object/
│   │   └── modal_info.php # Summary and full view
│   └── resources/modal_info/
│       ├── all.php       # Admin listing page
│       ├── add.php       # Admin add page
│       ├── edit.php      # Admin edit page
│       └── view.php      # Admin view page
├── elgg-plugin.php       # Plugin declaration, routes, actions
├── composer.json         # Dependencies: elgg/elgg ^4.0
└── autoloader.php        # PSR-4 autoloader for classes/
```

## Registered Hooks

| Hook | Type | Handler | Description |
|------|------|---------|-------------|
| `entity:url` | `object` | `Bootstrap::setEntityUrl` | Returns `/modal_info/view/{guid}` URL |
| `register` | `menu:entity` | `Bootstrap::setupEntityMenu` | Adds edit/delete items for modal_info objects |

## Routes

All routes require `\Elgg\Router\Middleware\AdminGatekeeper`.

| Route name | Path | Resource |
|---|---|---|
| `collection:object:modal_info` | `/modal_info/all` | `modal_info/all` |
| `add:object:modal_info` | `/modal_info/add` | `modal_info/add` |
| `edit:object:modal_info` | `/modal_info/edit/{guid}` | `modal_info/edit` |
| `view:object:modal_info` | `/modal_info/view/{guid}` | `modal_info/view` |

## Actions

| Action | Access | Description |
|--------|--------|-------------|
| `modal_info/edit` | admin | Create or update a modal |
| `modal_info/dismiss` | public | Mark modal as viewed for current user |

## Dependencies

None (no plugin deps; requires only Elgg core ≥ 4.0).

## JavaScript

`views/default/js/modal_info.js` — AMD module. On page load, reads the `#modal-info` div injected by `preload.php` and opens it via `elgg/lightbox`. The dismiss button triggers `elgg/Ajax` to call `action/modal_info/dismiss`.

## Migration Notes (3.x → 4.x)

- `manifest.xml` removed; metadata now in `elgg-plugin.php` `'plugin'` key and `composer.json`
- `start.php` removed
- `Bootstrap` hook callbacks updated to single `\Elgg\Hook` parameter
- `preload.php` raw SQL replaced with `QueryBuilder` subquery
- JS: `elgg.action()` replaced with `elgg/Ajax` module
- `preload.php` now guards against non-logged-in users and admin context to prevent lightbox from appearing on admin pages
- `composer/installers` moved from `require-dev` to `require` with version `^2.0`
