<?php

/**
 * Group edit form
 *
 * @package ElggGroups
 */

elgg_require_js('elgg/groups/edit');

$entity = elgg_extract('entity', $vars);
$subtype = elgg_extract('subtype', $vars);
$container_guid = elgg_extract('container_guid', $vars) ? : ELGG_ENTITIES_ANY_VALUE;

if (!$subtype) {
	$subtype = 'default';
}

if (!$entity) {
	$entity = hypePrototyper()->entityFactory->build([
		'type' => 'group',
		'subtype' => $subtype == 'default' ? ELGG_ENTITIES_ANY_VALUE : $subtype,
		'container_guid' => $container_guid,
		'access_id' => get_default_access(),
		'membership' => ACCESS_PUBLIC,
	]);
	$entity->setContentAccessMode(ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED);
}

// context needed for input/access view
elgg_push_context("group-edit");

echo hypePrototyper()->form->with($entity, 'groups/edit')->view(['validate' => true]);

elgg_pop_context();
