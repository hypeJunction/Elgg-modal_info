<?php

$guid = get_input('guid');
$container_guid = get_input('container_guid');

$entity = get_entity($guid);
if (!$entity) {
	$entity = new ElggObject();
	$entity->subtype = 'modal_info';
	$entity->container_guid = $container_guid;
}

$entity->title = get_input('title');
$entity->description = get_input('description');
$entity->access_id = get_input('access_id');

$all_pages = get_input('all_pages');
if ($all_pages) {
	unset($entity->page_urls);
	$entity->all_pages = true;
} else {
	$page_urls = explode(PHP_EOL, get_input('page_urls', ''));
	foreach ($page_urls as $key => $page_url) {
		$page_urls[$key] = parse_url($page_url, PHP_URL_PATH);
	}
	$entity->page_urls = $page_urls;
}

$entity->width = get_input('width', 600);
$entity->height = get_input('height', 600);
$entity->show_once = get_input('show_once') ? : false;
$entity->can_dismiss = get_input('can_dismiss') ? : false;

if ($entity->save()) {
	system_message(elgg_echo('modal_info:edit:success'));
	forward('/modal_info/all');
} else {
	register_error(elgg_echo('modal_info:edit:error'));
	forward(REFERRER);
}
