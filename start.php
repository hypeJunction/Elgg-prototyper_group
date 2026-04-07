<?php

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'prototyper_group_init');

function prototyper_group_init() {

	elgg_register_admin_menu_item('configure', 'group_fields', 'appearance', 40);

	elgg_register_action('groups/prototype', __DIR__ . '/actions/groups/prototype.php', 'admin');
	elgg_register_action('groups/edit', __DIR__ . '/actions/groups/edit.php');

	elgg_register_plugin_hook_handler('prototype', 'groups/edit', 'prototyper_group_get_prototype_fields');

	elgg_register_plugin_hook_handler('profile:fields', 'group', 'prototyper_group_get_config_fields');

	elgg_extend_view('prototyper/elements/submit', 'groups/delete');
}

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

function prototyper_group_get_config_fields($hook, $type, $return, $params) {

	$subtype = elgg_extract('subtype', $params);
	$group = hypePrototyper()->entityFactory->build([
		'type' => 'group',
		'subtype' => $subtype ? : ELGG_ENTITIES_ANY_VALUE
	]);
	$fields = hypePrototyper()->prototype->fields($group, 'groups/edit');

	foreach ($fields as $field) {

		if ($field->getDataType() !== 'metadata') {
			continue;
		}

		$shortname = $field->getShortname();
		if (!array_key_exists($shortname, $return)) {
			$return[$shortname] = $field->getType();
		}
	}

	return $return;
}
