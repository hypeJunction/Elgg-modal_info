<?php

return [
	'plugin' => [
		'id' => 'modal_info',
		'name' => 'Modal Info Screens',
		'description' => 'Custom modal info screen popups',
		'version' => '5.0.0',
		'author' => 'Ismayil Khayredinov',
		'categories' => ['help'],
	],

	'bootstrap' => \hypeJunction\ModalInfo\Bootstrap::class,

	'actions' => [
		'modal_info/edit' => [
			'access' => 'admin',
		],
		'modal_info/dismiss' => [],
	],

	'routes' => [
		'collection:object:modal_info' => [
			'path' => '/modal_info/all',
			'resource' => 'modal_info/all',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'add:object:modal_info' => [
			'path' => '/modal_info/add',
			'resource' => 'modal_info/add',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'edit:object:modal_info' => [
			'path' => '/modal_info/edit/{guid}',
			'resource' => 'modal_info/edit',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
		'view:object:modal_info' => [
			'path' => '/modal_info/view/{guid}',
			'resource' => 'modal_info/view',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
	],
];
