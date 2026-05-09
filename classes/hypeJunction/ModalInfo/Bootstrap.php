<?php

namespace hypeJunction\ModalInfo;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_register_event_handler('entity:url', 'object', [$this, 'setEntityUrl']);
		elgg_register_event_handler('register', 'menu:entity', [$this, 'setupEntityMenu']);
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
	 * Set entity URL for modal_info objects
	 *
	 * @param \Elgg\Event $event "entity:url" event
	 *
	 * @return string|void
	 */
	public function setEntityUrl(\Elgg\Event $event) {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggObject || $entity->getSubtype() != 'modal_info') {
			return;
		}

		return "modal_info/view/{$entity->guid}";
	}

	/**
	 * Setup entity menu for modal_info objects
	 *
	 * @param \Elgg\Event $event "register", "menu:entity" event
	 *
	 * @return \ElggMenuItem[]|void
	 */
	public function setupEntityMenu(\Elgg\Event $event) {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggObject || $entity->getSubtype() != 'modal_info') {
			return;
		}

		$return = $event->getValue();
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
