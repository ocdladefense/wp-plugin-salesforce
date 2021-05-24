<?php

use Salesforce\OAuthRequest;
use Salesforce\RestApiRequest;

function process_oauth_redirect_uri() {

    $request_uri = explode("?",$_SERVER["REQUEST_URI"])[0];
    if($request_uri == "/oauth/api/request"){

        // Get an "OAuthConfig" object for a given connected app configuration array
        $configArray = getConfig("wp-customer");
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
        $username = getWpCompliantUsername($sf_userInfo["preferred_username"]);


        // If the salesforce user does not exist in the wordpress database, create a wordpress user using the salesforce "user info".
        if(!username_exists($username)){

            $role = wp_get_role_by_user_type($sf_userInfo);

            $params = array(
                "role"          => $role,
                "user_login"    => $username,
                "first_name"    => $sf_userInfo["given_name"],
                "last_name"     => $sf_userInfo["family_name"],
                "user_email"    => $sf_userInfo["email"]
            );

            $wp_userId = wp_insert_user($params);

            // Need to find out what this email looks like?  
            wp_new_user_notification($wp_userId, "user");
        }

        user_login($username);
    }
}

function wp_get_role_by_user_type($user) {

    $otherUserTypes = array(
        "PowerPartner"          => "Partner",
        "CSPLitePortal"         => "High Volume Portal",
        "CustomerSuccess"       => "Customer Portal User",
        "PowerCustomerSuccess"  => "Customer Portal Manager",
        "CsnOnly"               => "Chatter Free"
    );

    $adminUserTypes = array("Standard" => "Standard");

    $upperCaseAdminUserTypes = array();
    foreach($adminUserTypes as $key => $type) {

        $upperCaseAdminUserTypes[strtoupper($key)] = $type;
    }

    $userType = $user["user_type"];

    return in_array($userType, $upperCaseAdminUserTypes) ? "administrator" : "subscriber";

}

function wp_get_role_by_profile($user) {

    $userId = $user["user_id"];

    $user = $req->query("SELECT Profile.Name FROM User WHERE Id = '$userId'")->getRecord();

    $sf_userProfile = $user["Profile"]["Name"];

    $isAdministrator = $sf_userProfile == "System Administrator";    

    return $isAdministrator ? "administrator" : "subscriber";
}


function getWpCompliantUsername($username) {

    $nameParts = explode(".", $username);

    $compliantName = $nameParts[0] . "." . $nameParts[1];

    for($i = 2; $i < count($nameParts); $i++) {

        $compliantName = $compliantName . $nameParts[$i];
    }

    return $compliantName;
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


function auto_redirect_after_logout() {

    wp_safe_redirect( home_url() );
    exit;
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