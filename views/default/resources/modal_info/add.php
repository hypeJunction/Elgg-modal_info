<?php

elgg_push_breadcrumb(elgg_echo('modal_info:all'), 'modal_info/all');

$site = elgg_get_site_entity();

$title = elgg_echo('modal_info:add');
$content = elgg_view_form('modal_info/edit', [], [
	'container' => $site,
]);

$layout = elgg_view_layout('default', [
	'title' => $title,
	'content' => $content,
	'filter' => '',
]);

echo elgg_view_page($title, $layout);
