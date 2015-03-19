<?php
/**
 * Construct a Teaching Activity custom post type
 *
 *
 * @package Juxtalearn_Hub
 * @subpackage Juxtalearn_Hub_CustomPostType
 */
class Teaching_Activity_Template extends Juxtalearn_Hub_CustomPostType
{
	public $post_type = "teaching_activity";
	public $archive_slug = "teaching_activity"; // use pluralized string if you want an archive page
	public $singular = "Teaching Activity";
	public $plural = "Teaching Activities";
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
				'description' => __('An example of a Student Problem', self::LOC_DOMAIN),
				'supports' => array(
					'title', 'editor', 'excerpt', 'author' 
				),
				'has_archive' => true,
				'rewrite' => array(
					'slug' => $this->archive_slug,
					'with_front' => false,
				),
				'menu_position' => 30,
				'menu_icon' => JUXTALEARN_HUB_URL.'/images/icons/example.png',
			)
		);
		
	}
	
	/**
	 * set options
	 */
	public function set_options()
	{			
		$trickytopic_options = array();

		$trickytopic_query = new WP_Query(array(
			'post_type' => 'tricky_topic',
			'posts_per_page' => -1, // show all
			'orderby' => 'title',
			'order' => 'ASC',
		));
		
		foreach ($trickytopic_query->posts as $trickytopic) {
			$trickytopic_options[$trickytopic->ID] = get_the_title($trickytopic->ID);
		}
		
		
		$this->options = array_merge($this->options, array(
			'trickytopic_id' => array(
				'type' => 'select',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => __('Tricky Topic', self::LOC_DOMAIN),
				'options' => $trickytopic_options,
				),
		));
		 $this->options = array_merge($this->options, array(
			'education_level' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'quick_edit' => true,
				'label' => __('Education Level', self::LOC_DOMAIN),
				'options' => get_terms('juxtalearn_hub_education_level', 'hide_empty=0&orderby=id'),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'country' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'label' => __('Country', self::LOC_DOMAIN),
				'options' => get_terms('juxtalearn_hub_country', 'hide_empty=0'),
				),
		 ));
		 $this->options = array_merge($this->options, array(
			'location_id' => array(
				'type' => 'location',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => __('Location', self::LOC_DOMAIN),
				'descr' => __('Optional field to associate example to a location', self::LOC_DOMAIN),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'link' => array(
				'type' => 'text',
				'save_as' => 'post_meta',
				'position' => 'bottom',
				'label' => __('Link', self::LOC_DOMAIN),
				)
		 ));

	} // END public function set_options()
	
	/**
	 * hook into WP's add_meta_boxes action hook
	 */
	public function add_meta_boxes()
	{
		// Add this metabox to every selected post	
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_section', $this->post_type),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->post_type))),
			array(&$this, 'add_inner_meta_boxes'),
			$this->post_type,
			'normal',
			'high'
		);
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_side_section', $this->post_type),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->post_type))),
			array(&$this, 'add_inner_meta_boxes_side'),
			$this->post_type,
			'side'
		);
		remove_meta_box('tagsdiv-juxtalearn_hub_country',$this->post_type,'side');
		remove_meta_box('tagsdiv-juxtalearn_hub_type',$this->post_type,'side');
		remove_meta_box('tagsdiv-juxtalearn_hub_education_level',$this->post_type,'side');
		remove_meta_box('tagsdiv-juxtalearn_hub_sb',$this->post_type,'side');
		//remove_meta_box('fzisotope_categoriesdiv', 'fzisotope_post', 'side');
		//NDF: [#5] [#10]
		add_meta_box(
			'tagsdiv-juxtalearn_hub_sb',
			__('Stumbling Blocks', self::LOC_DOMAIN),
			array(&$this, 'custom_sb_meta_box'), #'post_tags_meta_box'
			$this->post_type,
			'side',
			'low',
			array( 'taxonomy' => 'juxtalearn_hub_sb' )
		);
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_section', $this->post_type),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->post_type))),
			array(&$this, 'add_inner_meta_boxes'),
			$this->post_type,
			'normal',
			'high'
		);
		
	} // END public function add_meta_boxes()
	
	public function columns($columns) {
		return array_slice($columns, 0, 3, true) +
				array('juxtalearn_hub_trickytopic_id' => __( 'Tricky Topic' )) +
				array_slice($columns, 3, count($columns) - 1, true) ;
	}
	
	public function column($column, $post_id) {
		global $post;
		switch (str_replace('juxtalearn_hub_', '', $column)) {
		case 'trickytopic_id':
			$trickytopic_id = get_post_meta( $post_id, $column, true );
			$trickytopic = get_the_title($trickytopic_id);
			if ( empty( $trickytopic ) )
				echo __( 'Empty' );
			else
				printf( '<a href="?post_type=teaching_activity&trickytopic_id=%s">%s</a>',
					$trickytopic_id, ucwords($trickytopic) );
			break;
		default :
			break;
		}
	}

	  
} // END class Post_Type_Template