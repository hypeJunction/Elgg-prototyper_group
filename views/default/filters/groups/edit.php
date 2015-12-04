<?php

$filter_context = elgg_extract('filter_context', $vars);

$tabs = [
	'profile',
	'settings',
];

foreach ($tabs as $tab) {
	elgg_register_menu_item('filter', [
		'name' => "groups:edit:$tab",
		'text' => elgg_echo("groups:edit:$tab"),
		'href' => elgg_http_add_url_query_elements(current_page_url(), [
			'tab' => $tab,
		]),
		'selected' => $tab == $filter_context,
	]);
}

echo elgg_view_menu('filter', [
	'entity' => elgg_extract('entity', $vars),
	'sort_by' => 'priority',
]);