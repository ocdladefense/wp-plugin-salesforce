<?php

use Salesforce\OAuthRequest;
use Salesforce\RestApiRequest;

/**
 * @package SalesforcePlugin
 */

/*
Plugin Name: Salesforce Plugin
Plugin URI: 
Description: Connect to the Salesforce Lightning Platform.
Version: 2.0.0
Author: José Bernal
Author URI: https://www.ocdla.org/
License: GPLv2 or later
Text Domain: wp-salesforce-plugin
*/

// If this file is accessed directly, abort.
defined('ABSPATH') or die('You shall not pass!');



// Setting a CONSTANT for the plugin dir path
define('WP_SALESFORCE_PLUGIN_DIR', plugin_dir_path(__FILE__));

include WP_CONTENT_DIR . "/oauth-config.php";
require WP_SALESFORCE_PLUGIN_DIR . "/includes/globals.php";
require WP_SALESFORCE_PLUGIN_DIR . "/includes/oauth.inc";
require WP_SALESFORCE_PLUGIN_DIR . "/includes/redirect.inc";



add_action( 'init', "add_urls" );

add_filter('request',"query_vars");

add_filter('template_include',"setting_template");

add_filter('init','flush_rules'); 

add_action( 'init', 'process_oauth_redirect_uri' );

add_action('wp_logout','auto_redirect_after_logout');


function load_api(){
    
    $config = get_oauth_config();

    $flowConfig = $config->getFlowConfig();

    $req = OAuthRequest::newAccessTokenRequest($config, $flowConfig);

    $resp = $req->authorize();

    if (!$resp->success()) throw new Exception("OAUTH_RESPONSE_ERROR: {$resp->getErrorMessage()}");

    return new RestApiRequest($resp->getInstanceUrl(), $resp->getAccessToken());
}