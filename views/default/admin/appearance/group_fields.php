<?php

$registered_subtypes = (array) elgg_entity_types_with_capability('searchable');
$group_subtypes = [];
foreach ($registered_subtypes as $type => $subtypes) {
	if ($type === 'group') {
		$group_subtypes = $subtypes ?: [];
		break;
	}
}

array_unshift($group_subtypes, 'default');

$subtype = get_input('subtype', $group_subtypes[0]);

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
		'subtype' => $subtype,
	]
]);
