<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\MetadataField;

/**
 * Content access mode field for group profile forms.
 */
class ContentAccessModeField extends MetadataField {

	/**
	 * @param ElggEntity $entity Entity to get values from
	 * @return mixed
	 */
	public function getValues(ElggEntity $entity) {
		return $entity->getContentAccessMode();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param ElggEntity $entity Entity being edited
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
