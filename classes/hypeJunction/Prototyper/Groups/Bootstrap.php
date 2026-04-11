<?php

namespace hypeJunction\Prototyper\Groups;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		\elgg_register_admin_menu_item('configure', 'group_fields', 'appearance', 40);
	}
}
