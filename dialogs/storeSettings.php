<?php
require_once('../wp-includes/cache.php');
require_once('../wp-includes/plugin.php');
require_once('../wp-includes/formatting.php');
require_once('../wp-includes/option.php');

add_action('wp_ajax_storeSettings', 'storeSettings_callback');

function storeSettings_callback()
{
    if (!is_null($_POST['username']) && !is_null($_POST['token'])) {
        update_option('vzaarAPIusername', $_POST['username']);
        update_option('vzaarAPItoken', $_POST['token']);
        echo ("Settings saved!");
    } else {
        echo("No username and token were sent");
    }

    die(); // this is required to return a proper result
}
