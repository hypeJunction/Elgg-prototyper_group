<?php

$registered_subtypes = get_registered_entity_types('group');
if (empty($registered_subtypes)) {
	return;
}

$registered_subtypes = (array) get_registered_entity_types('group');
array_unshift($registered_subtypes, 'default');

$context = elgg_extract('filter_context', $vars, 'default');

foreach ($registered_subtypes as $subtype) {
	
	elgg_register_menu_item('filter', [
		'name' => $subtype,
		'text' => $subtype === 'default' ? elgg_echo('groups:fields:default') : elgg_echo("item:group:$subtype"),
		'href' => elgg_http_add_url_query_elements(current_page_url(), [
			'subtype' => $subtype,
		]),
		'selected' => $subtype == $context,
	]);
}

echo elgg_view_menu('filter', [
	'sort_by' => 'priority',
	'class' => 'elgg-tabs',
]);