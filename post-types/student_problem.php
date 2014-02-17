<?php
/**
 * Construct a student problem custom post type
 *
 *
 * @package Juxtalearn_Hub
 * @subpackage Juxtalearn_Hub_CustomPostType
 */
new Student_Problem_Template();
class Student_Problem_Template extends Juxtalearn_Hub_CustomPostType
{
	public $post_type	= "student_problem";
	public $archive_slug = "student_problem"; // use pluralized string if you want an archive page
	public $singular  = "Student Problem";
	public $plural = "Student Problems";
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
				'menu_icon' => JUXTALEARN_HUB_URL.'/images/icons/example.png',
			)
		);			
	}
	/**
	* Register custom post type fields.
	*/
	public function set_options()
	{			
		//Pronamic_Google_Maps_Site::bootstrap();
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
				'label' => "Tricky Topic",
				'options' => $trickytopic_options,
				),
		));
		 $this->options = array_merge($this->options, array(
			'education_level' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'quick_edit' => true,
				'label' => 'Education Level',
				'options' => get_terms('juxtalearn_hub_education_level', 'hide_empty=0&orderby=id'),
				)
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
				'descr' => 'Optional field to associate example to a location',
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'location_id' => array(
				'type' => 'location',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => 'Location',
				'descr' => 'Optional field to associate example to a location',
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'link' => array(
				'type' => 'text',
				'save_as' => 'post_meta',
				'position' => 'bottom',
				'label' => 'Link'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'term01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'One term refers to multiple concepts'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'term02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Once concept has many scientific names'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'term03' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Scientific use of everyday language'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'pre01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Understanding of Scientific method, process and practice'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'pre02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Underpinning understandings'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'esn01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Underpinning concepts'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'esn02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Complementary concepts'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Weak human-like or world-like analogy '
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Key characteristic conveys group membership'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel03' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => 'Flawed causal reasoning'
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
		add_meta_box( 'tagsdiv-juxtalearn_hub_sb', 'Stumbling Blocks', 'post_tags_meta_box', $this->post_type, 'side', 'low', array( 'taxonomy' => 'juxtalearn_hub_sb' ));
		
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_tax_tool_section', $this->post_type),
			'Taxonomy: Why do students have this problem? Select all that apply.',
			array(&$this, 'add_inner_meta_boxes_tax_tool'),
			$this->post_type,
			'normal',
			'high'
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
				printf( __( '<a href="?post_type=student_problem&trickytopic_id=%s">%s</a>' ), $trickytopic_id, ucwords($trickytopic) );
			break;
		default :
			break;
		}
	}

	/**
	 * Save the metaboxes for this custom post type
	 */
	public function save_post($post_id)
	{	
		if (isset($_POST['tax_input'])){
			wp_set_post_terms( $_POST['juxtalearn_hub_trickytopic_id'], $_POST['tax_input']['juxtalearn_hub_sb'], 'juxtalearn_hub_sb', true ); 
		}
	} // END public function save_post($post_id)

} // END class Post_Type_Template