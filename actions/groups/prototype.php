<?php

$subtype = get_input('subtype', 'default');
$prototype = hypePrototyper()->ui->buildPrototypeFromInput();

if ($prototype && elgg_set_plugin_setting("prototype:$subtype", serialize($prototype), 'prototyper_group')) {
	return elgg_ok_response('', elgg_echo('groups:prototype:success'));
} else {
	return elgg_error_response(elgg_echo('groups:prototype:error'));
}
