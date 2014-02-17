<?php
/**
 *
 */

new JuxtaLearn_Hub_Shortcode_Subject_Summary();
// Base class 'JuxtaLearn_Hub_Shortcode' defined in 'shortcodes/shortcode.php'.
class JuxtaLearn_Hub_Shortcode_Subject_Summary extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'subject_summary';
	public $defaults = array(

	);

	

	public static $post_types_with_example = NULL;
	
	public function prep_options() {
	   // Turn csv into array
		if (!is_array($this->options['post_ids'])) {
			$this->options['post_ids'] = array();
		}

		if (!empty($this->options['post_ids'])) {
			$this->options['post_ids'] = explode(',', $this->options['post_ids']);
		}

		// add post_id to post_ids and get rid of it
		// Note, 'post_id' is not a bug!
		if ($this->options['post_id']) {
			$this->options['post_ids'] = array_merge($this->options['post_ids'], explode(',', $this->options['post_id']));
		}
		unset($this->options['post_id']);
		
		// fallback to current post if nothing specified
		if (empty($this->options['post_ids']) && $GLOBALS['post']->ID) {
			$this->options['post_ids'] = array($GLOBALS['post']->ID);
		}
		
		// unique list
		$this->options['post_ids'] = array_unique($this->options['post_ids']);
	}


    /**
     * @return string
     */
	public function content() {
		ob_start();
		extract($this->options);
		$subject = wp_get_post_terms($trickytopic, 'juxtalearn_hub_subject');
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
		<div><strong>Subjects</strong>
		<ul class="subjectlist">
		<?php wp_list_categories( $args ); ?>
		</ul>
        </div>
        <?php
 		return ob_get_clean();
	} // end of function content


} // end of class
