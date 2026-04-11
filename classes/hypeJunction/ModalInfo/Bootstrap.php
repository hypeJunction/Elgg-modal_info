<?php

namespace hypeJunction\ModalInfo;

use Elgg\Includer;
use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		Includer::requireFileOnce($this->plugin->getPath() . '/autoloader.php');
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_register_plugin_hook_handler('entity:url', 'object', [$this, 'setEntityUrl']);
		elgg_register_plugin_hook_handler('register', 'menu:entity', [$this, 'setupEntityMenu']);
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

	/**
	 * {@inheritdoc}
	 */
	public function ready() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}

	/**
	 * Set entity URL for modal_info objects
	 */
	public function setEntityUrl(\Elgg\Hook $hook) {
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggObject || $entity->getSubtype() != 'modal_info') {
			return;
		}
		return "modal_info/view/{$entity->guid}";
	}

	/**
	 * Setup entity menu for modal_info objects
	 */
	public function setupEntityMenu(\Elgg\Hook $hook) {
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggObject || $entity->getSubtype() != 'modal_info') {
			return;
		}
		$return = $hook->getValue();
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
}
