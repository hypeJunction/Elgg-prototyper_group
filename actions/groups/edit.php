<?php

$guid = get_input('guid');
$group = get_entity($guid);

if (!$group instanceof ElggGroup && elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !$user->isAdmin()) {
	register_error(elgg_echo("groups:cantcreate"));
	forward(REFERER);
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
	register_error(elgg_echo("groups:cantedit"));
	forward(REFERER);
}

$is_new_group = empty($group->guid);

try {
	$action = hypePrototyper()->action->with($group, 'groups/edit');
	if ($action->validate()) {
		$group = $action->update();
	}
} catch (\hypeJunction\Exceptions\ActionValidationException $ex) {
	register_error(elgg_echo('prototyper:validate:error'));
	forward(REFERER);
} catch (\IOException $ex) {
	register_error(elgg_echo('prototyper:io:error', [$ex->getMessage()]));
	forward(REFERER);
} catch (\Exception $ex) {
	register_error(elgg_echo('prototyper:handle:error', [$ex->getMessage()]));
	forward(REFERER);
}

if ($group) {
	if (elgg_is_xhr()) {
		echo $action->result->output;
	}

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

	system_message(elgg_echo('groups:saved'));
	forward($group->getURL());
} else {
	register_error(elgg_echo('groups:save_error'));
	forward(REFERER);
}