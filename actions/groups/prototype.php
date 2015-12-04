<?php

$subtype = get_input('subtype', 'default');
$prototype = hypePrototyper()->ui->buildPrototypeFromInput();

if ($prototype && elgg_set_plugin_setting("prototype:$subtype", serialize($prototype), 'prototyper_group')) {
	system_message(elgg_echo('groups:prototype:success'));
} else {
	register_error(elgg_echo('groups:prototype:error'));
}

forward(REFERER);
