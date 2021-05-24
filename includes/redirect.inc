<?php

function add_urls() {

    //may not be able to use compount paths as parameters to add_rewrite_endpoint
		add_rewrite_rule( '^sso-login/?', 'index.php?sso-login=sso-login', 'top' );
    add_rewrite_endpoint( 'sso-login', EP_PERMALINK);
}

function query_vars($vars) {

	if ( $vars['name'] == 'sso-login') {
		$vars['sso-login'] = true;
	}

	return $vars;
}

function setting_template($template) {

	if (get_query_var('sso-login')) {

		return locate_template('sso-login.php',false);
		exit;
	}
	
	return $template;
}

function flush_rules() { 

    global $wp_rewrite; 
    $wp_rewrite->flush_rules(); 
} 