# prototyper_group — Architecture (Elgg 7.x)

## Summary

prototyper_group integrates hypePrototyper with Elgg groups, replacing the
default group edit form with a prototyper-driven field schema. Admins can
configure a field prototype per group subtype via `/admin/appearance/group_fields`.
The plugin also exposes group-specific field classes (name, membership,
visibility, content access mode, owner, tools) for hypePrototyper to use when
building and saving group profiles.

## Plugin metadata

- **Plugin ID**: `prototyper_group`
- **Type**: Elgg 5.x plugin (no `start.php`)
- **Direct deps**: `hypeprototyper`
- **Transitive deps**: `hypeapps`, `hypelists` (via hypeprototyper)

## Module map

```
elgg-plugin.php          Plugin declaration: actions, events, upgrades, view_extensions
actions/
  groups/edit.php        Process group create/edit via hypePrototyper action API
  groups/prototype.php   Admin action: save prototype config for a group subtype
classes/hypeJunction/Prototyper/Groups/
  Hooks.php              Event handlers: getPrototypeFields, getConfigFields
  Upgrade/
    MigratePrototypesToJson.php  Upgrade batch: converts stored serialize() blobs to json_encode()
  NameField.php          AttributeField: assign group name, update ACL name
  MembershipField.php    MetadataField: public/private membership toggle
  VisibilityField.php    MetadataField: group visibility (access_id)
  ContentAccessModeField.php  MetadataField: members-only vs open content
  OwnerField.php         AttributeField: group ownership transfer + icon move
  ToolsField.php         MetadataField: enable/disable group tools
views/default/
  admin/appearance/      Admin UI for configuring group field prototypes
  filters/groups/edit.php  Tab filter for group edit (profile / settings)
  forms/groups/edit.php  Prototyper-driven group edit form
  groups/edit.php        Render group edit resource (delegates to tab sub-view)
  groups/delete.php      Extends prototyper submit with a delete button
  groups/profile/fields.php  Render custom group profile fields on the group page
  input/groups/          Custom input views: membership, visibility, content_access_mode, owner, tools
  resources/groups/      Page resources: add, edit, edit/profile, edit/settings
languages/en.php         English translations
```

## Key flows

### Group creation / edit

1. `resources/groups/add.php` or `resources/groups/edit.php` renders `forms/groups/edit.php`
2. The form delegates to `hypePrototyper()->prototype->render($group, 'groups/edit')`
3. On submit, `actions/groups/edit.php` calls `hypePrototyper()->action->with($group, 'groups/edit')`
4. The `prototype:groups/edit` hook fires → `Hooks::getPrototypeFields()` returns the field schema
5. Each field's `handle()` saves the value to the entity
6. On success, redirects to the group page

### Prototype configuration

1. Admin visits `/admin/appearance/group_fields`
2. `views/default/admin/appearance/group_fields.php` renders the configuration form
3. On submit, `actions/groups/prototype` saves `prototype:{subtype}` as a plugin setting
4. `Hooks::getPrototypeFields()` uses this stored prototype on subsequent group edits

### Field schema resolution

`Hooks::getPrototypeFields()` (event: `prototype`, `groups/edit`):
- Reads `prototype:{subtype}` (or `prototype:default`) from plugin settings
- If found: `json_decode()` (or `unserialize()` fallback for pre-5.x data) and use as the field spec
- If not: build a default spec from `elgg_get_config('group')` + fixed core fields

## Migration Notes (6.x → 7.x)

- `elgg/elgg ~7.0.0`, `php >=8.3` in `composer.json`.
- Docker test stack added for Elgg 7.x (docker/elgg7/) with PHP 8.3.
- No breaking changes: no CSS Crush syntax, no direct `ElggObject` instantiation, no removed Elgg APIs.
- No data migration needed.

## Migration Notes (5.x → 6.x)

- `elgg/elgg ~6.1.0`, `php >=8.1`, `ext-intl` added in `composer.json`.
- `elgg_require_js('elgg/groups/edit')` → `elgg_import_esm('elgg/groups/edit')` in forms/groups/edit.php.
- Docker test stack added for Elgg 6.x (docker/elgg6/).
- No data migration needed.

## Elgg 5.x migration notes

- `'hooks'` key replaced with `'events'` in `elgg-plugin.php` (Elgg 5.x unifies hooks and events)
- `Hooks.php` type-hints `\Elgg\Event` (concrete class in 5.x, was `\Elgg\Hook` interface in 4.x)
- `get_current_language()` replaced with `elgg_get_current_language()` (removed in 5.x)
- Prototype storage migrated from `serialize()` to `json_encode()` — `MigratePrototypesToJson` upgrade batch converts existing data; `Hooks.php` has fallback `unserialize()` for data not yet migrated
- `unserialize()` calls use `['allowed_classes' => false]` to prevent PHP object injection
- `elgg_reset_system_cache()` replaced with `elgg_clear_caches()` in install script (removed in 5.x)
- Docker stack upgraded to PHP 8.2-apache + MySQL 8.0 (`--innodb-use-native-aio=0`); `ELGG_SITE_URL` uses internal Docker hostname `http://elgg/`
- `elgg_get_config('group')` may return `null` when no custom group fields registered; wrapped with `(array)` cast
