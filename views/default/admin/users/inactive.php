<?php

$last_login = strtotime("-3 months");

$date = sanitise_int(get_input("last_login"));
if ($date > 0) {
	$last_login = $date;
}

$form_body = elgg_echo("profile_manager:admin:users:inactive:last_login") . ": ";
$form_body .= elgg_view("input/date", array("name" => "last_login", "value" => $last_login, "timestamp" => true));
$form_body .= elgg_view("input/submit", array("value" => elgg_echo("search")));

echo elgg_view("input/form", array("disable_security" => true, "action" => "/admin/users/inactive", "method" => "GET", "body" => $form_body));

echo "<br />";

$dbprefix = elgg_get_config("dbprefix");

$limit = max((int) get_input("limit", 50), 0);
$offset = sanitise_int(get_input("offset", 0), false);

$options = array(
	"type" => "user",
	"limit" => $limit,
	"offset" => $offset,
	"relationship" => "member_of_site",
	"relationship_guid" => elgg_get_site_entity()->getGUID(),
	"inverse_relationship" => true,
	"site_guids" => false,
	"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
	"wheres" => array("e.time_created <= " . $last_login ),
	"order_by" => "ue.last_login"
	);

$users_time_created = elgg_get_entities_from_relationship($options);

$users = array();
foreach ($users_time_created as $user) {
	if ($user->last_login == 0 && $user->last_action <= $last_login) {
		array_push($users, $user);
	} elseif ($user->last_login <= $last_login && $user->last_action <= $last_login && $user->last_login != 0 && $user->last_action != 0) {
		array_push($users, $user);
	}
}

if (!empty($users)) {
	$content = "<table class='elgg-table'>";
	$content .= "<tr>";
	$content .= "<th class='center'>" . elgg_view("input/checkbox", array("name" => "checkall", "default" => false)) . "</th>";
	$content .= "<th>" . elgg_echo("user") . "</th>";
	$content .= "<th>" . elgg_echo("usersettings:statistics:label:lastlogin") . "</th>";
	$content .= "<th>" . elgg_echo("banned") . "</th>";
	$content .= "</tr>";

	foreach ($users as $user) {
		$content .= "<tr>";
		$content .= "<td class='center'>" . elgg_view("input/checkbox", array(
			"name" => "user_guids[]",
			"value" => $user->getGUID(),
			"default" => false)) . "</td>";
		$content .= "<td>" . elgg_view("output/url", array("text" => $user->name, "href" => $user->getURL())) . "</td>";
		$user_last_login = $user->last_login;
		if (empty($user_last_login)) {
			$content .= "<td>" . elgg_echo("profile_manager:admin:users:inactive:never") . "</td>";
		} else {
			$content .= "<td>" . elgg_view_friendly_time($user_last_login) . "</td>";
		}
		$content .= "<td>" . elgg_echo("option:" . $user->banned) . "</td>";
		$content .= "</tr>";
	}

	$content .= "</table>";

	$options["count"] = true;
	$count = elgg_get_entities_from_relationship($options);

	$content .= elgg_view("navigation/pagination", array("offset" => $offset, "limit" => $limit, "count" => $count));

	$delete_button = elgg_view("input/submit", array(
		"value" => elgg_echo("profile_manager:admin:users:inactive:block_users"),
		"class" => "elgg-button-submit mvs",
		"onclick" => "return confirm(elgg.echo('profile_manager:admin:users:inactive:confirm_block_users'));"
	));
	$content .= $delete_button;

	echo elgg_view("input/form", array(
		"id" => "profile-manager-bulk-block-inactive",
		"action" => "action/profile_manager/users/block_inactive",
		"body" => $content
	));

	echo "<br /><hr><p>";
	echo elgg_echo("profile_manager:admin:users:inactive:download_description");
	echo "</p>";
	$download_link = elgg_add_action_tokens_to_url("/action/profile_manager/users/export_inactive?last_login=" . $last_login);
	echo "" . elgg_view("input/button", array("value" => elgg_echo("profile_manager:admin:users:inactive:download"), "onclick" => "document.location.href='" . $download_link . "'", "class" => "elgg-button-action"));


} else {
	$content = elgg_echo("notfound");
}











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

$users_all_inactive = elgg_get_entities_from_relationship($options);


$confirm_user_list = "";
foreach ($users_all_inactive as $user) {
	if (($user->time_created <= $last_login && $user->last_login == 0) || $user->last_login != 0)  {
		$confirm_user_list .= $user->name . " ";
	}
}

$block_users_link = elgg_add_action_tokens_to_url("/action/profile_manager/users/block_inactive?last_login=" . $last_login);
$confirm_message =  elgg_echo("profile_manager:admin:users:inactive:confirm_block_users") . date("Y-m-d", $last_login) . ". " .  elgg_echo("profile_manager:admin:users:inactive:confirm_block_user_list") . $confirm_user_list;
$delete_link = elgg_view("output/confirmlink", array(
	"text" =>  elgg_echo("profile_manager:admin:users:inactive:block_users"),
	"class" => "elgg-button elgg-button-submit float-alt mvs",
	"title" => elgg_echo("delete"),
	"confirm" => $confirm_message,
	"href" => $block_users_link ));