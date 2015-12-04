<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\AttributeField;

class NameField extends AttributeField {

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {
		$value = get_input($this->getShortname());
		$value = strip_tags($value);

		// update access collection name if group name changes
		if ($entity->guid && $value != $entity->name) {
			$entity_name = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
			$ac_name = sanitize_string(elgg_echo('groups:group') . ": " . $entity_name);
			$acl = get_access_collection($entity->group_acl);
			if ($acl) {
				$db_prefix = elgg_get_config('dbprefix');
				$query = "UPDATE {$db_prefix}access_collections SET name = '$ac_name'
				WHERE id = $entity->group_acl";
				update_data($query);
			}
		}

		$entity->name = $value;
		return $entity;
	}

}
