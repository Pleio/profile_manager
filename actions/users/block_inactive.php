<?php

$user_guids = get_input("user_guids");
$site = elgg_get_site_entity();

if (empty($user_guids)) {
	register_error(elgg_echo("profile_manager:actions:error:non_selected"));
	forward(REFERER);
}

$options = array(
	"type" => "user",
	"guids" => $user_guids,
	"limit" => false
);

$users = elgg_get_entities($options);
if ($users) {
	foreach ($users as $user) {
		if ($user->ban('banned')) {
			remove_entity_relationship($user->guid, "member_of_site", $site->guid);
		} else {
			register_error(elgg_echo('admin:user:ban:no'));
		}
	}
	system_message(elgg_echo("profile_manager:admin:users:inactive:bulk_delete:success"));
} else {
	register_error(elgg_echo("profile_manager:admin:users:inactive:bulk_delete:error"));
}

forward(REFERER);
