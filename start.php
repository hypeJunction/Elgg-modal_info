<?php

/**
 * Modal info screens
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'modal_info_init');

/**
 * Initialize the plugin
 * @return void
 */
function modal_info_init() {

	elgg_register_action('modal_info/edit', __DIR__ . '/actions/modal_info/edit.php', 'admin');
	elgg_register_action('modal_info/dismiss', __DIR__ . '/actions/modal_info/dismiss.php');

	elgg_register_page_handler('modal_info', 'modal_info_page_handler');

	elgg_register_plugin_hook_handler('entity:url', 'object', 'modal_info_url');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'modal_info_setup_menu');

	elgg_extend_view('page/elements/footer', 'modal_info/preload');
	
	elgg_register_menu_item('page', array(
		'name' => 'modal_info',
		'href' => 'modal_info/all',
		'text' => elgg_echo('modal_info:all'),
		'context' => 'admin',
		'parent_name' => 'appearance',
		'section' => 'configure',
	));
}

/**
 * Modal info page handler
 * 
 * /modal_info/add
 * /modal_info/edit/<guid>
 * /modal_info/view/<guid>
 * 
 * @param array $segments Page segments
 * @return bool
 */
function modal_info_page_handler($segments) {

	$section = array_shift($segments);

	switch ($section) {

		case 'add' :
			echo elgg_view('resources/modal_info/add');
			return true;

		case 'edit' :
			echo elgg_view('resources/modal_info/edit', array(
				'guid' => $segments[0],
			));
			return true;

		case 'view' :
			echo elgg_view('resources/modal_info/view', array(
				'guid' => $segments[0],
			));
			return true;

		case 'all' :
			echo elgg_view('resources/modal_info/all');
			return true;
	}

	return false;
}

/**
 * Pretty URLs for modal info screens
 *
 * @param string $hook   "entity:url"
 * @param string $type   "object"
 * @param string $return URL
 * @param array  $params Hook params
 * @return string
 */
function modal_info_url($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
		return;
	}

	return "modal_info/view/$entity->guid";
}

/**
 * Setup entity menu
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:entity"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function modal_info_setup_menu($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
		return;
	}

	if ($entity->canEdit()) {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'edit',
					'text' => elgg_echo('edit'),
					'href' => "modal_info/edit/$entity->guid",
					'priority' => 800,
		));

		$return[] = ElggMenuItem::factory(array(
					'name' => 'delete',
					'text' => elgg_view_icon('delete'),
					'href' => "actions/entities/delete?guid=$entity->guid",
					'confirm' => true,
					'priority' => 900,
		));

		return $return;
	}
}
