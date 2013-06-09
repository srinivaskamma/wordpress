function wizylike(post_id, user_id, type){
	if(post_id >= 1 && (user_id == 0 || user_id >= 0)){
		if (type === 'like') {
			
			// like button clicked
			jQuery('#wizylike-post-'+post_id+' .wizylike_like_unlike').addClass('wizylike_loading');
			
			jQuery.post(wizylike_url + '/',
						{post_id: post_id, user_id: user_id, like: 'like'}, 
						function(result){
							jQuery('#wizylike-post-'+post_id+' .wizylike_count').remove();	
							jQuery('#wizylike-post-'+post_id+' .wizylike_like_unlike').remove();
							jQuery('#wizylike-post-'+post_id+' .wizylike_linebreaker.last').remove();
							
							jQuery('#wizylike-post-'+post_id+'').append(result)
							
							jQuery('#wizylike-post-'+post_id+' .wizylike_icon').replaceWith('<span class="wizylike_icon" onclick="wizylike('+post_id+', '+user_id+', \'unlike\');">&nbsp;</span>');
						});
			
		} else if (type === 'unlike') {
			
			// unlike button clicked
			jQuery('#wizylike-post-'+post_id+' .wizylike_like_unlike').addClass('wizylike_loading');
			
			jQuery.post(wizylike_url + '/',
						{post_id: post_id, user_id: user_id, like: 'unlike'}, 
						function(result){
							jQuery('#wizylike-post-'+post_id+' .wizylike_count').remove();	
							jQuery('#wizylike-post-'+post_id+' .wizylike_like_unlike').remove();
							jQuery('#wizylike-post-'+post_id+' .wizylike_linebreaker.last').remove();
							
							jQuery('#wizylike-post-'+post_id+'').append(result)
							
							jQuery('#wizylike-post-'+post_id+' .wizylike_icon').replaceWith('<span class="wizylike_icon" onclick="wizylike('+post_id+', '+user_id+', \'like\');">&nbsp;</span>');
						});
					
		} // end like type check
	} // end post id check
} // end wizylike