<?php

if (!defined('ABSPATH')) exit;

/**
 * License manager module
 */
function gclpas_updater_utility() {
    $prefix = 'GCLPAS_';
    $settings = [
        'prefix' => $prefix,
        'get_base' => GCLPAS_PLUGIN_BASENAME,
        'get_slug' => GCLPAS_PLUGIN_DIR,
        'get_version' => GCLPAS_BUILD,
        'get_api' => 'https://download.geekcodelab.com/',
        'license_update_class' => $prefix . 'Update_Checker'
    ];

    return $settings;
}

function gclpas_updater_activate() {

    // Refresh transients
    delete_site_transient('update_plugins');
    delete_transient('gclpas_plugin_updates');
    delete_transient('gclpas_plugin_auto_updates');
}

require_once(GCLPAS_PLUGIN_DIR_PATH . 'updater/class-update-checker.php');
