<?php

set_time_limit(0);

$last_login = sanitise_int(get_input("last_login"), false);
$site = elgg_get_site_entity();

if (!empty($last_login)) {
	$dbprefix = elgg_get_config("dbprefix");

	$options = array(
		"type" => "user",
		"limit" => false,
		"relationship" => "member_of_site",
		"relationship_guid" => elgg_get_site_entity()->getGUID(),
		"inverse_relationship" => true,
		"site_guids" => false,
		"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
		"wheres" => array("ue.last_login <= " . $last_login),
		"order_by" => "ue.last_login"
	);

	$users = elgg_get_entities_from_relationship($options);
	if ($users) {
		foreach ($users as $user) {
			if ($user->time_created <= $last_login && $user->last_login == 0) {
				remove_entity_relationship($user->guid, "member_of_site", $site->guid);
			} else if ($user->last_login != 0) {
				remove_entity_relationship($user->guid, "member_of_site", $site->guid);
			}
		}
	} else {
		system_message(elgg_echo("InvalidParameterException:NoDataFound"));
		forward(REFERER);
	}
} else {
	register_error(elgg_echo("InvalidParameterException:NoDataFound"));
	forward(REFERER);
}
