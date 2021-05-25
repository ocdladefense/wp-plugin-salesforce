<?php



/**
 * @package SalesforcePlugin
 */

/*
Plugin Name: Salesforce Plugin
Plugin URI: 
Description: This is a plugin that ...
Version: 1.0.0
Author: Ruslan Kalashnikov
Author URI: https://www.ocdla.org/
License: GPLv2 or later
Text Domain: wp-salesforce-plugin
*/

// If this file is accessed directly, abort.
defined('ABSPATH') or die('You shall not pass!');



// Setting a CONSTANT for the plugin dir path
define('MY_PLUGIN_DIR', plugin_dir_path(__FILE__));

include WP_CONTENT_DIR . "/oauth-config.php";
require MY_PLUGIN_DIR . "/includes/globals.php";
require MY_PLUGIN_DIR . "/includes/oauth.inc";
require MY_PLUGIN_DIR . "/includes/redirect.inc";



add_action( 'init', "add_urls" );

add_filter('request',"query_vars");

add_filter('template_include',"setting_template");

add_filter('init','flush_rules'); 

add_action( 'init', 'process_oauth_redirect_uri' );

add_action('wp_logout','auto_redirect_after_logout');



// $thething = "%7B%22connected_app_name%22%3A%22ocdla-admin%22%2C%22flow%22%3A%22webserver%22%2C%22domain%22%3A%22https%3A%5C%2F%5C%2Ftest.salesforce.com%22%7D";

// $decoded = urldecode($thething);

// $regular = json_decode($decoded);

// var_dump($regular);exit;
