<?php

admin_gatekeeper();

elgg_push_breadcrumb(elgg_echo('modal_info:all'), 'modal_info/all');

elgg_register_menu_item('title', array(
	'name' => 'add',
	'href' => 'modal_info/add',
	'text' => elgg_echo('modal_info:add'),
	'link_class' => 'elgg-button elgg-button-action',
));

$title = elgg_echo('modal_info:all');
$content = elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => 'modal_info',
	'no_results' => elgg_echo('modal_info:no_results'),
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => '',
));

echo elgg_view_page($title, $layout);