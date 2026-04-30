<?php

return [
	'plugin' => [
		'version' => '4.0.0',
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

	'hooks' => [
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

	'view_extensions' => [
		'prototyper/elements/submit' => [
			'groups/delete' => [],
		],
	],
];
