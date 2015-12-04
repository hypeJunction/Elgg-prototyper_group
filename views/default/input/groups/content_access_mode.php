<?php

$access_mode_params = [
	"id" => "groups-content-access-mode",
	"options_values" => [
		ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED => elgg_echo("groups:content_access_mode:unrestricted"),
		ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY => elgg_echo("groups:content_access_mode:membersonly"),
	]
];

$entity = elgg_extract('entity', $vars);
if ($entity->guid) {
	// Disable content_access_mode field for hidden groups because the setting
	// will be forced to members_only regardless of the entered value
	if ($entity->access_id === $entity->group_acl) {
		$access_mode_params['disabled'] = 'disabled';
		$access_mode_params['value'] = ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY;
	}
}

$vars = array_merge($vars, $access_mode_params);
echo elgg_view("input/select", $vars);

if ($entity && $entity->getContentAccessMode() == ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED) {
	// Warn the user that changing the content access mode to more
	// restrictive will not affect the existing group content
	$access_mode_warning = elgg_echo("groups:content_access_mode:warning");
	echo "<span class='elgg-text-help'>$access_mode_warning</span>";
}
