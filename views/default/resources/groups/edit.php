<?php

elgg_gatekeeper();

$tab = get_input('tab', 'profile');
echo elgg_view_resource("groups/edit/$tab", $vars);

