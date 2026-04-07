<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\AttributeField;

class NameField extends AttributeField {

	public function handle(ElggEntity $entity) {
		$value = get_input($this->getShortname());
		$value = strip_tags($value);

		if ($entity->guid && $value != $entity->name) {
			$entity_name = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
			$ac_name = elgg_echo('groups:group') . ": " . $entity_name;
			$acl = $entity->getOwnedAccessCollection('group_acl');
			if ($acl) {
				$acl->name = $ac_name;
				$acl->save();
			}
		}

		$entity->name = $value;
		return $entity;
	}

}
