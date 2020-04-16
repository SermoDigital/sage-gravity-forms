<?php
/**
 * Gravity Forms saving for Sage 9 & 10
 * Version: 1.0
 * Author: Justin Lettenmair
 */


if (function_exists('add_action')) {

    add_action('gform_after_save_form', function($form, $is_new) {
        // Set Sage9 friendly path at /theme-directory/resources/assets/gravity-forms-json
        if(is_dir(get_stylesheet_directory() . '/assets')) {
            // This is Sage 9
            $path = get_stylesheet_directory() . '/assets/gravity-forms-json';
        } else {
            // This is Sage 10
            $path = get_stylesheet_directory() . '/resources/assets/gravity-forms-json';
        }

        // make dir if does not exist
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // bail early if dir isn't writable
        if(!is_writable($path)) {
            return false;
        }

        // Now that we have our path, let's save this form's JSON
        $form_json = json_encode( $form );
        $form_id   = $form['id'];
        $file      = 'form_' . $form_id . '.json';

        // write file
        $f = fopen("{$path}/{$file}", 'w');
        fwrite($f, $form_json);
        fclose($f);

        return true;
    }, 10, 2 );


    /**
     * How should we handle loading these? We can use the logic from
     * gravityformscli:
     * https://github.com/gravityforms/gravityformscli/blob/master/includes/class-gf-cli-form.php#L241
     * but we need a way to initiate it. Maybe a custom tab here:
     * https://docs.gravityforms.com/gform_export_page_view/
     */
}
