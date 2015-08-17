<?php $fields = elgg_extract("fields", $vars, array()); ?>

<p>
    <?php foreach ($fields as $field) {
        echo "<div>";
            echo "<label>" . $field->getTitle() . "*</label>";
            if($hint = $field->getHint()){
                $fields_result .= "<span class='custom_fields_more_info' id='more_info_" . $field->metadata_name . "'></span>";
                $fields_result .= "<span class='custom_fields_more_info_text' id='text_more_info_" . $field->metadata_name . "'>" . $hint . "</span>";
            }
            $fields_result .= "<br />";

            echo elgg_view("input/{$field->metadata_type}", array(
                'name' => 'custom_profile_fields[' . $field->metadata_name . ']',
                'options' => $field->getOptions(),
                'required' => 'required' // all retrieved fields are mandatory
            ));
        echo "</div>";
    } ?>
</p>

<p>
    <?php
    echo elgg_view('input/hidden', array(
        'name' => 'redirect_uri',
        'value' => get_input('redirect_uri')
    ));

    echo elgg_view('input/submit', array(
        'value' => elgg_echo('save')
    ));
    ?>
</p>