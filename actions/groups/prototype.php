<?php

$subtype = get_input('subtype', 'default');
$prototype = hypePrototyper()->ui->buildPrototypeFromInput();

if ($prototype) {
	$plugin = elgg_get_plugin_from_id('prototyper_group');
	$plugin->setSetting("prototype:$subtype", serialize($prototype));
	return elgg_ok_response('', elgg_echo('groups:prototype:success'));
} else {
	return elgg_error_response(elgg_echo('groups:prototype:error'));
}
