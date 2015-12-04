<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use ElggGroup;
use hypeJunction\Prototyper\Elements\MetadataField;

class VisibilityField extends MetadataField {

	/**
	 * {@inheritdoc}
	 */
	public function getValues(ElggEntity $entity) {
		return $entity->access_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(ElggEntity $entity) {
		$result = parent::validate($entity);
		if (get_input($this->getShortname()) == ACCESS_PRIVATE && elgg_get_plugin_setting("hidden_groups", "groups") != "yes") {
			$result->setFail(elgg_echo('groups:hidden_groups_disabled'));
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {

		$value = get_input($this->getShortname(), ACCESS_LOGGED_IN);
		$visibility = (int) $value;

		if ($visibility == ACCESS_PRIVATE) {
			// Make this group visible only to group members. We need to use
			// ACCESS_PRIVATE on the form and convert it to group_acl here
			// because new groups do not have acl until they have been saved once.
			$visibility = $entity->group_acl;

			// Force all new group content to be available only to members
			$entity->setContentAccessMode(ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY);
		}

		$entity->access_id = $visibility;
		return $entity;
	}

}
