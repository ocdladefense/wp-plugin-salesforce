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

// Adding a template to the 'Page Attributes' dropdown and including our custom template
add_filter('theme_page_templates', 'add_page_template');
add_filter('template_include', 'include_page_template', 99);

function add_page_template($templates)
{
    $templates[MY_PLUGIN_DIR . 'templates/test-tpl.php'] = __('Template from plugin', 'text-domain');
    return $templates;
}

function include_page_template($template)
{
    $newTemplate = MY_PLUGIN_DIR . '/templates/test-tpl.php';

    if (file_exists($newTemplate)) {
        return $newTemplate;
    }

    return $template;
}

function do_something()
{
    $clientId = "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2";
    $clientSecret = "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1";
    $tokenUrl = "https://test.salesforce.com/services/oauth2/token";
    $username = "kalashnikovr@ocdla.com.ocdpartial";
    $password = "Klaipeda1976";
    $securityToken = "9XhZ0NPPwRpHDLJEu3iOvb8m";


    // make a connection to SF
    $req = new OAuthRequest($tokenUrl); //"https://login.salesforce.com/services/oauth2/token");

    $body = array(
        "grant_type"             => "password",
        "client_id"             => $clientId,
        "client_secret"            => $clientSecret,
        "username"                => $username,
        "password"                => $password . $securityToken
    );

    $body = http_build_query($body);
    $contentType = new Http\HttpHeader("Content-Type", "application/x-www-form-urlencoded");
    $req->addHeader($contentType);

    $req->setBody($body);
    $req->setMethod("POST");
    // Sending a HttpResponse class as a Header to represent the HttpResponse.
    $req->addHeader(new Http\HttpHeader("X-HttpClient-ResponseClass", "\Salesforce\OAuthResponse"));

    $resp = $req->authorize();

    if (!$resp->success()) {
        throw new Exception("OAUTH_RESPONSE_ERROR: {$resp->getErrorMessage()}");
    }

    $api = new RestApiRequest($resp->getInstanceUrl(), $resp->getAccessToken());

    // List commiittees and related contact info for each member
    $results = $api->query("SELECT id, Name, (SELECT Contact__r.Id, Contact__r.Title, Contact__r.Name, Role__c, Contact__r.Email, Contact__r.Phone FROM Relationships__r) FROM Committee__c");
    print "<pre>";
    print print_r($results, true);
    print "</pre>";

    var_dump($results);
    exit;

    // run query for 10 contacts
    // var_dump and exit

    //print 'hello words!';
    ///exit;
}

add_action('wp_loaded', 'do_something');