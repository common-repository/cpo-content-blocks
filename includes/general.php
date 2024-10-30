<?php

	
//Abstracted function for retrieving specific options inside option arrays
if(!function_exists('ctcb_get_option')){
	function ctcb_get_option($option_name = '', $option_array = 'ctcb_settings'){
		//Determines whether to grab current language, or original language's option
		$option_list_name = $option_array;
		$option_list = get_option($option_list_name, false);
		if($option_list && isset($option_list[$option_name]))
			$option_value = $option_list[$option_name];
		else
			$option_value = false;
		return $option_value;
	}
}

//Abstracted function for updating specific options inside arrays
if(!function_exists('ctcb_update_option')){
	function ctcb_update_option($option_name, $option_value, $option_array = 'ctcb_settings'){
		$option_list_name = $option_array;
		$option_list = get_option($option_list_name, false);
		if(!$option_list)
			$option_list = array();
		$option_list[$option_name] = $option_value;
		if(update_option($option_list_name, $option_list))
			return true;
		else
			return false;
	}
}


//Custom function to do some cleanup on nested shortcodes
//Used for columns and layout-related shortcodes
function ctcb_do_shortcode($content){ 
	$content = do_shortcode(shortcode_unautop($content)); 
	$content = preg_replace('#^<\/p>|^<br\s?\/?>|<p>$|<p>\s*(&nbsp;)?\s*<\/p>#', '', $content);
	return $content;
}


//Retrieves and returns the shortcode prefix with a trailing underscore
function ctcb_shortcode_prefix(){ 
	$prefix = ctcb_get_option('shortcode_prefix'); 
	if($prefix != '') $prefix = esc_attr($prefix).'_';
	return $prefix;
}


//Returns the appropriate URL, either from a string or a post ID
function ctcb_image_url($id, $size = 'full'){ 
	$url = '';
	if(is_numeric($id)){
		$url = wp_get_attachment_image_src($id, $size);
		$url = $url[0];
	}else{
		$url = $id;
	}
	return $url;
}


//Load all blocks and assign them to corresponding actions
add_action('wp_head', 'ctcb_load_blocks');
function ctcb_load_blocks(){
	$blocks = new WP_Query('post_type=cpo_content_block&posts_per_page=-1&order=ASC&orderby=menu_order');
	if($blocks->posts){
		foreach($blocks->posts as $post){
			$block_location = get_post_meta($post->ID, 'block_location', true);
			$block_filter = get_post_meta($post->ID, 'block_pages', true);
			$block_priority = get_post_meta($post->ID, 'block_priority', true);
			if($block_priority == '') $block_priority = 10;
			$block_priority = absint($block_priority);
				
			//If block location is defined, add it to the queue
			if($block_location != ''){
				if(ctcb_block_page_filter($block_filter)){
					//Add block to action using anonymous function
					$block_data = array(
					'id' => $post->ID,
					'content' => ctcb_content_filters($post->post_content),
					'background' => get_post_meta($post->ID, 'block_bg', true),
					'color' => get_post_meta($post->ID, 'block_color', true),
					'padding_top' => get_post_meta($post->ID, 'block_padding_top', true),
					'padding_right' => get_post_meta($post->ID, 'block_padding_right', true),
					'padding_bottom' => get_post_meta($post->ID, 'block_padding_bottom', true),
					'padding_left' => get_post_meta($post->ID, 'block_padding_left', true),
					'margin_top' => get_post_meta($post->ID, 'block_margin_top', true),
					'margin_right' => get_post_meta($post->ID, 'block_margin_right', true),
					'margin_bottom' => get_post_meta($post->ID, 'block_margin_bottom', true),
					'margin_left' => get_post_meta($post->ID, 'block_margin_left', true));
					
					add_action($block_location, function() use ($block_data){ 
						ctcb_render_block($block_data); 
					}, $block_priority);
				}
			}
		}
	}
}


//Add filters to the content
function ctcb_content_filters($content){
	return do_shortcode($content);
}


//Determine whether to show the block on current page	
function ctcb_block_page_filter($pages){
	//If show always, return
	if(!is_array($pages)) return false;
	
	foreach($pages as $current_filter => $current_data){
		//Standard filters
		switch($current_filter){
			case 'always': return true; break;
			case 'home': if(is_front_page()) return true; break;
			case 'post': if(is_single()) return true; break;
			case 'page': if(is_page()) return true; break;
			case '404': if(is_404()) return true; break;
			case 'search': if(is_search()) return true; break;
		}
		
		//Custom Post Types
		if(is_singular($current_filter)) return true;
		
		//Taxonomies
		if(is_tax($current_filter)) return true;
	}
	
	return false;
}


//Render a single block
function ctcb_render_block($block_data){
	
	$color = !empty($block_data['color']) ? ' ctcb-'.$block_data['color'] : '';
	$background = !empty($block_data['background']) ? ' background-color:'.$block_data['background'].';' : '';
	$margin = '';
	$margin .= !empty($block_data['margin_top']) ? ' margin-top:'.$block_data['margin_top'].'px;' : '';
	$margin .= !empty($block_data['margin_right']) ? ' margin-right:'.$block_data['margin_right'].'px;' : '';
	$margin .= !empty($block_data['margin_bottom']) ? ' margin-bottom:'.$block_data['margin_bottom'].'px;' : '';
	$margin .= !empty($block_data['margin_left']) ? ' margin-left:'.$block_data['margin_left'].'px;' : '';
	$padding = '';
	$padding .= !empty($block_data['padding_top']) ? ' padding-top:'.$block_data['padding_top'].'px;' : ' padding-top:10px;';
	$padding .= !empty($block_data['padding_right']) ? ' padding-right:'.$block_data['padding_right'].'px;' : ' padding-right:10px;';
	$padding .= !empty($block_data['padding_bottom']) ? ' padding-bottom:'.$block_data['padding_bottom'].'px;' : ' padding-bottom:10px;';
	$padding .= !empty($block_data['padding_left']) ? ' padding-left:'.$block_data['padding_left'].'px;' : ' padding-left:10px;';
	$style = ' style="'.$margin.$padding.$background.'"';
	
	echo '<div class="ctcb-block'.$color.'"'.$style.'>';
	echo '<div class="ctcb-container">';
	echo $block_data['content'];
	echo '</div>';
	echo '</div>';
}


//Render a single block
function ctcb_block($post_id){
	$post = get_post($post_id);
	if($post){
		$block_location = get_post_meta($post->ID, 'block_location', true);
		$block_filter = get_post_meta($post->ID, 'block_pages', true);
		$block_priority = get_post_meta($post->ID, 'block_priority', true);
		if($block_priority == '') $block_priority = 10;
		$block_priority = absint($block_priority);
			
		//If block location is defined, add it to the queue
		if($block_location != ''){
			if(ctcb_block_page_filter($block_filter)){
				//Add block to action using anonymous function
				$block_data = array(
				'id' => $post->ID,
				'content' => apply_filters('the_content', $post->post_content),
				'background' => get_post_meta($post->ID, 'block_bg', true),
				'color' => get_post_meta($post->ID, 'block_color', true),
				'padding' => get_post_meta($post->ID, 'block_padding', true),
				'margin' => get_post_meta($post->ID, 'block_margin', true));
				
				ctcb_render_block($block_data);
			}
		}
	}
}



// Block shortcode
function ctcb_shortcode($atts, $content = null){
	$attributes = extract(shortcode_atts(array('id' => false), $atts));
	$output = '';
	if($id){
		ob_start();
		ctcb_block($id);
		$output = ob_get_clean();
	}
	return $output;
}
add_shortcode('cpo_content_block', 'ctcb_shortcode');
