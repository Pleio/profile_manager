<?php

gatekeeper();

$user = elgg_get_logged_in_user_entity();

$name_to_field = array();
$allowed_fields = array();

$values = get_input('custom_profile_fields');

$categories = profile_manager_get_categorized_fields($user, true);
foreach ($categories['fields'] as $category) {
    foreach ($category as $field) {
        $name_to_field[$field->metadata_name] = $field;
        $allowed_fields[] = $field->metadata_name;
    }
}

// only return allowed fields
$filled_fields = array_intersect($allowed_fields, array_keys($values));

foreach ($filled_fields as $fieldname) {
    $accesslevel = get_input('accesslevel');

    if ($accesslevel && array_key_exists($fieldname, $accesslevel)) {
        $access_id = $accesslevel[$fieldname];
    } elseif (in_array($fieldname, array('Adres','Postcode','Plaats','Geslacht'))) { // @todo: temporarily fix to have these fields private. real solution is to have a default permission level per profile field.
        $access_id = ACCESS_PRIVATE;
    } else {
        $access_id = get_default_access($user);
    }

    $field = $name_to_field[$fieldname];
    $value = $values[$fieldname];

    if ($field->metadata_type == "tags" || $field->output_as_tags == "yes") {
        $value = string_to_tag_array($value);
    }

    if (is_array($value)) {
        foreach ($value as $i => $tag) {
            if ($i == 0) { $multiple = false; } else { $multiple = true; }
            create_metadata($user->guid, $fieldname, $tag, 'text', $user->guid, $access_id, $multiple);
        }
    } else {
        create_metadata($user->guid, $fieldname, $value, 'text', $user->guid, $access_id);
    }

}

$redirect_uri = get_input('redirect_uri');
if ($redirect_uri) {
    forward($redirect_uri);
} else {
    forward(REFERER);
}