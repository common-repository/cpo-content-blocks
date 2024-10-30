<?php
// Exit if accessed directly
if(!defined('ABSPATH')) exit;


//Register post type
function ctcb_post_types(){
	//Add portfolio
	$labels = array('name' => __('Content Blocks', 'ctcb'),
	'singular_name' => __('Content Block', 'ctcb'),
	'add_new' => __('Add Content Block', 'ctcb'),
	'add_new_item' => __('Add New Content Block', 'ctcb'),
	'edit_item' => __('Edit Content Block', 'ctcb'),
	'new_item' => __('New Content Block', 'ctcb'),
	'view_item' => __('View Content Blocks', 'ctcb'),
	'search_items' => __('Search Content Blocks', 'ctcb'),
	'not_found' =>  __('No content blocks found.', 'ctcb'),
	'not_found_in_trash' => __('No content blocks found in the trash.', 'ctcb'), 
	'parent_item_colon' => '');
	
	$fields = array('labels' => $labels,
	'public' => false,
	'publicly_queryable' => false,
	'show_ui' => true, 
	'query_var' => true,
	'capability_type' => 'post',
	'hierarchical' => false,
	'show_in_nav_menus' => true,
	'menu_icon' => 'dashicons-schedule',
	'menu_position' => null,
	'supports' => array('title', 'editor')); 
	register_post_type('cpo_content_block', $fields);
}
add_action('init', 'ctcb_post_types');


//Declare columns
function ctcb_post_columns($columns){
	$columns = array(
	'cb' => '<input type="checkbox" />',
	'title' => __('Title', 'ctcb'),
	'ctcb-excerpt' => __('Content', 'ctcb'),
	'ctcb-location' => __('Location', 'ctcb'),
	'ctcb-pages' => __('Pages', 'ctcb'),
	);
	return $columns;
}
add_filter('manage_edit-cpo_content_block_columns', 'ctcb_post_columns') ;


//Declare column content
function ctcb_post_columns_content($column){
	global $post;
	switch($column){
		case 'ctcb-location': 
			echo ctcb_metadata_locations(get_post_meta($post->ID, 'block_location', true));
		break;	
		case 'ctcb-excerpt': 
			$content = strip_tags($post->post_content);
			echo substr($content, 0, 180);
			if(strlen($content) > 180) echo '&hellip;';
		break;	
		case 'ctcb-pages': 
			$pages = get_post_meta($post->ID, 'block_pages', true);
			if(is_array($pages)) foreach($pages as $current_page => $current_value){
				echo ctcb_metadata_pages($current_page).'<br>';
			}
		break;	
		default:break;
	}
}
add_action('manage_posts_custom_column', 'ctcb_post_columns_content', 2);


//Add metaboxes to block posts
function ctcb_metaboxes(){
	add_meta_box('cpo_content_block_settings', __('Block Display', 'ctcb'), 'ctcb_metabox_settings', 'cpo_content_block', 'side', 'default');
	add_meta_box('cpo_content_block_location', __('Block Location', 'ctcb'), 'ctcb_metabox_location', 'cpo_content_block', 'normal', 'high');
	add_meta_box('cpo_content_block_appearance', __('Block Appearance', 'ctcb'), 'ctcb_metabox_appearance', 'cpo_content_block', 'normal', 'high');
}
add_action('add_meta_boxes', 'ctcb_metaboxes');


//Settings & appearance
function ctcb_metabox_settings($post){
	ctcb_meta_fields($post, ctcb_metadata_block_settings());
}


//Location metabox
function ctcb_metabox_location($post){ 
	wp_enqueue_script('ctcb-admin');
	
	$location_value = esc_attr(get_post_meta($post->ID, 'block_location', true));
	$priority_value = esc_attr(get_post_meta($post->ID, 'block_priority', true));
	
	echo '<div class="ctcb-metabox-location">';
	echo '<input type="text" value="'.$location_value.'" class="ctcb-input-location" name="block_location" id="block_location" placeholder="'.__('Name of the action hook', 'ctcb').'"/>';
	echo '<input type="text" value="'.$priority_value.'" class="ctcb-input-location ctcb-input-priority" name="block_priority" id="block_priority" placeholder="'.__('Priority (10)', 'ctcb').'"/>';
	echo '</div>';
	
	$locations = ctcb_metadata_locations();
	$tab_content = '';
	$active_class = ' ctcb-tab-active';
	
	echo '<div class="ctcb-tabs">';
	echo '<div class="ctcb-tab-menu">';
	foreach($locations as $current_key => $current_location){
		$location_key = esc_attr($current_key);
		$location_name = isset($current_location['name']) ? esc_attr($current_location['name']) : __('(Unknown)', 'ctcb');
		$location_description = isset($current_location['description']) ? esc_attr($current_location['description']) : '';
		$location_image = isset($current_location['image']) ? esc_url($current_location['image']) : '';
		echo '<div class="ctcb-tab'.$active_class.'" rel="#ctcb-tab-content-'.$location_key.'">';
		if($location_image != ''){
			echo '<img class="ctcb-tab-image" src="'.$location_image.'">';
		}
		echo '<div class="ctcb-tab-title">'.$location_name.'</div>';
		echo '<div class="ctcb-tab-description">'.$location_description.'</div>';
		echo '</div>';
		
		$tab_content .= '<div class="ctcb-tab-group'.$active_class.'" id="ctcb-tab-content-'.$location_key.'">';
		foreach($current_location['hooks'] as $hook_key => $hook_description){
			$hook_key = esc_attr($hook_key);
			$hook_description = esc_html($hook_description);
			$tab_content .= '<div class="ctcb-tab-content" rel="'.$hook_key.'">';
			$tab_content .= '<div class="ctcb-tab-content-title">'.$hook_key.'</div>';
			$tab_content .= '<div class="ctcb-tab-content-description">'.$hook_description.'</div>';
			$tab_content .= '</div>';
		}
		$tab_content .= '</div>';
		$active_class = '';
	}
	echo '</div>';
	echo '<div class="ctcb-tab-body">';
	echo $tab_content;
	echo '</div>';
	echo '</div>';
}


//Display metaboxes
function ctcb_metabox_appearance($post){ 
	do_action('ctcb_metabox_before_appearance');
	ctcb_meta_fields($post, ctcb_metadata_block_appearance());
	do_action('ctcb_metabox_after_appearance');
}


//Save metaboxes on post update
function ctcb_metabox_save($post){
	ctcb_meta_save(ctcb_metadata_block_settings());
	ctcb_meta_save(ctcb_metadata_block_appearance());
	ctcb_meta_save(ctcb_metadata_block_location());
}
add_action('save_post_cpo_content_block', 'ctcb_metabox_save');


function ctcb_shortcode_preview() {
	global $post;
	if(isset($post->ID)){
		echo '<div class="ctcb-shortcode-preview">';
		echo __('Use this shortcode to embed this content block into your posts:', 'ctcb');
		echo '<div class="ctcb-shortcode-preview-content wp-ui-highlight">';
		echo '[cpo_content_block id="'.$post->ID.'"]';
		echo '</div>';
		echo '</div>';
	}
}
//add_action('edit_form_after_title', 'ctcb_shortcode_preview');