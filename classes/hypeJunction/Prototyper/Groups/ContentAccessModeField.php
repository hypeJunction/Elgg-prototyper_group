<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\MetadataField;

class ContentAccessModeField extends MetadataField {

	public function getValues(ElggEntity $entity) {
		return $entity->getContentAccessMode();
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {
		$value = (string) get_input($this->getShortname());
		if (!isset($entity->group_acl) || $entity->access_id != $entity->group_acl) {
			// only set content access mode if the group is not hidden
			$entity->setContentAccessMode($value);
		}
		return $entity;
	}

}
