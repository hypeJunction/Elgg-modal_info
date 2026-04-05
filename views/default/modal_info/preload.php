<?php

// Rewritten for Elgg 3.x — metadata uses name/value directly (no metastrings)

$user_guid = (int) elgg_get_logged_in_user_guid();
$page_url = parse_url(current_page_url(), PHP_URL_PATH);
$dbprefix = elgg_get_config('dbprefix');

$options = [
    'types' => 'object',
    'subtypes' => 'modal_info',
    'metadata_name_value_pairs' => [
        [
            'name' => 'all_pages',
            'value' => true,
        ],
    ],
    'wheres' => [
        "NOT EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships er WHERE er.guid_one = {$user_guid} AND er.relationship = 'viewed' AND er.guid_two = e.guid)",
    ],
    'limit' => 2,
];

// Also check for page-specific modals
$page_options = [
    'types' => 'object',
    'subtypes' => 'modal_info',
    'metadata_name_value_pairs' => [
        [
            'name' => 'page_urls',
            'value' => $page_url,
        ],
    ],
    'wheres' => [
        "NOT EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships er WHERE er.guid_one = {$user_guid} AND er.relationship = 'viewed' AND er.guid_two = e.guid)",
    ],
    'limit' => 2,
];

$modals = elgg_get_entities($options);
if (empty($modals)) {
    $modals = elgg_get_entities($page_options);
}

if (empty($modals)) {
    return;
}

$entity = $modals[0];
if ($entity->show_once) {
    add_entity_relationship($user_guid, 'viewed', $entity->guid);
}
echo elgg_view('modal_info/content', ['entity' => $entity]);
