<?php


function add_rule() {

    add_rewrite_rule('sso/([0-9]+)/?$', 'index.php?pagename=sso&type=$matches[1]', 'top');
}

function query_vars($query_vars) {

    $query_vars[] = 'property_id';
    return $query_vars;
}


// Show the alternate login screen
function user_form_template() {

    if (strpos($_SERVER['REQUEST_URI'], "loggedout=true") !== false) {

        include(MY_PLUGIN_DIR . 'templates/alternate-login.tpl.php');
        
        exit;
    }
}


function add_urls() {

    $urls = array("sso/login");

    foreach($urls as $url) {

        add_rewrite_endpoint($url, EP_PERMALINK);
    }
}

function set_query_vars($vars) {

    $page = $vars["page"];
    $name = $vars["name"];

    if($name == "sso/login"){

        $vars['login_type'] = 'salesforce';
    }

    return $vars;
}

function show_login(){

    get_query_var("login2");
    include(MY_PLUGIN_DIR . 'templates/alternate-login.tpl.php');
    exit;
}

function add_page_template($templates) {

    $templates[MY_PLUGIN_DIR . 'templates/test-tpl.php'] = __('Template from plugin', 'text-domain');
    return $templates;
}

function include_page_template($template) {

    $newTemplate = MY_PLUGIN_DIR . '/templates/test-tpl.php';

    if (file_exists($newTemplate)) {

        return $newTemplate;
    }

    return $template;
}