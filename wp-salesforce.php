<?php

/**
 * @package SalesforcePlugin
 */
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
add_filter('theme_page_templates', 'add_page_template');
add_filter('template_include', 'include_page_template', 99);

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