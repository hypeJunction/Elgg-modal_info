# modal_info — Plugin Architecture (Elgg 6.x)

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
├── composer.json         # Dependencies: elgg/elgg ^5.0
└── autoloader.php        # PSR-4 autoloader for classes/
```

## Registered Events

Registered programmatically in `Bootstrap::init()` via `elgg_register_event_handler()` (Elgg 5.x unified event system).

| Event | Object type | Handler | Description |
|-------|-------------|---------|-------------|
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

None (no plugin deps; requires only Elgg core ≥ 5.0, PHP ≥ 8.0).

## JavaScript

`views/default/js/modal_info.js` — AMD module. On page load, reads the `#modal-info` div injected by `preload.php` and opens it via `elgg/lightbox`. The dismiss button triggers `elgg/Ajax` to call `action/modal_info/dismiss`.

## Migration Notes (4.x → 5.x)

- `composer.json` bumped to PHP ≥ 8.0 and `elgg/elgg ^5.0`
- `elgg_register_plugin_hook_handler()` replaced with `elgg_register_event_handler()` in `Bootstrap::init()`
- `\Elgg\Hook` callback parameter updated to `\Elgg\Event` in `setEntityUrl()` and `setupEntityMenu()`
- `$hook->getValue()` / `$hook->getEntityParam()` updated to `$event->getValue()` / `$event->getEntityParam()`
- Integration tests updated: `elgg_get_session()->setLoggedInUser()` → `_elgg_services()->session_manager->setLoggedInUser()` (Elgg 5.x session API)
- Docker test stack upgraded: `php:8.1-apache`, `mysql:8.0`, `elgg/elgg ~5.1.0`
