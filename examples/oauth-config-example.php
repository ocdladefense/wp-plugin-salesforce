<?php

/**
 * Change the name of this file and place it in wp-content/
 */


function getConfig($configName){
    
    $configs = array(
        "wp-admin" => array( 
            "default" => true,
            "sandbox" => true, // Might be used to determine domain for urls
            "client_id" => "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2",
            "client_secret" => "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1",
            "auth" => array(
                "saml" => array(),
                "oauth" => array(
                    "usernamepassword" => array(
                        "token_url" => "https://ltdglobal-customer.cs197.force.com/services/oauth2/token",
                        "username" => "",
                        "password" => "",
                        "security_token" => ""
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
        "wp-customer" => array(
            "default" => true,
            "sandbox" => true, // Might be used to determine domain for urls
            "client_id" => "3MVG9gI0ielx8zHLKXlEe15aGYjrfRJ2j60D4kIpoTDqx2YSaK2xqoA3wU77thTRImxT5RSq_obv6EOQaZBm2",
            "client_secret" => "3B61242366DCD4812DAA4C63A5FDF9C76F619528547B87A950A1584CEAB825E1",
            "auth" => array(
                "saml" => array(),
                "oauth" => array(
                    "usernamepassword" => array(
                        "token_url" => "https://ltdglobal-customer.cs197.force.com/services/oauth2/token",
                        "username" => "",
                        "password" => "",
                        "security_token" => ""
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







if(!function_exists("salesforce_oauth_url_admin")) {

	print "Hello world!";exit;
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
}


if(!function_exists("salesforce_oauth_url_customer")) {
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

}
