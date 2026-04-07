<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup || !$entity->guid) {
	return;
}
if ($entity->owner_guid != elgg_get_logged_in_user_guid() && !elgg_is_admin_logged_in()) {
	return;
}

$members = [];

$batch = elgg_get_entities([
	'type' => 'user',
	'relationship' => 'member',
	'relationship_guid' => $entity->getGUID(),
	'inverse_relationship' => true,
	'limit' => false,
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
	'batch' => true,
]);

foreach ($batch as $member) {
	$option_text = "{$member->getDisplayName()} (@{$member->username})";
	$members[$member->guid] = htmlspecialchars($option_text, ENT_QUOTES, 'UTF-8', false);
}

$vars['options_values'] = $members;
$vars['id'] = 'groups-owner-guid';
$vars['class'] = 'groups-owner-input';
echo elgg_view('input/select', $vars);

if ($entity->owner_guid == elgg_get_logged_in_user_guid()) {
	echo "<span class='elgg-text-help'>" . elgg_echo("groups:owner:warning") . "</span>";
}
