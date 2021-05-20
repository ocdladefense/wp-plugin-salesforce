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
//add_filter('theme_page_templates', 'add_page_template');
//add_filter('template_include', 'include_page_template', 99);
add_action( 'init', 'process_oauth_redirect_uri' );
 
function process_oauth_redirect_uri() {

    $request_uri = explode("?",$_SERVER["REQUEST_URI"])[0];
    if($request_uri == "/ocdla-prod/my-account"){

        // Get an "OAuthConfig" object for a given connected app configuration array
        $configArray = getConfig("ocdla-sandbox-customer");
        $config = new OAuthConfig($configArray);
        $config->setAuthorizationCode($_GET["code"]);
        
        // Get an access token and an instance url from the "OAuthResponse".
        $oauth = OAuthRequest::newAccessTokenRequest($config, "webserver");
        $oauthResponse = $oauth->authorize();
        
        $accessToken = $oauthResponse->getAccessToken();
        $instanceUrl = $oauthResponse->getInstanceUrl();

        // Get the salesforce "user info" for the current user.
		$userInfoEndpoint = "/services/oauth2/userinfo?access_token={$accessToken}";
		$req = new RestApiRequest($instanceUrl, $accessToken);
		$resp = $req->send($userInfoEndpoint);

		$sf_userInfo = $resp->getBody();

        // Get the salesforce user's "preferred username" from the "user info".
        $username = getWpComplientUsername($sf_userInfo["preferred_username"]);

        // If the salesforce user does not exist in the wordpress database, create a wordpress user using the salesforce "user info".
        if(!username_exists($username)){

            $isAdministrator = $sf_userInfo["user_type"] == "STANDARD"; // Need a better way to determine the role.
            $role = $isAdministrator ? "administrator" : "subscriber";  // Salesforce sets "user_type" to "STANDARD" for admin and customer users? 

            $params = array(
                "role"          => $role,
                "user_login"    => $username,
                "first_name"    => $sf_userInfo["given_name"],
                "last_name"     => $sf_userInfo["family_name"],
                "user_email"    => $sf_userInfo["email"],
                "user_pass"     => wp_generate_password(12, false)
            );

            $wp_userId = wp_insert_user($params);

            // Need to find out what this email looks like?  
            wp_new_user_notification($wp_userId, "user");
        }

        user_login($username);
    }
}

function getWpComplientUsername($username) {

    $nameParts = explode(".", $username);

    $complientName = $nameParts[0] . "." . $nameParts[1];

    for($i = 2; $i < count($nameParts); $i++) {

        $complientName = $complientName . $nameParts[$i];
    }

    return $complientName;
}




function user_login($username) {

    $user = get_user_by('login', $username);

    // Redirect URL //
    if ( !is_wp_error( $user ) )
    {
        wp_clear_auth_cookie();
        wp_set_current_user ( $user->ID );
        wp_set_auth_cookie  ( $user->ID );
    }

    if(!is_user_logged_in()) {

        throw new Exception("LOGIN_ERROR: The new user did not get logged in.");
    }
}

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

function salesforce_connect($flow = "usernamepassword")
{
    if($flow == "usernamepassword"){
        return salesforce_username();
    }

    if($flow == "webserver"){
        return salesforce_webserver();
    }
}

function getConfig($configName){
    $configs = array(
        "ocdla-sandbox-admin" => array(
            //"highscope-sandbox-2.0--webserver--user" 
            "default" => true,
            "sandbox" => true, // Might be used to determine domain for urls
            "client_id" => "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2",
            "client_secret" => "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1",
            "auth" => array(
                "saml" => array(),
                "oauth" => array(
                    "usernamepassword" => array(
                        "token_url" => "https://ltdglobal-customer.cs197.force.com/services/oauth2/token",
                        "username" => "membernation@ocdla.com.ocdpartial",
                        "password" => "asdi49ir4",
                        "security_token" => "mT4ZN6OQmoF9SSZmx830AtpEM"
                    ),
                    "webserver" => array(
                        "token_url" => "https://test.salesforce.com/services/oauth2/token",
                        "auth_url" => "https://test.salesforce.com/services/oauth2/authorize",	// Web server ouath flow has two oauth urls.
                        "redirect_url" => "http://localhost/oauth/api/request",
                        "callback_url" => "http://localhost/ocdla-prod/my-account"
                    )
                )
            )
        ),
        "ocdla-sandbox-customer" => array(
            //"highscope-sandbox-2.0--webserver--user" 
            "default" => true,
            "sandbox" => true, // Might be used to determine domain for urls
            "client_id" => "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2",
            "client_secret" => "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1",
            "auth" => array(
                "saml" => array(),
                "oauth" => array(
                    "usernamepassword" => array(
                        "token_url" => "https://ltdglobal-customer.cs197.force.com/services/oauth2/token",
                        "username" => "membernation@ocdla.com.ocdpartial",
                        "password" => "asdi49ir4",
                        "security_token" => "mT4ZN6OQmoF9SSZmx830AtpEM"
                    ),
                    "webserver" => array(
                        "token_url" => "https://ocdpartial-ocdla.cs169.force.com/services/oauth2/token",
                        "auth_url" => "https://ocdpartial-ocdla.cs169.force.com/services/oauth2/authorize",	// Web server ouath flow has two oauth urls.
                        "redirect_url" => "http://localhost/oauth/api/request",
                        "callback_url" => "http://localhost/ocdla-prod/my-account"
                    )
                )
            )
                    
        )
    );

    return $configs[$configName];
}

function salesforce_oauth_url_admin(){

    $clientId = "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2";
    $clientSecret = "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1";
    $auth_url = "https://test.salesforce.com/services/oauth2/authorize";


    $state = array("connected_app_name" => "ocdla sandbox", "flow" => "webserver");

    $body = array(
        "client_id"		=> $clientId,
        //"redirect_uri"	=> "http://localhost/ocdla-prod/oauth/api/request",
        "redirect_uri"	=> "http://localhost/ocdla-prod/my-account",
        "response_type" => "code",
        "state"         => json_encode($state)
    );


    $body = http_build_query($body);
    return $auth_url."?".$body;
}

function salesforce_oauth_url_customer(){
    
    $clientId = "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2";
    $clientSecret = "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1";
    $auth_url = "https://ocdpartial-ocdla.cs169.force.com/services/oauth2/authorize";


    $state = array("connected_app_name" => "ocdla sandbox", "flow" => "webserver");

    $body = array(
        "client_id"		=> $clientId,
        //"redirect_uri"	=> "http://localhost/ocdla-prod/oauth/api/request",
        "redirect_uri"	=> "http://localhost/ocdla-prod/my-account",
        "response_type" => "code",
        "state"         => json_encode($state)
    );


    $body = http_build_query($body);
    return $auth_url."?".$body;
}



//then salesforce_webserver_step2
//Location http://localhost/my-account
function salesforce_webserver_step2($config){
    return Salesforce\OAuthRequest::newAccessTokenRequest($config,"webserver");
}




function salesforce_username(){
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
}
