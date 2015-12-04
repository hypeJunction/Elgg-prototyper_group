<?php

namespace hypeJunction\Prototyper\Groups;

use ElggEntity;
use hypeJunction\Prototyper\Elements\AttributeField;

class OwnerField extends AttributeField {

	/**
	 * {@inheritdoc}
	 */
	public function handle(ElggEntity $entity) {
		$value = get_input($this->getShortname());
		if (!$entity->guid) {
			return $entity;
		}

		$old_owner_guid = $entity->owner_guid;
		$new_owner_guid = ($value === null) ? $old_owner_guid : (int) $value;

		$owner_has_changed = false;
		$old_icontime = null;
		if (!$new_owner_guid || $new_owner_guid == $old_owner_guid) {
			return $entity;
		}

		$user = elgg_get_logged_in_user_entity();

		// verify new owner is member and old owner/admin is logged in
		if ($entity->isMember(get_user($new_owner_guid)) && ($old_owner_guid == $user->guid || $user->isAdmin())) {
			$entity->owner_guid = $new_owner_guid;
			if ($entity->container_guid == $old_owner_guid) {
				// Even though this action defaults container_guid to the logged in user guid,
				// the group may have initially been created with a custom script that assigned
				// a different container entity. We want to make sure we preserve the original
				// container if it the group is not contained by the original owner.
				$entity->container_guid = $new_owner_guid;
			}

			$metadata = elgg_get_metadata([
				'guid' => $entity->guid,
				'limit' => false,
			]);
			if ($metadata) {
				foreach ($metadata as $md) {
					if ($md->owner_guid == $old_owner_guid) {
						$md->owner_guid = $new_owner_guid;
						$md->save();
					}
				}
			}

			// @todo Remove this when #4683 fixed
			$owner_has_changed = true;
			$old_icontime = $entity->icontime;
		}

		$must_move_icons = ($owner_has_changed && $old_icontime);

		if ($must_move_icons) {
			$filehandler = new ElggFile();
			$filehandler->setFilename('groups');
			$filehandler->owner_guid = $old_owner_guid;
			$old_path = $filehandler->getFilenameOnFilestore();

			$icon_sizes = hypeApps()->iconFactory->getSizes($entity);
			$sizes = array_keys($icon_sizes);
			array_unshift($sizes, '');

			// move existing to new owner
			$filehandler->owner_guid = $entity->owner_guid;
			$new_path = $filehandler->getFilenameOnFilestore();

			foreach ($sizes as $size) {
				rename("$old_path/{$entity->guid}{$size}.jpg", "$new_path/{$entity->guid}{$size}.jpg");
			}
		}

		return $entity;
	}

}
