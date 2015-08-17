<?php

$unfilled_fields = profile_manager_get_unfilled_mandatory_fields();

// forward user to index when all mandatory fields are filled
if (count($unfilled_fields) == 0) {
    if ($redirect_uri = get_input('redirect_uri')) {
        forward($redirect_uri);
    } else {
        forward('/');
    }
}

$content = "<p>" . elgg_echo('profile_manager:complete:description') . "</p>";
$content .= elgg_view_form('profile_manager/complete', array(), array(
    'fields' => $unfilled_fields
));

elgg_extend_view("page/elements/sidebar", "profile_manager/complete/sidebar");

echo elgg_view_page($title, elgg_view_layout("one_sidebar", array(
    'title' => elgg_echo('profile_manager:complete:title'),
    'filter_context' => '',
    'content' => $content

)));