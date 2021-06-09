<?php

use Salesforce\OAuthRequest;
use Salesforce\RestApiRequest;
use Salesforce\OAuth;

define("DOMAIN", $_SERVER["CONTEXT_PREFIX"]);

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

	if($request_uri != DOMAIN . "/sso-callback") return;

	$state = get_querystring_value("state");


	// Get an "OAuthConfig" object for a given connected app configuration array
	$config = get_oauth_config();
	$config->setAuthorizationCode($_GET["code"]);


	$domain = $state->domain;

	$flow = $config->getFlowConfig("webserver", $domain);


	// Get an access token and an instance url from the "OAuthResponse".
	$oauth = OAuthRequest::newAccessTokenRequest($config, $flow);

	$resp = $oauth->authorize();

	if(!$resp->success()) throw new Exception("OAUTH_ERROR: {$resp->getErrorMessage()}");

	$accessToken = $resp->getAccessToken();
	$instanceUrl = $resp->getInstanceUrl();

	$uInfo = get_user_info($instanceUrl,$accessToken);
    //$uInfo = getTestUserInfo();

    //var_dump($uInfo);exit;

    $salesforceUsername = $uInfo["preferred_username"];
    $salesforceUserType = $uInfo["user_type"];
    $salesforceUserEmail = $uInfo["email"];
    
	$filteredUsername = getWpCompliantUsername($salesforceUsername);

    // If the salesforce user does not exist in the wordpress database, create a wordpress user using the salesforce "user info".
    $existingWpUserId = get_existing_wp_user_id($filteredUsername, $salesforceUserEmail);

    $role = wp_get_role_by_user_type($salesforceUserType);

    $params = array(
        "role"          => $role,
        "user_login"    => $filteredUsername,
        "first_name"    => $uInfo["given_name"],
        "last_name"     => $uInfo["family_name"],
        "user_email"    => $uInfo["email"]
    );


    if($existingWpUserId == null){

        $result = wp_insert_user($params);

    } else {

        $result = wp_update_existing_user($existingWpUserId, $params);
    }
    
    if(is_wp_error($result)) throw new Exception("INSERT_USER_ERROR: {$result->get_error_message()}");

	$did_it_work = user_login($filteredUsername, $salesforceUserEmail);
	
	if(!$did_it_work) throw new Exception("LOGIN_ERROR: The new user did not get logged in.");

    wp_safe_redirect(home_url());

    exit;
}

function wp_update_existing_user($id, $params){

    $params["ID"] = $id;
    return wp_update_user($params);
}


// Figure out how to check for exsiting username AND existing email.
// If either one exists user insert will fail.
function get_existing_wp_user_id($username, $email) {

    $userId = username_exists($username);

    if(!$userId) {

        $user = get_user_by("email", $email);
        $userId = $user->data->ID;
    }

	return $userId == false || empty("userId") ? null : $userId;
}




function user_login($username, $email) {

    $user = get_user_by('email', $email);

    if(!$user){

        $user = get_user_by('login', $username);
    }

    if($user != false){

        wp_clear_auth_cookie();
        wp_set_current_user ( $user->ID );
        wp_set_auth_cookie  ( $user->ID );
    }    

    return is_user_logged_in();
}

function get_user_info($instanceUrl,$accessToken){
	// Get the salesforce "user info" for the current user.
	$userInfoEndpoint = "/services/oauth2/userinfo?access_token={$accessToken}";
	$req = new RestApiRequest($instanceUrl, $accessToken);
	$resp = $req->send($userInfoEndpoint);

	$uInfo = $resp->getBody();
    return $uInfo;
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

/////////////////////////////       TESTING CODE        /////////////////////////////////////////////////////////

function getTestUserInfo(){

    $info = array(
        "user_type"             => "CustomerSuccess",
        "preferred_username"    => "trevoruehlinx1@gmail.com",
        "given_name"            => "Trevor",
        "family_name"           => "Uehlin",
        "email"                 => "email@example3.com"
    );

    return $info;
}


