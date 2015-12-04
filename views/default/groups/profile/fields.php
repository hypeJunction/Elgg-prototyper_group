<?php

$entity = elgg_extract('entity', $vars);

echo hypePrototyper()->profile->with($entity, 'groups/edit')->filter(function(hypeJunction\Prototyper\Elements\Field $field) {
	return !in_array($field->getShortname(), ['name', 'vis', 'membership', 'content_access_mode', 'tools', 'owner_guid', 'icon']);
})->view();
