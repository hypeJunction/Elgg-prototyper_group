## Elgg 6.x Migration (2026-05-09)

- Bumped `elgg/elgg` to `~6.1.0`, `php` to `>=8.1`, added `ext-intl`
- `elgg_require_js('elgg/groups/edit')` → `elgg_import_esm('elgg/groups/edit')` in forms/groups/edit.php
- Added docker/elgg6/ test stack
- No data migration needed

<a name="5.0.0"></a>
# 5.0.0 (2026-05-04)

### Breaking Changes

* **Elgg 5.x**: Requires Elgg 5.x and PHP 8.2+. Dropped Elgg 4.x support.
* **Events API**: `'hooks'` key in `elgg-plugin.php` replaced with `'events'` (Elgg 5.x unifies hooks/events).
* **Handler type hints**: `\Elgg\Hook` → `\Elgg\Event` in all handler method signatures.

### Features

* **Prototype storage**: Admin-saved prototypes now stored as `json_encode()` instead of `serialize()`. Existing `serialize()` blobs are migrated automatically via the `MigratePrototypesToJson` upgrade batch.

### Bug Fixes

* **`get_current_language()`**: Replaced removed function with `elgg_get_current_language()`.
* **Visibility view**: Guard `$entity->group_acl` access against null entity in test/render context.

### Infrastructure

* Docker stack updated to PHP 8.2-apache + MySQL 8.0 (`--innodb-use-native-aio=0`).
* `ELGG_SITE_URL` uses internal Docker hostname `http://elgg/` (required for Elgg 5.x).
* `elgg_reset_system_cache()` replaced with `elgg_clear_caches()` in install script.


<a name="4.0.0"></a>
# 4.0.0 (2026-04-17)

### Breaking Changes

* **Elgg 4.x**: Requires Elgg 4.x. Dropped Elgg 2.x/3.x support.
* **start.php removed**: Plugin no longer uses `start.php`; all declarations moved to `elgg-plugin.php`.
* **Autoloading**: Migrated from `psr-0` to `psr-4` autoloading.

### Bug Fixes

* **Hooks**: Guard `elgg_get_config('group')` with `(array)` cast when no custom group fields registered.
* **Field classes**: Cast subtype to `string` in `getConfigFields` to satisfy Elgg 4.x strict type requirement.

### Infrastructure

* Added per-plugin Docker test stack (`docker/`) for Elgg 4.x verification.
* Added PHPUnit integration test suite (`tests/phpunit/integration/`).
* Added `phpunit.xml` configuration.
* Added `ARCHITECTURE.md`.


<a name="1.0.1"></a>
## [1.0.1](https://github.com/hypeJunction/Elgg-prototyper_group/compare/1.0.0...v1.0.1) (2016-06-09)


### Bug Fixes

* **input:** add missing access level to visiblity ([d5bf711](https://github.com/hypeJunction/Elgg-prototyper_group/commit/d5bf711))



<a name="1.0.0"></a>
# 1.0.0 (2015-12-04)


### Features

* **releases:** initial commit ([4c26102](https://github.com/hypeJunction/Elgg-prototyper_group/commit/4c26102))



