<?php

admin_gatekeeper();

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', 'modal_info');

$entity = get_entity($guid);

elgg_push_breadcrumb(elgg_echo('modal_info:all'), 'modal_info/all');
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());

$title = elgg_echo('modal_info:edit');
$content = elgg_view_form('modal_info/edit', array(), array(
	'entity' => $entity,
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => '',
));

echo elgg_view_page($title, $layout);