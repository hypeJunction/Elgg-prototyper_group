# prototyper_group — Architecture (Elgg 4.x)

## Summary

prototyper_group integrates hypePrototyper with Elgg groups, replacing the
default group edit form with a prototyper-driven field schema. Admins can
configure a field prototype per group subtype via `/admin/appearance/group_fields`.
The plugin also exposes group-specific field classes (name, membership,
visibility, content access mode, owner, tools) for hypePrototyper to use when
building and saving group profiles.

## Plugin metadata

- **Plugin ID**: `prototyper_group`
- **Type**: Elgg 4.x plugin (no `start.php`)
- **Direct deps**: `hypeprototyper`
- **Transitive deps**: `hypeapps`, `hypelists` (via hypeprototyper)

## Module map

```
elgg-plugin.php          Plugin declaration: actions, hooks, view_extensions
actions/
  groups/edit.php        Process group create/edit via hypePrototyper action API
  groups/prototype.php   Admin action: save prototype config for a group subtype
classes/hypeJunction/Prototyper/Groups/
  Hooks.php              Hook handlers: getPrototypeFields, getConfigFields
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

`Hooks::getPrototypeFields()` (hook: `prototype`, `groups/edit`):
- Reads `prototype:{subtype}` (or `prototype:default`) from plugin settings
- If found: deserialize and use as the field spec (admin-configured prototype)
- If not: build a default spec from `elgg_get_config('group')` + fixed core fields

## Elgg 4.x migration notes

- No `start.php` — plugin metadata and hook registrations live entirely in `elgg-plugin.php`
- `Hooks.php` type-hints `\Elgg\Hook` (interface) for plugin hook callbacks — correct for 4.x
- `elgg_get_config('group')` may return `null` when no custom group fields registered; wrapped with `(array)` cast
- `getConfigFields` uses `(string)` cast on subtype to satisfy `ElggEntity::setSubtype()` strict string requirement
- Prototype data stored as `serialize()` — no migration needed (existing data format unchanged)
