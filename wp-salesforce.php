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

    $config = getCustomerConfig()["ocdla-sandbox"];

    $request_uri = explode("?",$_SERVER["REQUEST_URI"])[0];
    if($request_uri == "/ocdla-prod/my-account"){

        user_login("membernation@ocdla.comocdpartial");

        return null;
        
        //get access url from $_GET["code"]
        $config = new OAuthConfig($config);
        $config->setAuthorizationCode($_GET["code"]);
        
        $oauth = OAuthRequest::newAccessTokenRequest($config, "webserver");
        $resp = $oauth->authorize();
        
        $accessToken = $resp->getAccessToken();
        $instanceUrl = $resp->getInstanceUrl();
        //get user info with access token
		$url = "/services/oauth2/userinfo?access_token={$accessToken}";
		$req = new RestApiRequest($instanceUrl, $accessToken);
		$resp = $req->send($url);
        var_dump($resp);exit;
		$sf_userInfo = $resp->getBody();

        //set wordpress username and their session
        $username = $sf_userInfo["preferred_username"];

        if(!username_exists($username)){

            $password = wp_generate_password(12, false);
            $email = $sf_userInfo["email"];

            // $wp_userId = wp_create_user($username, $password, $email); 

            $isAdministrator = $sf_userInfo["user_type"] == "STANDARD";
            $role = $isAdministrator ? "administrator" : "subscriber";

            $params = array(
                "role" => $role,
                "user_login" => $username,
                "user_email" => $email,
                "user_pass"  => $password
            );

            $wp_userId = wp_insert_user($params);

            // Need to find out what this email looks like?  
            wp_new_user_notification($wp_userId, "user");
        }
        //var_dump($username, $sf_userInfo); exit;

        user_login($username);
    }
}



function user_login($email){
    $user = get_user_by('email', $email );

    // Redirect URL //
    if ( !is_wp_error( $user ) )
    {
        wp_clear_auth_cookie();
        wp_set_current_user ( $user->ID );
        wp_set_auth_cookie  ( $user->ID );

        //$redirect_to = user_admin_url();
        //wp_safe_redirect( $redirect_to );
        //exit();
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

function getConfig(){
    return array(
    "ocdla-sandbox" =>   array(
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
        )
    );
}

function getCustomerConfig(){
    return array(
    "ocdla-sandbox" =>   array(
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
}

function salesforce_oauth_url_admin(){

    // $config = new Salesforce\OAuthCOnfig(getConfig());
    // var_dump(Salesforce\OAuth::start($config, $authFlow));
    // exit;



    
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

    // $config = new Salesforce\OAuthCOnfig(getConfig());
    // var_dump(Salesforce\OAuth::start($config, $authFlow));
    // exit;



    
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

//Location: http://localhost/oauth/api/request
//this calls a function to set the access token
// function oauthFlowAccessToken(){

//     $info = json_decode($_GET["state"], true);
//     $connectedApp = $info["connected_app_name"];
//     $flow = $info["flow"];

//     $config = get_oauth_config($connectedApp);

//     $config->setAuthorizationCode($_GET["code"]);

//     $oauth = OAuthRequest::newAccessTokenRequest($config, "webserver");

//     $resp = $oauth->authorize();

//     if(!$resp->success()){

//         throw new OAuthException($resp->getErrorMessage());
//     }

//     OAuth::setSession($connectedApp, $flow, $resp->getInstanceUrl(), $resp->getAccessToken(), $resp->getRefreshToken());

//     $resp2 = new HttpResponse();

//     $flowConfig = $config->getFlowConfig($flow);

//     $resp2->addHeader(new HttpHeader("Location", $flowConfig->getCallbackUrl()));

//     return $resp2;
// }


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

    // run query for 10 contacts
    // var_dump and exit

    //print 'hello words!';
    ///exit;
}

//add_action('wp_loaded', 'salesforce_connect');
