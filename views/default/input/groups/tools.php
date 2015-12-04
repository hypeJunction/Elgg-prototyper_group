<?php

$tools = elgg_get_config('group_tool_options');
if (empty($tools)) {
	return;
}

$values = elgg_extract('value', $vars);

usort($tools, create_function('$a, $b', 'return strcmp($a->label, $b->label);'));

foreach ($tools as $tool) {
	$tool_toggle_name = $tool->name . "_enable";
	$value = elgg_extract($tool_toggle_name, $values);
	echo elgg_format_element('div', [], elgg_view('input/checkbox', array(
		'name' => $tool_toggle_name,
		'value' => 'yes',
		'default' => 'no',
		'checked' => ($value === 'yes') ? true : false,
		'label' => $tool->label
	)));
}

