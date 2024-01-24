<?php
/*
 * Plugin Name:       Post Author Switcher
 * Description:       This plugin can help you to switch post author of multiple posts at one click. You have option to switch post author by post type & from another user instead of selected post author. 
 * Author:            Geek Code Lab
 * Author URI:        https://geekcodelab.com/
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       post-author-switcher
 * Version:           1.0
 */

if (!defined('ABSPATH')) exit;

if (!defined("GCLPAS_PLUGIN_DIR_PATH"))

	define("GCLPAS_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

if (!defined("GCLPAS_PLUGIN_URL"))

	define("GCLPAS_PLUGIN_URL", plugins_url() . '/' . basename(dirname(__FILE__)));

define("GCLPAS_BUILD", '1.0');

define("GCLPAS_TEXT_DOMAIN", 'post-author-switcher');

require_once(GCLPAS_PLUGIN_DIR_PATH . '/functions.php');

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'gclpas_plugin_add_settings_link');

function gclpas_plugin_add_settings_link($links){
	$support_link = '<a href="https://geekcodelab.com/contact/"  target="_blank" >' . __('Support','post-author-switcher') . '</a>';
	array_unshift($links, $support_link);

	$settings_link = '<a href="admin.php?page=gclpas-settings">' . __('Settings','post-author-switcher') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}