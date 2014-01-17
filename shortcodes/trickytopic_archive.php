<?php

new JuxtaLearn_Hub_Shortcode_TrickyTopic_Archive();
class JuxtaLearn_Hub_Shortcode_TrickyTopic_Archive extends JuxtaLearn_Hub_Shortcode {
	var $shortcode = 'trickytopic_archive';
	var $defaults = array();

	static $post_types_with_sessions = NULL;

	function content() {
		ob_start();
		extract($this->options);
		$args=array(
		  'post_type' => 'trickytopic',
		  'post_status' => 'publish',
		  'orderby' => 'title',
		  'order' => 'ASC',
		  'posts_per_page' => -1
		);
		$my_query = null;
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post();
		  	global $more; $more = 0; 
			get_template_part( 'content');
		  endwhile;
		}
		wp_reset_query(); 
		return ob_get_clean();
	}
}