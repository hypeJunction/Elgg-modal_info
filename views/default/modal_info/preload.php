<?php

$user_guid = (int) elgg_get_logged_in_user_guid();
$page_url = parse_url(current_page_url(), PHP_URL_PATH);

$not_viewed_clause = function(\Elgg\Database\QueryBuilder $qb, $alias) use ($user_guid) {
	$rel = $qb->subquery('entity_relationships');
	$rel->select('1')
		->where($qb->compare("$rel.guid_one", '=', $user_guid, ELGG_VALUE_INTEGER))
		->andWhere($qb->compare("$rel.relationship", '=', 'viewed', ELGG_VALUE_STRING))
		->andWhere("$rel.guid_two = $alias.guid");

	return "NOT EXISTS ({$rel->getSQL()})";
};

$options = [
	'type' => 'object',
	'subtype' => 'modal_info',
	'metadata_name_value_pairs' => [
		[
			'name' => 'all_pages',
			'value' => true,
		],
	],
	'wheres' => [
		$not_viewed_clause,
	],
	'limit' => 2,
];

$page_options = [
	'type' => 'object',
	'subtype' => 'modal_info',
	'metadata_name_value_pairs' => [
		[
			'name' => 'page_urls',
			'value' => $page_url,
		],
	],
	'wheres' => [
		$not_viewed_clause,
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
	$user = elgg_get_logged_in_user_entity();
	if ($user) {
		$user->addRelationship($entity->guid, 'viewed');
	}
}
echo elgg_view('modal_info/content', ['entity' => $entity]);
