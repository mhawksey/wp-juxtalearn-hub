<?php
/**
 * Construct a student problem custom post type
 *
 *
 * @package Juxtalearn_Hub
 * @subpackage Juxtalearn_Hub_CustomPostType
 */
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
				'description' => __("An example of a Student Problem", self::LOC_DOMAIN),
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
		 // 'location_id' array_merge was duplicated?!
		 $this->options = array_merge($this->options, array(
			'location_id' => array(
				'type' => 'location',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => __('Location', self::LOC_DOMAIN),
				'descr' =>
				__('Optional field to associate example to a location', self::LOC_DOMAIN),
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
		 $this->options = array_merge($this->options, array(
			'term01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('One term refers to multiple concepts', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'term02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('One concept has many scientific names', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'term03' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Scientific use of everyday language', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'pre01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' =>
			__('Understanding of Scientific method, process and practice', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'pre02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Underpinning understandings', self::LOC_DOMAIN),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'esn01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Underpinning concepts', self::LOC_DOMAIN),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'esn02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Complementary concepts', self::LOC_DOMAIN),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel01' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Weak human-like or world-like analogy', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel02' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' =>
				__('Key characteristic conveys group membership', self::LOC_DOMAIN)
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'bel03' => array(
				'type' => 'checkbox',
				'save_as' => 'post_meta',
				'position' => 'tax_tool',
				'label' => __('Flawed causal reasoning', self::LOC_DOMAIN),
				)
		 ));

	} // END public function set_options()

	/**
	 * Used by JuxtaLearn_Quiz_Scaffold [Bug #8].
	 *
	 * @link https://github.com/mhawksey/wp-juxtalearn-hub/issues/8
	 */
	public function get_options() {
		return $this->options;
	}

	/** Used internally and by JuxtaLearn_Quiz_Scaffold [Bug #8].
	 */
	public function get_tax_tool_tabs() {
		return array(
			array('name' => __('Terminology', self::LOC_DOMAIN),
				 'id'=> 'term',
				 'description' =>
					__('Problems with use of language and scientific terms, inconsistent and overlapping terminology.', self::LOC_DOMAIN),
				 'prompt' => array( 'term01' =>
					__('One scientific term has a different meaning depending on the context it is used in. e.g. volts and voltage in Physics. Use of the term kinetic energy in both Physics and in Biology.', self::LOC_DOMAIN),
									'term02' =>
					__('Different terms are used to refer to the same concept. e.g. voltage is also referred to as potential difference. Confusion between voltage and charge.', self::LOC_DOMAIN),
									'term03' =>
					__('Reuse of everyday terms that students believe they understand in a scientific context. e.g. the "drop" part of "forward voltage drop", "work done" in physics.', self::LOC_DOMAIN),
								   ),
				 ),
			array('name' => __('Incomplete Pre-Knowledge', self::LOC_DOMAIN),
				 'id'=> 'pre',
				 'description' =>
					__('Previous understandings that need to be unlearned, modified or improved to understand the Tricky Topic', self::LOC_DOMAIN),
				 'prompt' => array( 'pre01' =>
					__('Simplistic understandings that need to be unlearned e.g. imagining atomic structure as balls on sticks suggests space between atoms.', self::LOC_DOMAIN),
									'pre02' =>
					__('Understanding  that the student is expected to know already. e.g. to do the calculations related to Avogadro\'s number in Chemistry assumes a math understanding of powers of ten and ratios.', self::LOC_DOMAIN),
								   ),
				 ),
			array('name' => __('Essential concepts', self::LOC_DOMAIN),
				 'id'=> 'esn',
				 'description' =>
					__('Key assumptions and knowledge that relate to the tricky topic, without which it is impossible to understand it', self::LOC_DOMAIN),
				 'prompt' => array( 'esn01' =>
					__('Knowledge is required in order to understand the Stumbling Block. e.g. to understand genetic drift, a student needs to already know about natural selection.', self::LOC_DOMAIN),
									'esn02' =>
					__('Complementary knowledge the student needs to learn alongside the stumbling block. Understanding genetic drift involves learning about its causes; founder effect and bottleneck effect.', self::LOC_DOMAIN),
								  ),
 					),
			array('name' => __('Intuitive Beliefs', self::LOC_DOMAIN),
 				  'id'=> 'bel',
				 'description' =>
					__('Informal, intuitive ways of thinking about the world. Strongly biased toward causal explanations', self::LOC_DOMAIN),
				 'prompt' => array('bel01' =>
					__('Human-Like or world like analogy. Viewing scientific concepts in terms of everyday phenomena e.g. males of any species are bigger than females. Plants suck up food from soil thru roots. Analogy based on metaphor that doesn\'t carry through e.g. "Stage" and "Costume" used in Sense programming.', self::LOC_DOMAIN),
								   'bel02' =>
					__('The belief that if one condition is fulfilled, then the object is automatically a member of a group. One unobservable core feature defines membership of a category eg: one to one relationship between DNA and physical traits. Birds have wings therefore all creatures with wings are birds.', self::LOC_DOMAIN),
									'bel03' =>
					__('Reasoning based on the assumption of goal or purpose eg birds have wings so they can fly. Genes turn off so that cell can develop properly. Inappropriate assumption of cause and effect, eg release an object along a curved path and it will continue in a curve, rocks are pointy so animals won\'t sit on them and crush them. ', self::LOC_DOMAIN),
								   ),
					),
			);
	}

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
		//NDF:
		add_meta_box(
			'tagsdiv-juxtalearn_hub_sb',
			__('Stumbling Blocks', self::LOC_DOMAIN),
			array(&$this, 'custom_sb_meta_box'), //'post_tags_meta_box'
			$this->post_type,
			'side',
			'low',
			array( 'taxonomy' => 'juxtalearn_hub_sb' )
		);
		
		add_meta_box( 
			sprintf('wp_juxtalearn_hub_%s_tax_tool_section', $this->post_type),
			__('Taxonomy: Why do students have this problem? Select all that apply.', self::LOC_DOMAIN),
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

	/**NDF: Stumbling Block tags widget [#5].
	 */
	public function custom_sb_meta_box( $post, $box ) {
		// ?tags-ui=classic|wordpress|wp
		if (isset($_GET['tags-ui']) && preg_match('/^(c|w)/', $_GET['tags-ui'])):
			post_tags_meta_box($post, $box);
		else:
		?><pre><?php
			#var_dump($this->options);
		?></pre>
		<div class=tagsdiv id=juxtalearn_hub_sb ><!--HACK: -->
		<div class="ajaxtag hide-if-no-js">
		  <label class=screen-reader-text for="new-tag-juxtalearn_hub_sb"><?php
		        echo __('Stumbling Blocks', self::LOC_DOMAIN) ?></label>
		  <div class=taghint ><?php
		        echo __('Add New Stumbling Block', self::LOC_DOMAIN) ?></div>
		  <p><input id=new-tag-juxtalearn_hub_sb name="newtag[juxtalearn_hub_sb]" class="newtag form-input-tip" size="16" autocomplete="off" value="">
		  <input type=button class="button tagadd-cust" value="<?php
		        echo __('Add', self::LOC_DOMAIN) ?>"></p>
	    </div>
			<input type=hidden id=custom-sb-meta-box-type value=check />
			<div id=juxtalearn_hub_sb_custom >
		<?php
			$nm = '__tax_input[juxtalearn_hub_sb][]';
			$term_list = wp_get_post_terms($post->ID, 'juxtalearn_hub_sb',
				array('fields'=>'names'));
			foreach ($term_list as $tm):
				?><label><input type=checkbox checked name="<?php echo $nm ?>"
					value="<?php echo $tm ?>"><?php echo $tm ?></label> <?php
			endforeach;
		?></div></div><?php
		endif;
    }

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
				echo __( 'Empty', self::LOC_DOMAIN );
			else
				printf( '<a href="?post_type=student_problem&trickytopic_id=%s">%s</a>',
					$trickytopic_id, ucwords($trickytopic) );
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
		// IMPORTANT: call the parent method.
		$b_continue = parent::save_post($post_id);

		if (!$b_continue) return;

		if (isset($_POST['tax_input'])){
			wp_set_post_terms( $_POST['juxtalearn_hub_trickytopic_id'], $_POST['tax_input']['juxtalearn_hub_sb'], 'juxtalearn_hub_sb', true ); 
		}
	} // END public function save_post($post_id)

} // END class Post_Type_Template