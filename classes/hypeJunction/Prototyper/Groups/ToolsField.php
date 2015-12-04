<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\MetadataField;

class ToolsField extends MetadataField {

	public function getValues(ElggEntity $entity) {
		$values = array();
		$tools = elgg_get_config('group_tool_options');
		if ($tools) {
			foreach ($tools as $tool) {
				$option_name = $tool->name . "_enable";
				if (isset($entity->$option_name)) {
					$values[$option_name] = $entity->$option_name;
				} else {
					$values[$option_name] = $tool->default_on ? 'yes' : 'no';
				}
			}
		}

		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {
		// Set group tool options
		$tools = elgg_get_config('group_tool_options');
		if ($tools) {
			foreach ($tools as $tool) {
				$option_toggle_name = $tool->name . "_enable";
				$option_default = $tool->default_on ? 'yes' : 'no';
				$value = get_input($option_toggle_name);

				// if already has option set, don't change if no submission
				if (isset($entity->$option_toggle_name) && !isset($value)) {
					continue;
				}

				$entity->$option_toggle_name = $value ? $value : $option_default;
			}
		}
		return $entity;
	}

}
