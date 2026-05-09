<?php

$guid = (int) get_input('guid');
$entity = $guid ? get_entity($guid) : null;
$user = elgg_get_logged_in_user_entity();

if ($entity && $user) {
	$user->addRelationship($entity->guid, 'viewed');
}

return elgg_ok_response();
