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

$form_vars = array(
	'enctype' => 'multipart/form-data',
	'validate' => true,
	'class' => 'elgg-form-alt',
);

$content = elgg_view_form('groups/edit', $form_vars, groups_prepare_form_vars($group));

$params = [
	'content' => $content,
	'title' => $title,
	'filter' => elgg_view('filters/groups/edit', [
		'filter_context' => 'profile',
		'entity' => $group,
	]),
];
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
