<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup || !$entity->guid) {
	return;
}

if ($entity->owner_guid != elgg_get_logged_in_user_guid() && !elgg_is_admin_logged_in()) {
	return;
}

$members = [];

$dbprefix = elgg_get_config('dbprefix');
$options = [
	"type" => "user",
	"relationship" => "member",
	"relationship_guid" => $entity->getGUID(),
	"inverse_relationship" => true,
	"limit" => false,
	"callback" => false,
	"joins" => ["JOIN {$dbprefix}users_entity ue ON e.guid = ue.guid"],
	"selects" => ['ue.*'],
	"order_by" => 'ue.name ASC',
];

$batch = new ElggBatch("elgg_get_entities_from_relationship", $options);
foreach ($batch as $member) {
	$option_text = "$member->name (@$member->username)";
	$members[$member->guid] = htmlspecialchars($option_text, ENT_QUOTES, "UTF-8", false);
}

$vars['options_values'] = $members;
$vars['id'] = 'groups-owner-guid';
$vars['class'] = 'groups-owner-input';

echo elgg_view('input/select', $vars);

if ($entity->owner_guid == elgg_get_logged_in_user_guid()) {
	echo "<span class='elgg-text-help'>" . elgg_echo("groups:owner:warning") . "</span>";
}