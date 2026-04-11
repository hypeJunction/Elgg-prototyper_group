<?php

namespace hypeJunction\Prototyper\Groups;

class Hooks {

	/**
	 * Populate prototype fields for group edit form
	 *
	 * @param \Elgg\Hook $hook 'prototype', 'groups/edit'
	 * @return array
	 */
	public static function getPrototypeFields(\Elgg\Hook $hook) {
		$return = $hook->getValue();

		$entity = $hook->getParam('entity');
		$subtype = $entity->getSubtype() ?: 'default';
		$prototype = \elgg_get_plugin_setting("prototype:$subtype", 'prototyper_group');

		if (!$prototype && $subtype != 'default') {
			$prototype = \elgg_get_plugin_setting('prototype:default', 'prototyper_group');
		}

		if ($prototype) {
			$prototype_fields = unserialize($prototype);
			$return = array_merge($return, $prototype_fields);
		} else {
			$fields = \elgg_get_config('group');

			$return['icon'] = [
				'type' => 'icon',
				'data_type' => 'file',
				'label' => [
					\get_current_language() => \elgg_echo('groups:icon'),
				],
				'help' => false,
			];

			$return['description'] = [
				'type' => 'description',
				'data_type' => 'attribute',
				'label' => [
					\get_current_language() => \elgg_echo('groups:description'),
				],
				'help' => false,
			];

			foreach ($fields as $shortname => $input_type) {
				$return[$shortname] = [
					'type' => $input_type,
					'data_type' => 'metadata',
					'label' => [
						\get_current_language() => \elgg_echo("groups:$shortname"),
					],
					'help' => false,
				];
			}
		}

		$return['name'] = [
			'type' => 'name',
			'data_type' => 'attribute',
			'class_name' => NameField::class,
			'label' => [
				\get_current_language() => \elgg_echo('groups:name'),
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
			'class_name' => MembershipField::class,
			'label' => [
				\get_current_language() => \elgg_echo("groups:membership"),
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
			'class_name' => VisibilityField::class,
			'label' => [
				\get_current_language() => \elgg_echo("groups:visibility"),
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
			'class_name' => ContentAccessModeField::class,
			'label' => [
				\get_current_language() => \elgg_echo("groups:content_access_mode"),
			],
			'help' => false,
			'priority' => 900,
		];

		$return['owner_guid'] = [
			'type' => 'select',
			'data_type' => 'attribute',
			'input_view' => 'input/groups/owner',
			'output_view' => false,
			'class_name' => OwnerField::class,
			'label' => [
				\get_current_language() => \elgg_echo("groups:owner"),
			],
			'help' => false,
			'priority' => 900,
		];

		$return['tools'] = [
			'type' => 'checkboxes',
			'data_type' => 'metadata',
			'input_view' => 'input/groups/tools',
			'output_view' => false,
			'class_name' => ToolsField::class,
			'label' => false,
			'help' => false,
			'priority' => 900,
		];

		return $return;
	}

	/**
	 * Provide group profile fields from prototype config
	 *
	 * @param \Elgg\Hook $hook 'fields', 'group:group'
	 * @return array
	 */
	public static function getConfigFields(\Elgg\Hook $hook) {
		$return = $hook->getValue();

		$subtype = $hook->getParam('subtype');
		$group = \hypePrototyper()->entityFactory->build([
			'type' => 'group',
			'subtype' => $subtype ?: ELGG_ENTITIES_ANY_VALUE,
		]);
		$fields = \hypePrototyper()->prototype->fields($group, 'groups/edit');

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
}
