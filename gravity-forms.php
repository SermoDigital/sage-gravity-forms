<?php
/**
 * Gravity Forms saving for Sage 9 & 10
 * Version: 1.0
 * Author: Justin Lettenmair
 */


if (function_exists('add_action')) {

    function get_sage_gform_save_path($from_theme_root=false) {
        // Set Sage9 friendly path at /theme-directory/resources/assets/gravity-forms-json
        if(is_dir(get_stylesheet_directory() . '/assets')) {
            // This is Sage 9
            $path = '/assets/gravity-forms-json';
        } else {
            // This is Sage 10
            $path = '/resources/assets/gravity-forms-json';
        }

        if(!$from_theme_root) {
            $path = get_stylesheet_directory() . $path;
        }

        return $path;
    }

    // When this theme is first activated (or when Gravity Forms is activated),
    // these forms will be automatically loaded in
    define('GF_THEME_IMPORT_FILE', get_sage_gform_save_path(true) . '/_all_gforms.json');

    add_action('gform_after_save_form', function($form, $is_new) {
        $path = get_sage_gform_save_path();

        // make dir if does not exist
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // bail early if dir isn't writable
        if(!is_writable($path)) {
            return false;
        }

        // Now that we have our path, let's save this form's JSON
        $form_json = json_encode($form);
        $form_id   = $form['id'];
        $file      = 'gform_' . $form_id . '.json';

        // write file
        $f = fopen("{$path}/{$file}", 'w');
        fwrite($f, $form_json);
        fclose($f);

        // Now, to make GF's theme import work, we need all our forms in a single file
        // Collect all our gform files
        $all_form_files = glob("{$path}/gform_*.json");
        $all_forms      = [];
        $form_count     = 0;
        foreach($all_form_files as $form_file) {
            $all_forms[$form_count++] = json_decode(file_get_contents($form_file));
        }

        // Add the version as a top level key (otherwise GF won't accept it as a
        // valid import file)
        $all_forms['version'] = $all_forms[0]->version;

        // Cast the array to an object so PHP will encode the keys as strings
        $all_forms_json = json_encode((object) $all_forms);
        $all_file       = '_all_gforms.json';

        // Write out all the forms to a single file
        $f = fopen("{$path}/{$all_file}", 'w');
        fwrite($f, $all_forms_json);
        fclose($f);

        return true;
    }, 10, 2 );
}
