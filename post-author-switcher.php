<?php
/*
 * Plugin Name:       Post Author Switcher
 * Description:       This plugin can help you to switch post author of multiple posts at one click. You have option to switch post author by post type & from another user instead of selected post author.
 * Author:            Geek Code Lab
 * Author URI:        https://geekcodelab.com/
 * License:       	  GPLv2 or later
 * Text Domain:       post-author-switcher
 * Version:           1.1.0
 */
if (!defined('ABSPATH')) exit;
define("GCLPAS_BUILD", '1.1.0');

if (!defined("GCLPAS_PLUGIN_BASENAME"))
	define("GCLPAS_PLUGIN_BASENAME", plugin_basename(__FILE__));

if (!defined("GCLPAS_PLUGIN_DIR"))
	define("GCLPAS_PLUGIN_DIR", plugin_basename(__DIR__));

if (!defined("GCLPAS_PLUGIN_DIR_PATH"))
	define("GCLPAS_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

if (!defined("GCLPAS_PLUGIN_URL"))
	define("GCLPAS_PLUGIN_URL", plugins_url() . '/' . basename(dirname(__FILE__)));


require_once(GCLPAS_PLUGIN_DIR_PATH . '/functions.php');
require_once(GCLPAS_PLUGIN_DIR_PATH . '/admin/user-switch-author.php');
require (GCLPAS_PLUGIN_DIR_PATH .'updater/updater.php');

add_filter("plugin_action_links_post-author-switcher/post-author-switcher.php", 'gclpas_plugin_add_settings_link');

function gclpas_plugin_add_settings_link($links){
	$support_link = '<a href="https://geekcodelab.com/contact/"  target="_blank" >' . __('Support','post-author-switcher') . '</a>';
	array_unshift($links, $support_link);

	$settings_link = '<a href="admin.php?page=gclpas-settings">' . __('Settings','post-author-switcher') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}

add_action('upgrader_process_complete', 'gclpas_updater_activate'); // remove  transient  on plugin  update