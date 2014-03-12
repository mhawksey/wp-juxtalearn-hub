<?php
/**
 * Subject summary counts and links 
 *
 * Generates metadata bars for policy
 * Shortcode: [subject_summary]
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */

// Base class 'JuxtaLearn_Hub_Shortcode' defined in 'shortcodes/shortcode.php'.
class JuxtaLearn_Hub_Shortcode_Subject_Summary extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'subject_summary';
	public $defaults = array(	);

	public static $post_types_with_example = NULL;

    /**
     * @return string
     */
	public function content() {
		ob_start();
		extract($this->options);
		//Not used :: $subject = wp_get_post_terms($trickytopic, 'juxtalearn_hub_subject');
		//list terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)
		
		$taxonomy     = 'juxtalearn_hub_subject';
		$orderby      = 'name'; 
		$show_count   = 1;      // 1 for yes, 0 for no
		$pad_counts   = 0;      // 1 for yes, 0 for no
		$hierarchical = 0;      // 1 for yes, 0 for no
		$title        = '';
		
		$args = array(
		  'taxonomy'     => $taxonomy,
		  'orderby'      => $orderby,
		  'show_count'   => $show_count,
		  'pad_counts'   => $pad_counts,
		  'hierarchical' => $hierarchical,
		  'title_li'     => $title
		);
		?>
		<div class="subject-wrap"><h3>Subjects</h3>
		<ul class="subjectlist">
		<?php wp_list_categories( $args ); ?>
		</ul>
        </div>
        <?php
 		return ob_get_clean();
	} // end of function content


} // end of class
