<?php

elgg_gatekeeper();

$container_guid = elgg_extract('container_guid', $vars);
$container = get_entity($container_guid);
$subtype = elgg_extract('subtype', $vars) ? : ELGG_ENTITIES_ANY_VALUE;

if ($container && !$container->canWriteToContainer(0, 'group', $subtype)) {
	throw new \Elgg\Exceptions\HttpException(elgg_echo('groups:noaccess'), 403);
}

elgg_set_page_owner_guid($container_guid);

$title = elgg_echo('groups:add');
elgg_push_breadcrumb($title);

if (elgg_get_plugin_setting('limited_groups', 'groups') != 'yes' || elgg_is_admin_logged_in()) {
	$form_vars = [
		'enctype' => 'multipart/form-data',
		'validate' => true,
		'class' => 'elgg-form-alt',
	];
	$content = elgg_view_form('groups/edit', $form_vars, $vars);
} else {
	$content = elgg_echo('groups:cantcreate');
}

$params = [
	'content' => $content,
	'title' => $title,
	'filter' => '',
];
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
