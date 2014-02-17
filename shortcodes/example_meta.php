<?php
/**
 * Meta Bars Shortcode class used to construct shortcodes
 *
 * Generates metadata bars for policy
 * Shortcode: [example_meta]
 * Options: title - boolean|string
 *			location - header|footer|false
 *			header_terms - comma seperated list of fields to display
 *			footer_terms -  comma seperated list of fields to display
 *			no_example_message - message used on error
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */
new JuxtaLearn_Hub_Shortcode_Example_Meta();
class JuxtaLearn_Hub_Shortcode_Example_Meta extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'example_meta';
	public $defaults = array(
		'title' => false,
		'location' => 'header',
		'header_terms' => 'sb',
		'footer_terms' => 'post_type,type,education_level,country,trickytopic,link',
		'no_example_message' => "There is no meta data for this example",
	);

	

	static $post_types_with_example = array('student_problem', 'teaching_activity');
	
	function add_to_page($content) {
		if (in_array(get_post_type(), self::$post_types_with_example)) {
			$content = do_shortcode('[example_meta location="header"]').$content.do_shortcode('[example_meta location="footer"]');
		}
		return $content;
	}

	function content() {
		ob_start();
		extract($this->options);
		$post_id = get_the_ID();
		$errors = array();

		if (!$post_id) $errors[] = "No posts ID provided";
		
		$post = JuxtaLearn_Hub::add_meta($post_id);
		
		$post['post_type'] = get_post_type($post_id);
		if (!$post) {
			$errors[] = "$post_id is not a valid post ID";
		} /*else if (!in_array($post['post_type'], self::$post_types_with_example)) {
			$errors[] = "<a href='".get_permalink($post_id)."'>".get_the_title($post_id)."</a> is not the correct type of post";
		}*/ else if ($location=="header") { 
			$this->meta_bar($post, $header_terms);
		} else if ($location=="footer") { 
			$this->meta_bar($post, $footer_terms);
		}
		
		if (count($errors)) return "[Shortcode errors (".$this->shortcode."): ".implode(', ', $errors)."]";
		
		return ob_get_clean();
	}
}