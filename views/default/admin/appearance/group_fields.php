<?php

$registered_subtypes = (array) get_registered_entity_types('group');
array_unshift($registered_subtypes, 'default');

$subtype = get_input('subtype', $registered_subtypes[0]);

echo elgg_view('admin/appearance/group_fields/filter', [
	'filter_context' => $subtype,
]);

echo elgg_view_form('prototyper/edit', [
	'action' => '/action/groups/prototype',
		], [
	'action' => 'groups/edit',
	'attributes' => [
		'type' => 'group',
		'subtype' => $subtype == 'default' ? ELGG_ENTITIES_ANY_VALUE : $subtype,
	],
	'params' => [
		// add a hidden field
		'subtype' => $subtype,
	]
]);
