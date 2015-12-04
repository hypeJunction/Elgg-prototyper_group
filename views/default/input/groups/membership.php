<?php

$vars['id'] = 'groups-membership';
$vars['options_values'] = [
	ACCESS_PRIVATE => elgg_echo("groups:access:private"),
	ACCESS_PUBLIC => elgg_echo("groups:access:public"),
];

echo elgg_view("input/select", $vars);