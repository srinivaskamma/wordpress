<?php

add_action( 'widgets_init', 'wizylike_widget' );

// registers the widget
function wizylike_widget() {
	register_widget( 'WizyLike_Widget' );
} // end wizylike_widget


class WizyLike_Widget extends WP_Widget {
	
	function WizyLike_Widget() {
		
		// Widget settings
		$widget_ops = array( 'classname' => 'most-liked', 'description' => 'A widget to display most liked posts' );

		// Widget control settings
		$control_ops = array('id_base' => 'wizylike-widget' );

		// Create the widget
		$this->WP_Widget( 'wizylike-widget', 'Most Liked Posts', $widget_ops, $control_ops );
		
	}
	
	
	function widget($args, $instance) {
		global $wpdb, $wl_tablename;
		extract($args);

		// User-selected settings
		$title = apply_filters('widget_title', $instance['title']);
		$count = $instance['posts_num'];

		// Before widget (defined by themes)
		echo $before_widget;

		// Title of widget (before and after defined by themes)
		if($title)
			echo $before_title . $title . $after_title;

		$widget_posts = $wpdb->get_results("SELECT * FROM $wpdb->posts
	WHERE post_status = 'publish' AND post_type = 'post' ORDER BY like_count DESC LIMIT $count");
		
		if ($widget_posts):
			echo '<ul id="wizylike_mostliked">';
			foreach ($widget_posts as $post):
				
				$wizylike_disabled = get_post_meta($post->ID, 'wizylike', true);
				if(!isset($wizylike_disabled) || $wizylike_disabled != 'disabled'):
		?>
			<li id="mostliked_post-<?php echo $post->ID ?>">
			
				<?php if(has_post_thumbnail($post->ID)) : ?>
					<?php echo get_the_post_thumbnail($post->ID, 'small'); ?> 
				<?php endif; ?>
				
				<div class="mostliked_post_meta">
					<h4 class="mostliked_post_title">
						<a href="<?php echo $post->guid ?>" title="<?php echo $post->post_title ?>"><?php echo $post->post_title ?></a>
					</h4>
					<span class="mostliked_like_count">
						<?php echo $post->like_count . ' '; if(get_option('wizylike_widget_txt')) echo get_option('wizylike_widget_txt') ?>
					</span>
				</div>
				
			</li>
		<?php
				endif;
			endforeach;
			echo '</ul>';
		else :
		?>
			<ul id="wizylike_mostliked">
				<li>
					<h2>Not Found</h2>
				</li>
			</ul>
		<?php endif;
	
		// After widget (defined by themes)
		echo $after_widget;
	}
	
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['posts_num'] = strip_tags( $new_instance['posts_num'] );

		return $instance;
	}
	
	
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Most Liked Posts', 'posts_num' => '5');
		$instance = wp_parse_args((array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"  class="widefat"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'posts_num' ); ?>">Number of posts to show:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'posts_num' ); ?>" name="<?php echo $this->get_field_name( 'posts_num' ); ?>" value="<?php echo $instance['posts_num']; ?>" size="3"/>
		</p>
		
		<?php
	}
	
}

?>