<?php

admin_gatekeeper();

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', 'modal_info');

$entity = get_entity($guid);

elgg_push_breadcrumb(elgg_echo('modal_info:all'), 'modal_info/all');
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());

$title = $entity->getDisplayName();
$content = elgg_view_entity($entity, array(
	'full_view' => true,
));

if (elgg_is_xhr()) {
	echo $content;
} else {
	$layout = elgg_view_layout('content', array(
		'entity' => $entity,
		'title' => $title,
		'content' => $content,
		'filter' => '',
	));
	echo elgg_view_page($title, $content);
}

