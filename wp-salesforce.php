<?php

/**
 * @package SalesforcePlugin
 */

use Salesforce\OAuthRequest;
use Salesforce\RestApiRequest;

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

require MY_PLUGIN_DIR . "/includes/config.inc";
require MY_PLUGIN_DIR . "/includes/oauth.inc";
require MY_PLUGIN_DIR . "/includes/redirect.inc";
require MY_PLUGIN_DIR . "/includes/rewrite.inc";

// add_action( 'init', 'process_oauth_redirect_uri' );

// add_action('init', 'add_urls');

// add_filter('request', 'set_query_vars');

// add_action('template_redirect', 'show_login');

//add_action( 'init', 'add_rule' );

//add_filter( 'query_vars', 'wpse26388_query_vars' );
