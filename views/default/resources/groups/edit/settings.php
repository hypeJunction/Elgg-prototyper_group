<?php

elgg_gatekeeper();

$guid = elgg_extract('guid', $vars);
$title = elgg_echo("groups:edit");
$group = get_entity($guid);

if (!$group instanceof ElggGroup || !$group->canEdit()) {
	register_error(elgg_echo('groups:noaccess'));
	return;
}

elgg_set_page_owner_guid($group->getGUID());

elgg_push_breadcrumb($group->name, $group->getURL());
elgg_push_breadcrumb($title);

$content = elgg_view("groups/edit", ['entity' => $group]);
if (empty($content)) {
	$content = elgg_format_element('p', [], elgg_echo('groups:edit:extended:empty'));
}

$params = [
	'content' => $content,
	'title' => $title,
	'filter' => elgg_view('filters/groups/edit', [
		'filter_context' => 'settings',
		'entity' => $group,
	]),
];
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
