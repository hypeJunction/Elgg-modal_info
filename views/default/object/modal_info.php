<?php

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof ElggObject || $entity->getSubtype() != 'modal_info') {
	return;
}

$full_view = elgg_extract('full_view', $vars);

$description = elgg_view('output/longtext', array(
	'value' => $entity->description,
		));

if ($full_view && elgg_is_xhr()) {
	echo $description;
	return;
}

$metadata = '';
if (!elgg_in_context('widgets')) {
	$metadata = elgg_view_menu('entity', array(
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
		'entity' => $entity,
	));
}

if (!$full_view) {
	echo elgg_view('object/elements/summary', array(
		'entity' => $entity,
		'content' => elgg_get_excerpt($description),
		'metadata' => $metadata,
	));
	return;
}

$page_urls_list = array();

if ($entity->all_pages) {
	$page_urls_list[] = elgg_format_element('div', array(), elgg_echo('modal_info:all_pages'));
} else {
	$page_urls = (array) $entity->page_urls;
	foreach ($page_urls as $url) {
		$page_urls_list[] = elgg_view('output/url', array(
			'href' => elgg_normalize_url($url),
			'target' => '_blank',
		));
	}
}

$page_url_info = elgg_view_module('info', elgg_echo('modal_info:page_urls:list'), implode('<br />', $page_urls_list));

echo elgg_view('object/elements/full', array(
	'entity' => $entity,
	'summary' => elgg_view('object/elements/summary', array(
		'title' => $entity->getDisplayName(),
		'metadata' => $metadata,
	)),
	'body' => $description . $page_url_info,
));

