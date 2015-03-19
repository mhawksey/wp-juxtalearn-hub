<?php
/**
 * Construct a custom post type
 *
 *
 * @package Juxtalearn_Hub
 * @subpackage Juxtalearn_Hub_CustomPostType
 */
class Juxtalearn_Hub_CustomPostType {

	const LOC_DOMAIN = JuxtaLearn_Hub::LOC_DOMAIN;

	public $post_type = "custom_post_type";
	public $archive_slug = false; // use pluralized string if you want an archive page
	public $singular = "Item";
	public $plural = "Items";
	
	public $options = array();

	/**
	* The Constructor
	*
	*/
	public function __construct( $as_wp_plugin = TRUE ) {

		if (!$as_wp_plugin) {
			return;
		}

		// register actions
		add_action('init', array(&$this, 'init'));
		add_action('init', array(&$this, 'set_options'));
		add_action('admin_init', array(&$this, 'admin_init'));
		// register custom columns in wp-admin
		add_action('manage_edit-'.$this->post_type.'_columns', array(&$this, 'columns'));
		add_action('manage_'.$this->post_type.'_posts_custom_column', array(&$this, 'column'),10 ,2);
		// add filters
		add_filter('post_type_link', array(&$this, 'custom_post_type_link'), 1, 3);
		add_action('edit_form_after_title', array(&$this, 'foo_move_deck'),999);

		add_filter( 'the_excerpt', array(&$this, 'search_excerpt') );
		add_filter( 'the_content', array(&$this, 'search_excerpt') );

		// push post types for caching
		Juxtalearn_Hub::$post_types[] = $this->post_type;

	} // END public function __construct()

	/**
	* hook into WP's init action hook.
	*
	*/
	public function init() {
		// Initialize Post Type
		$this->create_post_type();
		// save post action
		add_action('save_post', array(&$this, 'save_post'));

	} // END public function init()
	public function foo_move_deck(){
	}
	
	/**
	* Register custom post type.
	*
	*/
	public function create_post_type(){
		// no action
	}
	
	/**
	* Register custom post type fields.
	*
	*/
	public function set_options(){
		// no action	
	}
	
	/**
	* Save the metaboxes for this custom post type.
	*
	*/
	public function save_post($post_id)	{
		$b_continue = TRUE;

		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if (get_post_type($post_id) != $this->post_type) return !$b_continue;
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return !$b_continue;
		
		if (isset($_POST['juxtalearn_hub_nonce']) &&
			!wp_verify_nonce($_POST['juxtalearn_hub_nonce'], plugin_basename(__FILE__))) {
			return !$b_continue;
		}
		
		if (!current_user_can('edit_post', $post_id)) return !$b_continue;

		foreach($this->options as $name => $option)	{
			// Update the post's meta field
			$field_name = "juxtalearn_hub_$name";
			if (isset($_POST[$field_name])){
				if ($option['save_as'] == 'term'){
					$term = term_exists($_REQUEST[$field_name], $field_name);
					if ($term !== 0 && $term !== NULL){
						wp_set_object_terms( $post_id, $_POST[$field_name], $field_name);
					}
				} else {
					update_post_meta($post_id, $field_name, $_POST[$field_name]);
				}
			}
		}

		//NDF:
		$tax = 'juxtalearn_hub_sb';
		$pin = '__tax_input';
		if (isset($_POST[$pin]) && isset($_POST[$pin][$tax])) {
			$terms = $_POST[$pin][$tax];
			@header("X-Jxl-Hub-tax-input-$tax: ". json_encode($terms));
			#$_POST['tax_input'][$fname] = implode(',', $terms);
			$ret = wp_set_post_terms($post_id, $terms, $tax);
		}

		@header("X-Jxl-Hub-save-post: $post_id; tid=". json_encode($ret)); #. json_encode($_POST));

		return $b_continue;
	} // END public function save_post($post_id)
	
	/**
	* Add action to add metaboxes.
	*
	*/
	public function admin_init() {			
		add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
	} // END public function admin_init()
	
	/**
	* Register custom fields box in wp-admin.
	*
	*/
	public function add_meta_boxes() {
		// no action
	}
	
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

			/**
	 * called off of the add meta box
	 */		
	public function add_inner_meta_boxes_tax_tool($post)
	{		
		// Render the job order metabox
		$sub_options = JuxtaLearn_Hub::filterOptions($this->options, 'position', 'tax_tool');
		include(sprintf("%s/taxonomy-tool.php", dirname(__FILE__)));			
	} // END public function add_inner_meta_boxes($post)
	
	/**
	* function to register custom slug hypothesis/%hypothesis_slug%/%post_id%/.
	*
	* @return string
	*/
	public function custom_post_type_link($post_link, $post = 0, $leavename = false) {			
		// no action
		return $post_link;
	}
	
	/**
	* Add hypothesis column to wp-admin.
	*
	* @params array
	* @return array
	*/
	public function columns($columns) {
		return $columns;
	}
	
	/**
	* Sets text and link for custom columns.
	*
	* @return NULL
	*/	
	public function column($column, $post_id) {
		
	}

	/**
	* Filter to fix formatting of search results.
	http://stackoverflow.com/questions/19755876/wordpress-customize-search-results-for-custom-post-types
	*/
	public function search_excerpt( $content ) {
		global $post;

		if ( is_search() ) {
			$sc = new JuxtaLearn_Hub_Shortcode_Example_Meta();

			$content =
			    '<div class=entry-type >'.
			    ucwords(str_replace('_', ' ', $post->post_type)) .'</div>'.
				$post->post_content .
				$sc->meta_bar((array) $post, 'sb, subject, country', TRUE);

			// maybe add a read more link
			// also, you can use global $post to access the current search result
		}
		return $content;
	}


	/**NDF: Stumbling Block tags widget [#5] [#10].
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

}