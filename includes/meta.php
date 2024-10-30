<?php
// Prints meta field HTML
if(!function_exists('ctcb_meta_fields')){
	function ctcb_meta_fields($post, $cpo_metadata = null){
		if($cpo_metadata == null || sizeof($cpo_metadata) == 0) return;
		$output = '';
		
		wp_nonce_field('ctcb_savemeta', 'ctcb_nonce');
		
		foreach($cpo_metadata as $current_meta){
			$field_name = $current_meta["name"];
			$field_title = $current_meta['label'];
			$field_desc = isset($current_meta['desc']) ? $current_meta['desc'] : '';
			$field_type = $current_meta['type'];
			$field_value = '';
			$field_value = get_post_meta($post->ID, $field_name, true);
			
			//Additional CSS classes depending on field type
			$field_classes = '';
			if($field_type == 'collection') $field_classes = ' ctcb-metabox-wide';
			
			$output .= '<div class="ctcb-metabox '.$field_classes.'"><div class="name">'.$field_title.'</div>';
			$output .= '<div class="field">';
			
			// Print metaboxes here. Develop different cases for each type of field.
			if($field_type == 'text')
				$output .= ctcb_form_text($field_name, $field_value, $current_meta);
			
			elseif($field_type == 'checkbox')
				$output .= ctcb_form_checkbox($field_name, $field_value, $current_meta['option'], $current_meta);
			
			elseif($field_type == 'textarea')
				$output .= ctcb_form_textarea($field_name, $field_value, $current_meta);
			
			elseif($field_type == 'select')
				$output .= ctcb_form_select($field_name, $field_value, $current_meta['option'], $current_meta);
			
			elseif($field_type == 'yesno')
				$output .= ctcb_form_yesno($field_name, $field_value, $current_meta);
			
			elseif($field_type == 'color')
				$output .= ctcb_form_color($field_name, $field_value);
					
			$output .= '</div>';
			$output .= '<div class="desc">'.$field_desc.'</div></div>';
		}
		echo $output;
	}
}

	
//Save to database
if(!function_exists('ctcb_meta_save')){
	function ctcb_meta_save($option){

		if(!isset($_POST['post_ID']) || !isset($_POST['ctcb_nonce'])) return;
		if(!wp_verify_nonce($_POST['ctcb_nonce'], 'ctcb_savemeta')) return;
		
		$cpo_metaboxes = $option;
		$post_id = $_POST['post_ID'];
			
		//Check if we're editing a post
		if(isset($_POST['action']) && $_POST['action'] == 'editpost'){                                   
			
			//Check every option, and process the ones there's an update for.
			if(sizeof($cpo_metaboxes) > 0)
			foreach ($cpo_metaboxes as $current_meta){
			   
				$field_name = $current_meta['name'];
				
				//If the field has an update, process it.
				if(isset($_POST[$field_name])){
					$field_value = $_POST[$field_name];
					
					// Delete unused metadata
					if(empty($field_value) || $field_value == ''){ 
						delete_post_meta($post_id, $field_name, get_post_meta($post_id, $field_name, true));
					}
					// Update metadata
					else{ 
						update_post_meta($post_id, $field_name, $field_value);
					}
				}
			}
		}
	}
}