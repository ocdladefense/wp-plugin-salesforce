<?php

/**
 * Change the name of this file and place it in wp-content/
 */

define("SALESFORCE_DOMAIN", "https://test.salesforce.com");
define("SALESFORCE_COMMUNITY_DOMAIN", "https://ocdpartial-ocdla.cs217.force.com");
define("MY_DOMAIN", "http://localhost/wpocdla");


$oauth_config = array(
    "ocdla-wordpress" => array(
        "default" => true,
        "sandbox" => true, // Might be used to determine domain for urls
        "client_id" => "",
        "client_secret" => "",
        "auth" => array(
            "saml" => array(),
            "oauth" => array(
                "usernamepassword" => array(
                    "token_url" => SALESFORCE_DOMAIN . "/services/oauth2/token",
                    "username" => "",
                    "password" => "",
                    "security_token" => ""
                ),
                "webserver" => array(
                    "token_url" => SALESFORCE_COMMUNITY_DOMAIN . "/services/oauth2/token",
                    "auth_url" => SALESFORCE_COMMUNITY_DOMAIN . "/services/oauth2/authorize",
                    "redirect_url" => MY_DOMAIN . "/sso-callback",
                    "callback_url" => MY_DOMAIN . "/jobs"
                )
            )
        )
    )
);
