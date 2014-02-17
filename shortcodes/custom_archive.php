<?php
/**
 * Creates shortcode to list custom post type archive
 *
 * Generates metadata bars for policy
 * Shortcode: [custom_archive]
 * Options: posttype - string custom post_type you want to show
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */
new JuxtaLearn_Hub_Shortcode_Custom_Archive();
class JuxtaLearn_Hub_Shortcode_Custom_Archive extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'custom_archive';
	public $defaults = array('posttype' => false);

	static $post_types_with_sessions = NULL;

	function content() {
		ob_start();
		extract($this->options);
		global $wp_query;
		
		// http://wordpress.stackexchange.com/a/120408/45617
		// Define custom query parameters
		$custom_query_args = array( 'post_type' => $posttype,
								    'post_status' => 'publish',
									 );
		
		// Get current page and append to custom query parameters array
		$custom_query_args['paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		
		// Instantiate custom query
		$custom_query = new WP_Query( $custom_query_args );
		
		// Pagination fix
		$temp_query = $wp_query;
		$wp_query   = NULL;
		$wp_query   = $custom_query;
		
		// Output custom query loop
		if ( $custom_query->have_posts() ) :
			while ($custom_query->have_posts() ) : $custom_query->the_post();
				get_template_part( 'content');
			endwhile;
		else : ?>

			<article id="post-0" class="post no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'Nothing Found', 'tinyforge' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'tinyforge' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->

		<?php endif; 
		// Reset postdata
		wp_reset_postdata();
		// Custom query loop pagination
		?>
		<nav class="nav-single">
					<h3 class="assistive-text"><?php _e( 'Post navigation', 'tinyforge' ); ?></h3>
					<span class="nav-previous"><span class="meta-nav"><?php previous_posts_link( '&laquo; Next' ); ?></span></span>
					<span class="nav-next"><span class="meta-nav"><?php next_posts_link( 'Previous &raquo;', $custom_query->max_num_pages ); ?></span></span>
		</nav><!-- .nav-single -->
		<?php 

		// Reset main query object
		$wp_query = NULL;
		$wp_query = $temp_query;
		
		return ob_get_clean();
	}
}