<?php

$guid = get_input('guid');
$entity = get_entity($guid);
$user = elgg_get_logged_in_user_entity();

if ($entity && $user) {
	if (get_input('dismiss', true)) {
		add_entity_relationship($user->guid, 'viewed', $entity->guid);
	} else {
		remove_entity_relationship($user->guid, 'viewed', $entity->guid);
	}
}

forward(REFERRER);

