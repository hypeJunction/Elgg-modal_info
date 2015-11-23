<?php

$user_guid = (int) elgg_get_logged_in_user_guid();
$page_url = parse_url(current_page_url(), PHP_URL_PATH);

$ms_all_pages = elgg_get_metastring_id('all_pages');
$ms_true = elgg_get_metastring_id(true);
$ms_page_urls = elgg_get_metastring_id('page_urls');
$ms_page_url = elgg_get_metastring_id($page_url);

$dbprefix = elgg_get_config('dbprefix');

$options = array(
	'types' => 'object',
	'subtypes' => 'modal_info',
	'joins' => array(
		"JOIN {$dbprefix}metadata md ON md.entity_guid = e.guid",
	),
	'wheres' => array(
		"((md.name_id = $ms_all_pages AND md.value_id = $ms_true) OR (md.name_id = $ms_page_urls AND md.value_id = $ms_page_url))",
		"NOT EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships er WHERE er.guid_one = $user_guid AND er.relationship = 'viewed' AND er.guid_two = e.guid)"
	),
	'limit' => 2,
);
		
$modals = elgg_get_entities($options);
if (empty($modals)) {
	return;
}

$entity = $modals[0];
if ($entity->show_once) {
	add_entity_relationship($user_guid, 'viewed', $entity->guid);
}

echo elgg_view('modal_info/content', array(
	'entity' => $entity,
));

