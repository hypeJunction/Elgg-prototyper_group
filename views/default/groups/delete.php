<?php

$action = elgg_extract('action', $vars);
$entity = elgg_extract('entity', $vars);

if ($action == 'groups/edit' && $entity instanceof ElggGroup && $entity->guid) {
	echo elgg_view("output/url", [
		"text" => elgg_echo("groups:delete"),
		"href" => "action/groups/delete?guid=$entity->guid",
		"confirm" => elgg_echo("groups:deletewarning"),
		"class" => "elgg-button elgg-button-delete float-alt",
	]);
}