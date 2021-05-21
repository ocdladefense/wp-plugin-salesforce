add_action( 'init', "add_urls" );
add_filter('request',"query_vars");
add_filter('template_include',"setting_template");
add_filter('init','flushRules'); 

function flushRules(){ 
    global $wp_rewrite; 
    $wp_rewrite->flush_rules(); 
} 

function add_urls(){
    //may not be able to use compount paths as parameters to add_rewrite_endpoint
    add_rewrite_endpoint( 'sso-login', EP_PERMALINK);
}


function query_vars($vars) {

	if ( $vars['name'] == 'sso-login') {
		$vars['sso-login'] = true;
	}

	return $vars;
}

function setting_template ($template) {
	if (get_query_var('sso-login')) {
		//$post = get_queried_object();
		//load_template(MY_PLUGIN_DIR . '/templates/sso-login.php');
        	return locate_template('sso-login.php',true);
	}
	return $template;
}