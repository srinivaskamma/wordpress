<?php

class wizylike {
	
	private		$ip;
	public		$post_id;
	public		$user_id;
	public		$likes_count;
	public		$capabilities;
	public		$user_is_loggedin;
	
	public		$style;
	public		$colour;
	public		$like_txt;
	public		$unlike_txt;
	
	
	public function __construct($post_id, $user_id){
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->post_id = $post_id;
		$this->user_id = $user_id;
		
		if($user_id != 0 && $user_id >= 1){
			$this->user_is_loggedin = true;
		}
		
		if(get_option('wizylike_capabilities'))
			$this->capabilities = get_option('wizylike_capabilities');
		
		if (get_option('wizylike_style'))
			$this->style = get_option('wizylike_style');
			
		if (get_option('wizylike_colour'))
			$this->colour = get_option('wizylike_colour');
			
		if (get_option('wizylike_like_txt'))
			$this->like_txt = get_option('wizylike_like_txt');
			
		if (get_option('wizylike_unlike_txt'))
			$this->unlike_txt = get_option('wizylike_unlike_txt');
		
		$this->likes_count();
	} // end wizylike __construct


	public function likes_count(){
		global $wpdb, $wl_tablename;
		
		// check in the db for likes
		$likes_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wl_tablename WHERE post_id = %d AND like_status = %s", $this->post_id, 'like'));
		
		// returns likes, return 0 if no likes were found
		$this->likes_count = $likes_count;
		
	} // likes_count
	
	
	public function check_recurring_like(){
		global $wpdb, $wl_tablename;
		
		if($this->user_is_loggedin){
			
			// user is logged in	
			$likes_check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wl_tablename
											WHERE	post_id = %d
											AND		user_id = %d
											AND		like_status = %s", $this->post_id, $this->user_id, 'like'));
						
			if($likes_check === 0){
				// user didn't like this post before
				return false;
			} elseif($likes_check >= 1){
				// user liked this post before
				return true;	
			}
		} else{
			// user not logged in, check by ip address
			$likes_check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wl_tablename
											WHERE	post_id = %d
											AND		ip_address = %s
											AND		user_id = %d
											AND		like_status = %s", $this->post_id, $this->ip, 0, 'like'));
			
			if($likes_check === 0){
				// ip didn't like this post before
				return false;
			} elseif($likes_check >= 1){
				// if liked this post before
				return true;	
			}
		}
	} // end check_recurring_like
	
	
	public function check_recurring_unlike(){
		global $wpdb, $wl_tablename;
		
		if($this->user_is_loggedin){
			// user is logged in	
			$likes_check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wl_tablename
											WHERE	post_id = %d
											AND		user_id = %d
											AND		like_status = %s", $this->post_id, $this->user_id, 'unlike'));
						
			if($likes_check === 0){
				// user didn't unlike this post before
				return false;
			} elseif($likes_check >= 1){
				// user un;iked this post before
				return true;	
			}
		} else {
			// user not logged in, check by ip address
			$likes_check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wl_tablename
											WHERE	post_id = %d
											AND		ip_address = %s
											AND		user_id = %d
											AND		like_status = %s", $this->post_id, $this->ip, 0, 'unlike'));
			
			if($likes_check === 0){
				// ip didn't un;ike this post before
				return false;
			} elseif($likes_check >= 1){
				// if unliked this post before
				return true;	
			}
		}
	} // end check_recurring_unlike
	
	
	public function like_post(){
		global $wpdb, $wl_tablename;
		
		if(!$this->check_recurring_like() && !$this->check_recurring_unlike()){
			
			// Adds a new database row if not liked and then unliked
			
			if($this->user_is_loggedin){
				// adds a new like in the database using user id & ip address
				$wpdb->insert($wl_tablename, array('post_id' => $this->post_id, 
													'user_id' => $this->user_id,
													'ip_address' => $this->ip,
													'like_status' => 'like'), array('%d', '%d', '%s', '%s'));
			} else {
				// adds a new like in the database using user id = 0 & ip address
				$wpdb->insert($wl_tablename, array('post_id' => $this->post_id, 
													'user_id' => 0,
													'ip_address' => $this->ip,
													'like_status' => 'like'), array('%d', '%d', '%s', '%s'));
			}
			
		} elseif(!$this->check_recurring_like() && $this->check_recurring_unlike()){
			
			// updates an existing database row if liked and then unliked and then liked again
			
			if($this->user_is_loggedin){
				// adds a new like in the database using user id & ip address
				$wpdb->update($wl_tablename, 
								array('like_status' => 'like'),
								array('post_id' => $this->post_id, 'user_id' => $this->user_id),
								array('%s'),
								array('%d', '%d'));
			} else {
				// adds a new like in the database using user id = 0 & ip address
				$wpdb->update($wl_tablename, 
								array('like_status' => 'like'),
								array('post_id' => $this->post_id, 'user_id' => 0, 'ip_address' => $this->ip),
								array('%s'),
								array('%d', '%d', '%s'));
			}
		}
		
		// count the new likes count
		$this->likes_count();
		
		// updates posts table with new like count
		$wpdb->update($wpdb->posts, 
							array('like_count' => $this->likes_count),
							array('ID' => $this->post_id, 'post_status' => 'publish'),
							array('%d'),
							array('%d', '%s'));
	} // end like_post
	
	
	public function unlike_post(){
		global $wpdb, $wl_tablename;
		
		if($this->check_recurring_like()){
			if($this->user_is_loggedin){
				// unlick usign user id
				$wpdb->update($wl_tablename, 
								array('like_status' => 'unlike'),
								array('post_id' => $this->post_id, 'user_id' => $this->user_id),
								array('%s'),
								array('%d', '%d'));
				
			} else {
				// unlick usign user id = 0 and ip
				$wpdb->update($wl_tablename, 
								array('like_status' => 'unlike'),
								array('post_id' => $this->post_id, 'user_id' => 0, 'ip_address' => $this->ip),
								array('%s'),
								array('%d', '%d', '%s'));
				
			}
			
			// count the new likes count
			$this->likes_count();
			
			// updates posts table with new like count
			$wpdb->update($wpdb->posts, 
								array('like_count' => $this->likes_count),
								array('ID' => $this->post_id, 'post_status' => 'publish'),
								array('%d'),
								array('%d', '%s'));
		}
	} // end unlike_post
	
	
	public function like_button(){
		
		if(($this->capabilities === 'users-only' && $this->user_is_loggedin) || $this->capabilities === 'all'){
			if(!$this->check_recurring_like()){
				
				// onclick javascript function for processing. found in wizylike.js in /js folder
				$onclick_like = 'wizylike(' . $this->post_id . ', ' . $this->user_id . ', \'like\');';
				
				// like button
				$button =  '<div class="wizylike_button '.$this->style.' light_bg '.$this->colour.'" id="wizylike-post-'.$this->post_id.'">' . "\n";
				$button .= "\t" . '<span class="wizylike_icon" onclick="'.$onclick_like.'">&nbsp;</span>' . "\n";
				$button .= "\t" . '<span class="wizylike_linebreaker first"></span>' . "\n";
				$button .= "\t" . '<span class="wizylike_count">'.$this->likes_count.'</span>' . "\n";
				$button .= "\t" . '<span class="wizylike_linebreaker last"></span>' . "\n";
				$button .= "\t" . '<span class="wizylike_like_unlike" onclick="'.$onclick_like.'">'.$this->like_txt.'</span>' . "\n";
				$button .= '</div>' . "\n";
				
				
			} elseif($this->check_recurring_like()){
				// onclick javascript function for processing the unlike. found in wizylike.js in /js folder
				$onclick_unlike = 'wizylike(' . $this->post_id . ', ' . $this->user_id . ', \'unlike\')';
			
				// like button
				$button =  '<div class="wizylike_button '.$this->style.' light_bg '.$this->colour.'" id="wizylike-post-'.$this->post_id.'">' . "\n";
				$button .= "\t" . '<span class="wizylike_icon" onclick="'.$onclick_unlike.'">&nbsp;</span>' . "\n";
				$button .= "\t" . '<span class="wizylike_linebreaker first"></span>' . "\n";
				$button .= "\t" . '<span class="wizylike_count">'.$this->likes_count.'</span>' . "\n";
				$button .= "\t" . '<span class="wizylike_linebreaker last"></span>' . "\n";
				$button .= "\t" . '<span class="wizylike_like_unlike" onclick="'.$onclick_unlike.'">'.$this->unlike_txt.'</span>' . "\n";
				$button .= '</div>' . "\n";
			
			}
		} elseif(($this->capabilities === 'users-only' && !$this->user_is_loggedin)) {
			
			// count
			$button =  '<div class="wizylike_button '.$this->style.' light_bg '.$this->colour.' modal" id="wizylike-post-'.$this->post_id.'">' . "\n";
			$button .= "\t" . '<span class="wizylike_icon">&nbsp;</span>' . "\n";
			$button .= "\t" . '<span class="wizylike_linebreaker first"></span>' . "\n";
			$button .= "\t" . '<span class="wizylike_count">'.$this->likes_count.'</span>' . "\n";
			$button .= '</div>' . "\n";
			
		}
				
		return $button;
	}
	
}

?>