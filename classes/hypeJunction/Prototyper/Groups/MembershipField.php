<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\MetadataField;

class MembershipField extends MetadataField {

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {
		$value = get_input($this->getShortname());
		if (!isset($entity->membership) || isset($value)) {
			$is_public_membership = ($value == ACCESS_PUBLIC);
			$entity->membership = $is_public_membership ? ACCESS_PUBLIC : ACCESS_PRIVATE;
		}
		return $entity;
	}

}
