<?php

/**
 * Group Fields Prototyper
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'prototyper_group_init');

/**
 * Initialize the plugin
 * @return void
 */
function prototyper_group_init() {

	elgg_register_admin_menu_item('configure', 'group_fields', 'appearance', 40);

	elgg_register_action('groups/prototype', __DIR__ . '/actions/groups/prototype.php', 'admin');
	elgg_register_action('groups/edit', __DIR__ . '/actions/groups/edit.php');

	elgg_register_plugin_hook_handler('prototype', 'groups/edit', 'prototyper_group_get_prototype_fields');

	elgg_get_plugin_setting('profile:fields', 'group', 'prototyper_group_get_config_fields');

	elgg_extend_view('prototyper/elements/submit', 'groups/delete');
}

/**
 * Returns prototyped fields
 *
 * @param string $hook   "prototype"
 * @param string $type   "groups/edit"
 * @param array  $return Fields
 * @param array  $params Hook params
 * @return array
 */
function prototyper_group_get_prototype_fields($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	$subtype = $entity->getSubtype() ? : 'default';
	$prototype = elgg_get_plugin_setting("prototype:$subtype", 'prototyper_group');

	if (!$prototype && $subtype != 'default') {
		$prototype = elgg_get_plugin_setting('prototype:default', 'prototyper_group');
	}

	if ($prototype) {
		$prototype_fields = unserialize($prototype);
		$return = array_merge($return, $prototype_fields);
	} else {
		$fields = elgg_get_config('group');

		$return['icon'] = [
			'type' => 'icon',
			'data_type' => 'file',
			'label' => [
				get_current_language() => elgg_echo('groups:icon'),
			],
			'help' => false,
		];

		$return['description'] = [
			'type' => 'description',
			'data_type' => 'attribute',
			'label' => [
				get_current_language() => elgg_echo('groups:description'),
			],
			'help' => false,
		];

		foreach ($fields as $shortname => $input_type) {
			$return[$shortname] = [
				'type' => $input_type,
				'data_type' => 'metadata',
				'label' => [
					get_current_language() => elgg_echo("groups:$shortname"),
				],
				'help' => false,
			];
		}
	}

	// Not adding these above, as we want them to persist, even if they are deleted from the UI
	$return['name'] = [
		'type' => 'name',
		'data_type' => 'attribute',
		'class_name' => \hypeJunction\Prototyper\Groups\NameField::class,
		'label' => [
			get_current_language() => elgg_echo('groups:name'),
		],
		'help' => false,
		'priority' => 1,
	];

	$return['membership'] = [
		'type' => 'membership',
		'data_type' => 'metadata',
		'id' => 'groups-membership',
		'input_view' => 'input/groups/membership',
		'output_view' => false,
		'class_name' => hypeJunction\Prototyper\Groups\MembershipField::class,
		'label' => [
			get_current_language() => elgg_echo("groups:membership"),
		],
		'help' => false,
		'priority' => 900,
	];

	// treating this as metadata so that it gets handled after the entity has been saved once and group_acl has been created
	$return['vis'] = [
		'type' => 'access',
		'data_type' => 'metadata',
		'id' => 'groups-vis',
		'input_view' => 'input/groups/visibility',
		'output_view' => false,
		'class_name' => hypeJunction\Prototyper\Groups\VisibilityField::class,
		'label' => [
			get_current_language() => elgg_echo("groups:visibility"),
		],
		'help' => false,
		'priority' => 900,
	];

	$return['content_access_mode'] = [
		'type' => 'content_access_mode',
		'data_type' => 'metadata',
		'id' => 'groups-content-access-mode',
		'input_view' => 'input/groups/content_access_mode',
		'output_view' => false,
		'class_name' => hypeJunction\Prototyper\Groups\ContentAccessModeField::class,
		'label' => [
			get_current_language() => elgg_echo("groups:content_access_mode"),
		],
		'help' => false,
		'priority' => 900,
	];

	$return['owner_guid'] = [
		'type' => 'select',
		'data_type' => 'attribute',
		'input_view' => 'input/groups/owner',
		'output_view' => false,
		'class_name' => hypeJunction\Prototyper\Groups\OwnerField::class,
		'label' => [
			get_current_language() => elgg_echo("groups:owner"),
		],
		'help' => false,
		'priority' => 900,
	];

	$return['tools'] = [
		'type' => 'checkboxes',
		'data_type' => 'metadata',
		'input_view' => 'input/groups/tools',
		'output_view' => false,
		'class_name' => hypeJunction\Prototyper\Groups\ToolsField::class,
		'label' => false,
		'help' => false,
		'priority' => 900,
	];

	return $return;
}

/**
 * Populates the profile fields config with prototyped values
 *
 * @param string $hook   "prototype"
 * @param string $type   "groups/edit"
 * @param array  $return Fields
 * @param array  $params Hook params
 * @return array
 */
function prototyper_group_get_config_fields($hook, $type, $return, $params) {

	$subtype = elgg_extract('subtype', $params);
	$group = hypePrototyper()->entityFactory->build([
		'type' => 'group',
		'subtype' => $subtype ? : ELGG_ENTITIES_ANY_VALUE
	]);
	$fields = hypePrototyper()->prototype->fields($group, 'groups/edit');

	foreach ($fields as $field) {
		/* @var $field \hypeJunction\Prototyper\Elements\Field */

		if ($field->getDataType() !== 'metadata') {
			// only add metadata fields
			continue;
		}

		$shortname = $field->getShortname();
		if (!array_key_exists($shortname, $return)) {
			$return[$shortname] = $field->getType();
		}
	}

	return $return;
}
