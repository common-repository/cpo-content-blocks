jQuery(document).ready(function(){
	jQuery('.ctcb-tab').click(function(){
		var tab = jQuery(this).attr('rel');
		jQuery('.ctcb-tab').removeClass('ctcb-tab-active');
		jQuery('.ctcb-tab-group').removeClass('ctcb-tab-active');
		jQuery(this).addClass('ctcb-tab-active');
		jQuery(tab).addClass('ctcb-tab-active');
	});
	
	jQuery('.ctcb-tab-content').click(function(){
		var value = jQuery(this).attr('rel');
		jQuery('#block_location').val(value);
	});
});