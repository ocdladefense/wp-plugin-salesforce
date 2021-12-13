<?php

use Salesforce\OAuthConfig;


function get_oauth_config($key = null) {

	global $oauth_config;

	if(null == $key || $key == "default") {

		$defaultConfigs = array();

		foreach($oauth_config as $key => $connectedApp) {

			$connectedApp["name"] = $key;

			if($connectedApp["default"]) {

				$defaultConfigs[] = $connectedApp;
			}
		}

        if(count($defaultConfigs) == 0) throw new Exception("CONFIG_ERROR: No connected app is set to default in your configuration, and no connected app is set on the module.");

        return new OAuthConfig($defaultConfigs[0]);

		
	} else {

		$config = $oauth_config[$key];
		$config["name"] = $key;

		return new OAuthConfig($config);
	}
	
	throw new Exception("HTTP_INIT_ERROR: No default Connected App / Org.  Check your configuration.");
}