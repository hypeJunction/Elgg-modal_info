<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
	return;
}

$description = elgg_view('output/longtext', array(
	'value' => $entity->description,
		));

$dismiss = '';
if ($entity->can_dismiss && elgg_is_logged_in()) {
	$dismiss_chkbx = elgg_view('input/checkbox', array(
		'value' => 1,
		'default' => false,
		'class' => 'modal-info-dismiss',
		'data-action' => 'action/modal_info/dismiss?guid=' . $entity->guid,
	));
	$dismiss = elgg_format_element('label', array(), $dismiss_chkbx . elgg_echo('modal_info:dismiss'));
}

elgg_load_css('lightbox');
elgg_load_js('lightbox');
elgg_require_js('modal_info');
echo elgg_format_element('div', array(
	'id' => 'modal-info',
	'class' => 'hidden',
	'data-guid' => $entity->guid,
	'data-title' => $entity->title,
	'data-width' => $entity->width,
	'data-height' => $entity->height,
	'data-src' => $entity->getURL(),
		), $description . $dismiss);
