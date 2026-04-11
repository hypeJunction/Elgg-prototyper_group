<?php

$guid = get_input('guid');
$group = get_entity($guid);

if (!$group instanceof ElggGroup && elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !elgg_get_logged_in_user_entity()->isAdmin()) {
	return elgg_error_response(elgg_echo("groups:cantcreate"));
}

if (!$group instanceof ElggGroup) {
	$subtype = get_input('subtype') ? : ELGG_ENTITIES_ANY_VALUE;
	$container_guid = get_input('container_guid') ? : elgg_get_logged_in_user_guid();
	$group = hypePrototyper()->entityFactory->build([
		'type' => 'group',
		'subtype' => $subtype,
		'access_id' => ACCESS_PUBLIC,
		'container_guid' => $container_guid,
	]);
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo("groups:cantedit"));
}

$is_new_group = empty($group->guid);

try {
	$action = hypePrototyper()->action->with($group, 'groups/edit');
	if ($action->validate()) {
		$group = $action->update();
	}
} catch (\hypeJunction\Exceptions\ActionValidationException $ex) {
	return elgg_error_response(elgg_echo('prototyper:validate:error'));
} catch (\Elgg\Exceptions\FileSystem\IOException $ex) {
	return elgg_error_response(elgg_echo('prototyper:io:error', [$ex->getMessage()]));
} catch (\Exception $ex) {
	return elgg_error_response(elgg_echo('prototyper:handle:error', [$ex->getMessage()]));
}

if ($group) {
	if ($is_new_group) {
		elgg_set_page_owner_guid($group->guid);
		$group->join($group->getOwnerEntity());
		elgg_create_river_item([
			'view' => 'river/group/create',
			'action_type' => 'create',
			'subject_guid' => $group->owner_guid,
			'object_guid' => $group->guid,
		]);
	}

	return elgg_ok_response('', elgg_echo('groups:saved'), $group->getURL());
} else {
	return elgg_error_response(elgg_echo('groups:save_error'));
}
