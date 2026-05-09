<?php

$registered_subtypes = (array) elgg_entity_types_with_capability('searchable');
$group_subtypes = [];
foreach ($registered_subtypes as $type => $subtypes) {
	if ($type === 'group') {
		$group_subtypes = $subtypes ?: [];
		break;
	}
}

if (empty($group_subtypes)) {
	return;
}

array_unshift($group_subtypes, 'default');

$context = elgg_extract('filter_context', $vars, 'default');

foreach ($group_subtypes as $subtype) {
	elgg_register_menu_item('filter', [
		'name' => $subtype,
		'text' => $subtype === 'default' ? elgg_echo('groups:fields:default') : elgg_echo("item:group:$subtype"),
		'href' => elgg_http_add_url_query_elements(elgg_get_current_url(), [
			'subtype' => $subtype,
		]),
		'selected' => $subtype == $context,
	]);
}

echo elgg_view_menu('filter', [
	'sort_by' => 'priority',
	'class' => 'elgg-tabs',
]);
