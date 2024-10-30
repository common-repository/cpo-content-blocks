<?php
/*
Plugin Name: CPO Content blocks
Description: Allows you to create reusable pieces of content that you can place anywhere in your site. Insert them into your posts, on a sidebar, or place them directly onto the layout thanks to the different areas available.
Author: CPOThemes
Version: 1.2.0
Author URI: http://www.cpothemes.com
*/

//Plugin setup
if(!function_exists('ctcb_setup')){
	add_action('plugins_loaded', 'ctcb_setup');
	function ctcb_setup(){
		//Load text domain
		$textdomain = 'ctcb';
		$locale = apply_filters('plugin_locale', get_locale(), $textdomain);
		if(!load_textdomain($textdomain, trailingslashit(WP_LANG_DIR).$textdomain.'/'.$textdomain.'-'.$locale.'.mo')){
			load_plugin_textdomain($textdomain, FALSE, basename(dirname($_SERVER["SCRIPT_FILENAME"])).'/languages/');
		}
	}
}


//Add public stylesheets
add_action('wp_enqueue_scripts', 'ctcb_add_styles');
function ctcb_add_styles(){
	$stylesheets_path = plugins_url('css/' , __FILE__);
	wp_enqueue_style('ctcb-content-blocks', $stylesheets_path.'style.css');
}


//Add Public scripts
add_action('admin_enqueue_scripts', 'ctcb_scripts_back');
function ctcb_scripts_back( ){
    $scripts_path = plugins_url('scripts/' , __FILE__);
	
	//Register custom scripts for later enqueuing
	wp_register_script('ctcb-admin', $scripts_path.'admin.js', array('jquery'), false, true);
}


//Add admin stylesheets
add_action('admin_print_styles', 'ctcb_add_styles_admin');
function ctcb_add_styles_admin(){
	$stylesheets_path = plugins_url('css/' , __FILE__);
	wp_enqueue_style('ctcb-admin', $stylesheets_path.'admin.css');
}



//Manage license activation
add_action('admin_notices', 'ctcb_admin_deprecated');
function ctcb_admin_deprecated(){
	if(current_user_can('manage_options')){
		echo '<div id="message" class="updated">';
		echo '<p>';
		echo '<b style="font-size:1.3em;">';
		echo __('CPO Content Blocks has been replaced by Infuse and will not be updated anymore!', 'cpotheme');
		echo '</b>';
		echo '<br><br>';
		echo __('The new version fully supports CPO Content Blocks and is in active development, getting lots of new features. You will only need to activate Infuse and delete CPO Content Blocks. No additional steps are required.', 'cpotheme');
		echo '<br>';
		$plugin_url = add_query_arg(array('tab' => 'plugin-information', 'plugin' => 'infuse', 'TB_iframe' => 'true', 'width' => '640', 'height' => '500'), admin_url('plugin-install.php'));
		echo '<b><a class="thickbox" href="'.$plugin_url.'">'.__('Download Infuse', 'cpotheme').'</a></b>';
		echo '</p>';
		echo '</div>';
	}
}


//Add all Shortcode components
$core_path = plugin_dir_path(__FILE__);

//General
require_once($core_path.'includes/post-types.php');
require_once($core_path.'includes/meta.php');
require_once($core_path.'includes/forms.php');
require_once($core_path.'includes/general.php');
require_once($core_path.'includes/metadata.php');