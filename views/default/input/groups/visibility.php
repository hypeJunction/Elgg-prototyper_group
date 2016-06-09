<?php

$visibility_options = [
	ACCESS_PRIVATE => elgg_echo("groups:access:group"),
	ACCESS_LOGGED_IN => elgg_echo("LOGGED_IN"),
	ACCESS_PUBLIC => elgg_echo("PUBLIC"),
];

$entity = elgg_extract('entity', $vars);
if ($entity->group_acl) {
	unset($visibility_options[ACCESS_PRIVATE]);
	$visibility_options[$entity->group_acl] = elgg_echo('groups:access:group');
}

if (elgg_get_config("walled_garden")) {
	unset($visibility_options[ACCESS_PUBLIC]);
}

if (elgg_get_plugin_setting("hidden_groups", "groups") != "yes") {
	unset($visibility_options[ACCESS_PRIVATE]);
}

$vars['id'] = 'groups-vis';
$vars['options_values'] = $visibility_options;
$vars['entity_type'] = 'group';
$vars['entity_subtype'] = $entity instanceof ElggGroup ? $entity->getSubtype() : '';

echo elgg_view("input/access", $vars);
