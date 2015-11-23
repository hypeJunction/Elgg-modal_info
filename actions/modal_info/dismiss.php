<?php

$guid = get_input('guid');
$entity = get_entity($guid);
$user = elgg_get_logged_in_user_entity();

if ($entity && $user) {
	add_entity_relationship($user->guid, 'viewed', $entity->guid);
}

forward(REFERRER);

