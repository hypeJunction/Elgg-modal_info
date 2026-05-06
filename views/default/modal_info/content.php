<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
	return;
}

$description = elgg_view('output/longtext', [
	'value' => $entity->description,
]);

$dismiss = '';
if ($entity->can_dismiss && elgg_is_logged_in()) {
	$dismiss_button = elgg_view('output/url', [
		'text' => elgg_echo('modal_info:dismiss'),
		'href' => 'action/modal_info/dismiss?guid=' . $entity->guid,
		'is_action' => true,
		'class' => 'elgg-button elgg-button-action modal-info-dismiss',
	]);
	$dismiss = elgg_format_element('div', [
		'class' => 'elgg-foot',
	], $dismiss_button);
}

elgg_require_js('modal_info');
echo elgg_format_element('div', [
	'id' => 'modal-info',
	'class' => 'hidden',
	'data-guid' => $entity->guid,
	'data-title' => $entity->title,
	'data-width' => $entity->width,
	'data-height' => $entity->height,
	'data-src' => $entity->getURL(),
], $description . $dismiss);
