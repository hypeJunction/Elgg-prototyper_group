<?php

return [
	'plugin' => [
		'version' => '7.0.0',
		'dependencies' => [
			'hypeprototyper' => [
				'position' => 'after',
			],
		],
	],

	'actions' => [
		'groups/prototype' => [
			'access' => 'admin',
		],
		'groups/edit' => [],
	],

	'events' => [
		'prototype' => [
			'groups/edit' => [
				\hypeJunction\Prototyper\Groups\Hooks::class . '::getPrototypeFields' => [],
			],
		],
		'fields' => [
			'group:group' => [
				\hypeJunction\Prototyper\Groups\Hooks::class . '::getConfigFields' => [],
			],
		],
	],

	'upgrades' => [
		\hypeJunction\Prototyper\Groups\Upgrade\MigratePrototypesToJson::class,
	],

	'view_extensions' => [
		'prototyper/elements/submit' => [
			'groups/delete' => [],
		],
	],
];
