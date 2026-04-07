<?php

elgg_register_event_handler('init', 'system', 'modal_info_init');

function modal_info_init() {
	elgg_register_plugin_hook_handler('entity:url', 'object', 'modal_info_url');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'modal_info_setup_menu');
	elgg_extend_view('page/elements/footer', 'modal_info/preload');
	elgg_register_menu_item('page', \ElggMenuItem::factory([
		'name' => 'modal_info',
		'href' => 'modal_info/all',
		'text' => elgg_echo('modal_info:all'),
		'context' => 'admin',
		'parent_name' => 'appearance',
		'section' => 'configure',
	]));
}

function modal_info_url($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
		return;
	}
	return "modal_info/view/{$entity->guid}";
}

function modal_info_setup_menu($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
		return;
	}
	if ($entity->canEdit()) {
		$return[] = \ElggMenuItem::factory([
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => "modal_info/edit/{$entity->guid}",
			'priority' => 800,
		]);
		$return[] = \ElggMenuItem::factory([
			'name' => 'delete',
			'text' => elgg_view_icon('delete'),
			'href' => "action/entity/delete?guid={$entity->guid}",
			'confirm' => true,
			'priority' => 900,
		]);
		return $return;
	}
}
