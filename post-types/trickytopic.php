<?php
/**
 * Construct a Teaching Activity custom post type
 *
 *
 * @package Juxtalearn_Hub
 * @subpackage Juxtalearn_Hub_CustomPostType
 */
class TrickyTopic_Template extends Juxtalearn_Hub_CustomPostType
{
	public $post_type = "tricky_topic";
	public $archive_slug = "trickytopic"; // use pluralized string if you want an archive page
	public $singular = "Tricky Topic";
	public $plural = "Tricky Topics";
	public $options = array();

	/**
	 * Create the post type
	 */
	public function create_post_type()
	{
		register_post_type($this->post_type,
			array(
				'labels' => array(
					'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", $this->post_type)))),
					'singular_name' => __(ucwords(str_replace("_", " ", $this->post_type)))
				),
				'labels' => array(
					'name' => __(sprintf('%s', $this->plural)),
					'singular_name' => __(sprintf('%s', $this->singular)),
					'add_new' => __(sprintf('Add New %s', $this->singular)),
					'add_new_item' => __(sprintf('Add New %s', $this->singular)),
					'edit_item' => __(sprintf('Edit %s', $this->singular)),
					'new_item' => __(sprintf('New %s', $this->singular)),
					'view_item' => __(sprintf('View %s', $this->singular)),
					'search_items' => __(sprintf('Search %s', $this->plural)),
					'not_found' => __(sprintf('No %s found', $this->plural)),
					'not_found_in_trash' => __(sprintf('No found in Trash%s', $this->plural)),
				),
				'public' => true,
				'description' => __("An example of a Student Problem"),
				'supports' => array(
					'title', 'editor', 'excerpt', 'author' 
				),
				'has_archive' => true,
				'rewrite' => array(
					'slug' => $this->archive_slug,
					'with_front' => false,
				),
				'menu_position' => 30,
				'menu_icon' => JUXTALEARN_HUB_URL.'/images/icons/tt.png',
			)
		);

	}

	/**
	 * set options
	 */
	public function set_options()
	{	
				
		$this->options = array_merge($this->options, array(
			'subject' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'label' => "Subject",
				'options' => get_terms('juxtalearn_hub_subject', 'hide_empty=0&orderby=id'),
				),
			));
		$this->options = array_merge($this->options, array(
			'country' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'label' => "Country",
				'options' => get_terms('juxtalearn_hub_country', 'hide_empty=0'),
				),
		 ));
		 $this->options = array_merge($this->options, array(
			'location_id' => array(
				'type' => 'location',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => 'Location',
				'descr' => 'Optional field to associate tricky topic to a location',
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'add_link' => array(
				'type' => 'add_link',
				'position' => 'side',
				'label' => "Action",
				),
			));
		// Add metaboxes
		
	} // END public function admin_init()
		
	/**
	 * hook into WP's add_meta_boxes action hook
	 */
	public function add_meta_boxes()
	{
		// Add this metabox to every selected post
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_side_section', $this->post_type),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->post_type))),
			array(&$this, 'add_inner_meta_boxes_side'),
			$this->post_type,
			'side'
		);	
		remove_meta_box('tagsdiv-juxtalearn_hub_subject',$this->post_type,'side');
		remove_meta_box('tagsdiv-juxtalearn_hub_sb',$this->post_type,'side');
		remove_meta_box('tagsdiv-juxtalearn_hub_country',$this->post_type,'side');
		add_meta_box( 'tagsdiv-juxtalearn_hub_sb', 'Stumbling Blocks', 'post_tags_meta_box', $this->post_type, 'side', 'low', array( 'taxonomy' => 'juxtalearn_hub_sb' ));				
	} // END public function add_meta_boxes()
			
	public function custom_post_type_link($post_link, $post = 0, $leavename = false) {		
		if ($post->post_type == 'tricky_topic') {
			return str_replace('%post_id%', $post->ID, $post_link);
		} else {
			return $post_link;
		}
	}

} // END class Post_Type_Template