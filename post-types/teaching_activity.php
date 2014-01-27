<?php
if(!class_exists('Teaching_Activity_Template'))
{
	/**
	 * A PostTypeTemplate class that provides 3 additional meta fields
	 */
	class Teaching_Activity_Template
	{
		const POST_TYPE	= "teaching_activity";
		const ARCHIVE_SLUG = "teaching_activity"; // use pluralized string if you want an archive page
		const SINGULAR = "Teaching Activity";
		const PLURAL = "Teaching Activities";
		var $options = array();

    	/**
    	 * The Constructor
    	 */
    	public function __construct()
    	{
    		// register actions
    		add_action('init', array(&$this, 'init'));
    		add_action('admin_init', array(&$this, 'admin_init'));
			add_action('manage_edit-'.self::POST_TYPE.'_columns', array(&$this, 'columns'));
			add_action('manage_'.self::POST_TYPE.'_posts_custom_column', array(&$this, 'column'),10 ,2);
			add_action('wp_ajax_juxtalearn_hub_location_callback', array(&$this, 'ajax_juxtalearn_hub_location_callback') );
			add_action('wp_ajax_juxtalearn_hub_if_location_exists_by_value', array(&$this, 'ajax_juxtalearn_hub_if_location_exists_by_value') );
			add_action('wp_ajax_juxtalearn_hub_tricky_topic_details', array(&$this, 'ajax_juxtalearn_hub_tricky_topic_details') );
			
			
			JuxtaLearn_Hub::$post_types[] = self::POST_TYPE;
			
    	} // END public function __construct()

    	/**
    	 * hook into WP's init action hook
    	 */
    	public function init()
    	{		
			// Initialize Post Type
    		$this->create_post_type();
    		add_action('save_post', array(&$this, 'save_post'));
    	} // END public function init()
		
		
		public function columns($columns) {
			return array_slice($columns, 0, 3, true) +
    				array('juxtalearn_hub_trickytopic_id' => __( 'Tricky Topic' )) +
   					array_slice($columns, 3, count($columns) - 1, true) ;
		}
		
		public function add_to_bulk_quick_edit_custom_box( $column_name, $post_type ) {
			print_r($column_name);
			$type = str_replace('juxtalearn_hub_', '', $column_name);
			switch ($type) {
				case 'sector':
					JuxtaLearn_Hub::get_select_quick_edit($this->options->$type, $column_name);
					break;
				default:
					break;
			}			
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
					printf( __( '<a href="?post_type=teaching_activity&tt_id=%s">%s</a>' ), $trickytopic_id, ucwords($trickytopic) );
				break;
			default :
				break;
			}
		}
		
		
    	/**
    	 * Create the post type
    	 */
    	public function create_post_type()
    	{
    		register_post_type(self::POST_TYPE,
    			array(
    				'labels' => array(
    					'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::POST_TYPE)))),
    					'singular_name' => __(ucwords(str_replace("_", " ", self::POST_TYPE)))
    				),
					'labels' => array(
						'name' => __(sprintf('%s', self::PLURAL)),
						'singular_name' => __(sprintf('%s', self::SINGULAR)),
						'add_new' => __(sprintf('Add New %s', self::SINGULAR)),
						'add_new_item' => __(sprintf('Add New %s', self::SINGULAR)),
						'edit_item' => __(sprintf('Edit %s', self::SINGULAR)),
						'new_item' => __(sprintf('New %s', self::SINGULAR)),
						'view_item' => __(sprintf('View %s', self::SINGULAR)),
						'search_items' => __(sprintf('Search %s', self::PLURAL)),
						'not_found' => __(sprintf('No %s found', self::PLURAL)),
						'not_found_in_trash' => __(sprintf('No found in Trash%s', self::PLURAL)),
					),
    				'public' => true,
    				'description' => __("An example of a Student Problem"),
    				'supports' => array(
    					'title', 'editor', 'excerpt', 'author' 
    				),
					'has_archive' => true,
					'rewrite' => array(
						'slug' => self::ARCHIVE_SLUG,
						'with_front' => false,
					),
					'menu_position' => 30,
					'menu_icon' => JUXTALEARN_HUB_URL.'/images/icons/example.png',
    			)
    		);
			
			$args = JuxtaLearn_Hub::get_taxonomy_args("Education Level","Education Levels", 'education_level');
			register_taxonomy( 'juxtalearn_hub_education_level', array(self::POST_TYPE, 'student_problem'), $args );
			//$args = JuxtaLearn_Hub::get_taxonomy_args("Type","Types");
			//register_taxonomy( 'juxtalearn_hub_type', array(self::POST_TYPE, 'student_problem'), $args );
			
			
    	}
	
    	/**
    	 * Save the metaboxes for this custom post type
    	 */
    	public function save_post($post_id)
    	{
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
			if (get_post_type($post_id) != self::POST_TYPE) return;
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
			if (isset($_POST['juxtalearn_hub_nonce']) && !wp_verify_nonce($_POST['juxtalearn_hub_nonce'], plugin_basename(__FILE__))) return;
			if (!current_user_can('edit_post', $post_id)) return;

			foreach($this->options as $name => $option)
			{
				// Update the post's meta field
				$field_name = "juxtalearn_hub_$name";
				if (isset($_POST[$field_name])){
					if ($option['save_as'] == 'term'){
						wp_set_object_terms( $post_id, $_POST[$field_name], $field_name);
					} else {
						update_post_meta($post_id, $field_name, $_POST[$field_name]);
					}
				}
			}
    	} // END public function save_post($post_id)

    	/**
    	 * hook into WP's admin_init action hook
    	 */
    	public function admin_init()
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
			/*
			$this->options = array_merge($this->options, array(
				'type' => array(
					'type' => 'select',
					'save_as' => 'term',
					'position' => 'side',
					'label' => "Type",
					'options' => get_terms('juxtalearn_hub_type', 'hide_empty=0&orderby=id'),
					),
			));*/
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
				'link' => array(
					'type' => 'text',
					'save_as' => 'post_meta',
					'position' => 'bottom',
					'label' => 'Link'
					)
			 ));

			
			// Add metaboxes
    		add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
			//add_action( 'bulk_edit_custom_box', array(&$this,'add_to_bulk_quick_edit_custom_box'), 10, 2 );
			//add_action( 'quick_edit_custom_box', array(&$this,'add_to_bulk_quick_edit_custom_box'), 10, 2 );
    	} // END public function admin_init()

			
    	/**
    	 * hook into WP's add_meta_boxes action hook
    	 */
    	public function add_meta_boxes()
    	{
    		// Add this metabox to every selected post	
    		add_meta_box( 
    			sprintf('wp_juxtalearn_hub_%s_section', self::POST_TYPE),
    			sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
    			array(&$this, 'add_inner_meta_boxes'),
    			self::POST_TYPE,
				'normal',
				'high'
    	    );
			add_meta_box( 
    			sprintf('wp_juxtalearn_hub_%s_side_section', self::POST_TYPE),
    			sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
    			array(&$this, 'add_inner_meta_boxes_side'),
    			self::POST_TYPE,
				'side'
    	    );
			remove_meta_box('tagsdiv-juxtalearn_hub_country',self::POST_TYPE,'side');
			remove_meta_box('tagsdiv-juxtalearn_hub_type',self::POST_TYPE,'side');
			remove_meta_box('tagsdiv-juxtalearn_hub_education_level',self::POST_TYPE,'side');
			remove_meta_box('tagsdiv-juxtalearn_hub_sb',self::POST_TYPE,'side');
			//remove_meta_box('fzisotope_categoriesdiv', 'fzisotope_post', 'side');
			add_meta_box( 'tagsdiv-juxtalearn_hub_sb', 'Stumbling Blocks', 'post_tags_meta_box', self::POST_TYPE, 'side', 'low', array( 'taxonomy' => 'juxtalearn_hub_sb' ));
			add_meta_box( 
    			sprintf('wp_juxtalearn_hub_%s_section', self::POST_TYPE),
    			sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
    			array(&$this, 'add_inner_meta_boxes'),
    			self::POST_TYPE,
				'normal',
				'high'
    	    );
			
    	} // END public function add_meta_boxes()
		

		 /**
		 * called off of the add meta box
		 */		
		public function add_inner_meta_boxes_side($post)
		{		
			wp_nonce_field(plugin_basename(__FILE__), 'juxtalearn_hub_nonce');
			$sub_options = JuxtaLearn_Hub::filterOptions($this->options, 'position', 'side');
			include(sprintf("%s/custom_post_metaboxes.php", dirname(__FILE__)));		
		} // END public function add_inner_meta_boxes($post)
		
		/**
		 * called off of the add meta box
		 */		
		public function add_inner_meta_boxes($post)
		{		
			// Render the job order metabox
			$sub_options = JuxtaLearn_Hub::filterOptions($this->options, 'position', 'bottom');
			include(sprintf("%s/custom_post_metaboxes.php", dirname(__FILE__)));			
		} // END public function add_inner_meta_boxes($post)
		
		
		
		public function ajax_juxtalearn_hub_location_callback() {
			global $wpdb;
			
			// if search term exists
			if ( $search_term = ( isset( $_POST[ 'juxtalearn_hub_location_search_term' ] ) && ! empty( $_POST[ 'juxtalearn_hub_location_search_term' ] ) ) ? $_POST[ 'juxtalearn_hub_location_search_term' ] : NULL ) {
				if ( ( $locations = $wpdb->get_results( "SELECT posts.ID, posts.post_title, postmeta.meta_value  FROM $wpdb->posts posts INNER JOIN $wpdb->postmeta postmeta ON postmeta.post_id = posts.ID AND postmeta.meta_key ='_pronamic_google_maps_address' WHERE ( (posts.post_title LIKE '%$search_term%' OR postmeta.meta_value LIKE '%$search_term%') AND posts.post_type = 'location' AND post_status = 'publish' ) ORDER BY posts.post_title" ) )
				&& is_array( $locations ) ) {
					$results = array();
					// loop through each user to make sure they are allowed
					foreach ( $locations  as $location ) {								
							$results[] = array(
								'location_id'	=> $location->ID,
								'label'			=> $location->post_title,
								'address'		=> $location->meta_value, 
								);
					}
					// "return" the results
					//wp_reset_postmeta();
					echo json_encode( $results );
				}
			}
			die();
		}
		
		
		public function ajax_juxtalearn_hub_if_location_exists_by_value() {
			if ( $location_id = ( isset( $_POST[ 'autocomplete_eh_location_id' ] ) && ! empty( $_POST[ 'autocomplete_eh_location_id' ] ) ) ? $_POST[ 'autocomplete_eh_location_id' ] : NULL ) {
				$location_name = $_POST[ 'autocomplete_eh_location_value' ];
			
				$actual_location_name = get_the_title($location_id);
				
				if($location_name !== $actual_location_name){
					echo json_encode( (object)array( 'notamatch' => 1 ) );
					die();
				} else {	
					echo json_encode( (object)array( 'valid' => 1,
													 //'map' => $mapcode,
													 'country' => ($loc = wp_get_object_terms($location_id, 'juxtalearn_hub_country')) ? $loc[0]->slug : NULL,
													 'lat' => get_post_meta($location_id, '_pronamic_google_maps_latitude', true ),
													 'lng' => get_post_meta($location_id, '_pronamic_google_maps_longitude', true ),
													 'zoom' => get_post_meta($location_id, '_pronamic_google_maps_zoom', true )));
					die();
				}
			} 
			echo json_encode( (object)array( 'noid' => 1 ) );
			die();
		}
		
		public function ajax_juxtalearn_hub_tricky_topic_details() {
			if ( $post_id = ( isset( $_POST[ 'tt_id' ] ) && ! empty( $_POST[ 'tt_id' ] ) ) ? $_POST[ 'tt_id' ] : NULL ) {
				echo json_encode( (object)array( 'juxtalearn_hub_country' => wp_get_object_terms($post_id,'juxtalearn_hub_country', array("fields" => "slugs")),
												 'juxtalearn_hub_location_id_field' => get_the_title(get_post_meta($post_id,'juxtalearn_hub_location_id',true)),
												 'juxtalearn_hub_location_id' => get_post_meta($post_id,'juxtalearn_hub_location_id',true),
												 'juxtalearn_hub_sb' => wp_get_post_terms( $post_id, 'juxtalearn_hub_sb', array("fields" => "names"))) );
				die();
			}
			echo json_encode( (object)array( 'noid' => 1 ) );
			die();
		}
		  
	} // END class Post_Type_Template
} // END if(!class_exists('Post_Type_Template'))