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



