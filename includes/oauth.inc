<?php

use Salesforce\OAuthRequest;
use Salesforce\RestApiRequest;
use Salesforce\OAuth;



function get_querystring_value($key) {

	$queryString = $_SERVER["QUERY_STRING"];

	$decoded = urldecode($queryString);

	$parsed = parse_str($decoded, $result);

	$state = $result[$key];

	$stateDecoded = json_decode($state);
	
	return $stateDecoded;
}




function process_oauth_redirect_uri() {

	$request_uri = explode("?",$_SERVER["REQUEST_URI"])[0];
	
	// Has to be able to figure out $request_uri when WordPress is installed in a "subdirectory".
	// E.g., when $request_uri == "/ocdla-prod/sso-callback".
	define('SOME_CONSTANT', '');
	if($request_uri != SOME_CONSTANT . "/sso-callback") return;

	$state = get_querystring_value("state");


	// Get an "OAuthConfig" object for a given connected app configuration array
	$config = get_oauth_config();
	$config->setAuthorizationCode($_GET["code"]);


	$domain = $state->domain;

	$flowConfig = OAuthConfig::parseFlowConfig("webserver", $domain);

	$flow = $config->getFlowConfig("webserver", $domain);



	// Get an access token and an instance url from the "OAuthResponse".
	$oauth = OAuthRequest::newAccessTokenRequest($config, $flow);

	$resp = $oauth->authorize();


	if(!$resp->success()) {
		var_dump($resp);
		exit;
	}

	$accessToken = $resp->getAccessToken();
	$instanceUrl = $resp->getInstanceUrl();



	// Get the salesforce "user info" for the current user.
	$userInfoEndpoint = "/services/oauth2/userinfo?access_token={$accessToken}";
	$req = new RestApiRequest($instanceUrl, $accessToken);
	$resp = $req->send($userInfoEndpoint);

	$uInfo = $resp->getBody();


	// Get the salesforce user's "preferred username" from the "user info".
	$username = getWpCompliantUsername($uInfo["preferred_username"]);


	// If the salesforce user does not exist in the wordpress database, create a wordpress user using the salesforce "user info".
	// 2. If the $uInfo["email"] is already associated with a WordPress user, then this fails.
	//  Note: this might fail silently (maybe).
	//  Need to check if email is already associated with a user.
	  // If it is, we need to get that WordPress user's ID.
	  
	  
	if(!username_exists($username)){

			$role = wp_get_role_by_user_type($uInfo["user_type"]);

			$params = array(
					"role"          => $role,
					"user_login"    => $username,
					"first_name"    => $uInfo["given_name"],
					"last_name"     => $uInfo["family_name"],
					"user_email"    => $uInfo["email"]
			);

			
			$userId = wp_insert_user($params);
			
			if(is_wp_error($userId)) {
				throw new Exception("Email address is already in use by WordPress.");
			}
			

			
			// var_dump($userId);exit;
			// Need to find out what this email looks like?  
			// wp_new_user_notification($wp_userId, "user");
	}


	$did_it_work = user_login($username);
	
	if(!$did_it_work) {

        throw new Exception("LOGIN_ERROR: The new user did not get logged in.");
    }
    
  else {
	// auto_redirect_after_logout();
	}
}


// Figure out how to check for exsiting username AND existing email.
// If either one exists user insert will fail.
function check_existing_user($data) {


	return null || $userId;
}



function user_login($username) {


    $user = get_user_by('login', $username);

		
		if( is_wp_error($user) ) {
			return false;
		}

		wp_clear_auth_cookie();
		wp_set_current_user ( $user->ID );
		wp_set_auth_cookie  ( $user->ID );
		
		return is_user_logged_in();
}





function wp_get_role_by_user_type($userType) {

    $otherUserTypes = array(
        "PowerPartner"          => "Partner",
        "CSPLitePortal"         => "High Volume Portal",
        "CustomerSuccess"       => "Customer Portal User",
        "PowerCustomerSuccess"  => "Customer Portal Manager",
        "CsnOnly"               => "Chatter Free"
    );

    $adminUserTypes = array("Standard");

    $upperCaseAdminUserTypes = array();
    foreach($adminUserTypes as $type) {

        $upperCaseAdminUserTypes[] = strtoupper($type);
    }


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









function auto_redirect_after_logout() {

    wp_safe_redirect( home_url() );
    exit;
}









function salesforce_oauth_url_admin(){

		$config = get_oauth_config();

    $resp = OAuth::start($config, "webserver", SALESFORCE_DOMAIN);

    $header = $resp->getHeader("Location");

    $url = $header->getValue();

    return $url;
}



function salesforce_oauth_url_customer(){

		$config = get_oauth_config();

    $resp = OAuth::start($config, "webserver", SALESFORCE_COMMUNITY_DOMAIN);

    $header = $resp->getHeader("Location");

    $url = $header->getValue();

    return $url;
}