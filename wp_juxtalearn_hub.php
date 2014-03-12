<?php
/*
Plugin Name: WP JuxtaLearn Hub
Plugin URI: https://github.com/mhawksey/wp-juxtalearn-hub
Description: TODO
Version: 0.1
Author: Martin Hawksey
Author URI: http://mashe.hawksey.info
License: GPL2

Based on Name: WP Plugin Template
Based on Plugin URI: https://github.com/fyaconiello/wp_plugin_template
Based on Version: 1.0
Based on Author: Francis Yaconiello
Based on Author URI: http://www.yaconiello.com
*/
/*
Copyright 2014  Martin Hawksey  (email : m.hawksey@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('JUXTALEARN_HUB_VERSION', '0.3');
define('JUXTALEARN_HUB_PATH', dirname(__FILE__));
// Handle symbolic links - code portability.
define('JUXTALEARN_HUB_REGISTER_FILE',
  preg_replace('@/Users/[^\/]+/[^\/]+/[^\/]+@', '',    # Mac OS X
    preg_replace('@\/var\/www\/[^\/]+@', '', __FILE__) # Linux
));
define('JUXTALEARN_HUB_URL', plugin_dir_url(JUXTALEARN_HUB_REGISTER_FILE));


if(!class_exists('JuxtaLearn_Hub'))
{
	class JuxtaLearn_Hub
	{
		static $post_types = array(); 
		
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			add_action('init', array(&$this, 'init'));
			
			require_once(sprintf("%s/post-types/class-custom_post_type.php", JUXTALEARN_HUB_PATH));
			// Register custom post types - trickytopic
			require_once(sprintf("%s/post-types/trickytopic.php", JUXTALEARN_HUB_PATH));
			$TrickyTopic_Template = new TrickyTopic_Template();
			// Register custom post types - student_problem
			require_once(sprintf("%s/post-types/student_problem.php", JUXTALEARN_HUB_PATH));
			$Student_Problem_Template = new Student_Problem_Template();
			// Register custom post types - student_problem
			require_once(sprintf("%s/post-types/teaching_activity.php", JUXTALEARN_HUB_PATH));
			$Teaching_Activity_Template = new Teaching_Activity_Template();
			// Register custom post types - location
			require_once(sprintf("%s/post-types/location.php", JUXTALEARN_HUB_PATH));
			$Location_Template = new Location_Template();

			require_once(sprintf("%s/shortcodes/shortcode.php", JUXTALEARN_HUB_PATH));
			
			require_once(sprintf("%s/shortcodes/custom_archive.php", JUXTALEARN_HUB_PATH));
			$custom_archive = new JuxtaLearn_Hub_Shortcode_Custom_Archive();
			require_once(sprintf("%s/shortcodes/example_meta.php", JUXTALEARN_HUB_PATH));
			$example_meta = new JuxtaLearn_Hub_Shortcode_Example_Meta();
			require_once(sprintf("%s/shortcodes/example_map.php", JUXTALEARN_HUB_PATH));
			$example_map = new JuxtaLearn_Hub_Shortcode_Example_Map();
			require_once(sprintf("%s/shortcodes/trickytopic_summary.php", JUXTALEARN_HUB_PATH));
			$trickytopic_summary = new JuxtaLearn_Hub_Shortcode_Tricky_Topic_Summary();
			require_once(sprintf("%s/shortcodes/subject_summary.php", JUXTALEARN_HUB_PATH));
			$subject_summary = new JuxtaLearn_Hub_Shortcode_Subject_Summary();


			// Initialize Pronamics Google Maps distro
			if (!class_exists('Pronamic_Google_Maps_Maps')){
			   require_once(sprintf("%s/lib/pronamic-google-maps/pronamic-google-maps.php", JUXTALEARN_HUB_PATH));
			}
			// Initialize JSON API Distro
			if (!class_exists('JSON_API')){
			   require_once(sprintf("%s/lib/json-api/json-api.php", JUXTALEARN_HUB_PATH));
			}
			if (!class_exists('Facetious')){
				require_once(sprintf("%s/lib/facetious/facetious.php", JUXTALEARN_HUB_PATH));
			}
			
			// Initialize Settings
            require_once(sprintf("%s/settings/settings.php", JUXTALEARN_HUB_PATH));
            $JuxtaLearn_Hub_Settings = new JuxtaLearn_Hub_Settings();
			require_once(sprintf("%s/settings/cache.php", JUXTALEARN_HUB_PATH));
			$JuxtaLearn_Hub_Settings_Cache = new JuxtaLearn_Hub_Settings_Cache();
			
			add_filter('json_api_controllers', array(&$this,'add_hub_controller'));
			add_filter('json_api_hub_controller_path', array(&$this,'set_hub_controller_path'));
			add_action('admin_notices', array(&$this, 'admin_notices'));
		   
		   	add_action('admin_enqueue_scripts', array(&$this, 'enqueue_autocomplete_scripts'));
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_front_scripts') );
		   
			add_filter('query_vars', array(&$this, 'juxtalearn_hub_queryvars') );
			add_action('pre_get_posts', array(&$this, 'juxtalearn_hub_query'), 1);
			
			add_action( 'admin_menu', array(&$this,'my_remove_named_menus'),999 );
			
			add_action('wp_ajax_get_sankey_data', array(&$this, 'get_sankey_data'));
			add_action('wp_ajax_nopriv_get_sankey_data', array(&$this, 'get_sankey_data'));
			
			add_action('wp_ajax_juxtalearn_hub_location_callback', array(&$this, 'ajax_juxtalearn_hub_location_callback') );
			add_action('wp_ajax_juxtalearn_hub_if_location_exists_by_value', array(&$this, 'ajax_juxtalearn_hub_if_location_exists_by_value') );
			add_action('wp_ajax_juxtalearn_hub_tricky_topic_details', array(&$this, 'ajax_juxtalearn_hub_tricky_topic_details') );
			
			add_action( 'wp_head', array(&$this, 'show_current_query') );

		   //$this->include_files();
		} // END public function __construct
		
		    	/**
    	 * hook into WP's init action hook
    	 */
    	public function init()
    	{	
			global $wp_roles;
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
			//You can replace "administrator" with any other role "editor", "author", "contributor" or "subscriber"...
			$wp_roles->roles['editor']['name'] = 'Teacher';
			$wp_roles->role_names['editor'] = 'Teacher';  
			$wp_roles->roles['subscriber']['name'] = 'Student';
			$wp_roles->role_names['subscriber'] = 'Student'; 
			
			// install contry codes/terms
			$countries = get_terms( 'evidence_hub_country', array( 'hide_empty' => false ) );
			// if no terms then lets add our terms
			if( empty( $countries ) ){
				$countries = $this->set_countries();
				foreach( $countries as $country_code => $country_name ){
					if( !term_exists( $country_name, 'juxtalearn_hub_country' ) ){
						wp_insert_term( $country_name, 'juxtalearn_hub_country', array( 'slug' => $country_code ) );
					}
				}
			}
			
			$args = JuxtaLearn_Hub::get_taxonomy_args("Stumbling Block","Stumbling Blocks", "block");
			$args['capabilities'] = array(	'manage_terms' => 'manage_categories',
											'edit_terms' => 'manage_categories',
											'delete_terms' => 'manage_categories',
											'assign_terms' => 'manage_categories'
										);
			register_taxonomy( 'juxtalearn_hub_sb', array('tricky_topic', 'student_problem', 'teaching_activity'), $args );
			
			$args = JuxtaLearn_Hub::get_taxonomy_args("Education Level","Education Levels" ,"education_level");
			register_taxonomy( 'juxtalearn_hub_education_level', array('student_problem', 'teaching_activity'), $args );
			
			$args = JuxtaLearn_Hub::get_taxonomy_args("Subject","Subjects");
			register_taxonomy( 'juxtalearn_hub_subject', array('tricky_topic'), $args );
			
			$args = JuxtaLearn_Hub::get_taxonomy_args("Country", "Countries");
			register_taxonomy( 'juxtalearn_hub_country', array('location', 'student_problem', 'teaching_activity', 'tricky_topic'), $args );
		}
		
		public function do_rewrites(){
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/([^/]+)/([^/]+)/([0-9]+)/?",'index.php?post_type=$matches[4]&trickytopic_id=$matches[2]&juxtalearn_hub_country=$matches[1]&paged=$matches[5]','top');
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/([^/]+)/([^/]+)/?",'index.php?post_type=$matches[4]&trickytopic_id=$matches[2]&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/([^/]+)/?",'index.php?post_type=tricky_topic&&p=$matches[2]&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/trickytopic/page/([0-9]+)/?",'index.php?&post_type[]=tricky_topic&jutalearn_hub_country=$matches[1]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)/trickytopic/?",'index.php?post_type=tricky_topic&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/teaching_activity/page/([0-9]+)/?",'index.php?&post_type[]=teaching_activity&jutalearn_hub_country=$matches[1]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)/teaching_activity/?",'index.php?post_type=teaching_activity&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/education_level/([^/]+)/page/([0-9]+)/?",'index.php?post_type[]=student_problem&post_type[]=teaching_activity&juxtalearn_hub_country=$matches[1]&juxtalearn_education_level=$matches[2]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)/education_level/([^/]+)/?",'index.php?post_type[]=student_problem&post_type[]=teaching_activity&juxtalearn_hub_country=$matches[1]&juxtalearn_hub_education_level=$matches[2]','top');
			
			add_rewrite_rule("^country/([^/]+)/subject/([^/]+)/page/([0-9]+)/?",'index.php?&post_type=trick_ytopic&jutalearn_hub_country=$matches[1]&juxtalearn_hub_subject=$matches[2]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)/subject/([^/]+)/?",'index.php?post_type=tricky_topic&juxtalearn_hub_country=$matches[1]&juxtalearn_hub_subject=$matches[2]','top');
			
			add_rewrite_rule("^country/([^/]+)/student_problem/page/([0-9]+)/?",'index.php?&post_type=student_problem&jutalearn_hub_country=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^country/([^/]+)/student_problem/?",'index.php?post_type=student_problem&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/page/([0-9]+)?",'index.php?post_type[]=tricky_topic&post_type[]=student_problem&post_type[]=teaching_activity&juxtalearn_hub_country=$matches[1]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)?",'index.php?post_type[]=tricky_topic&post_type[]=student_problem&post_type[]=teaching_activity&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&tt_id=$matches[1]&paged=$matches[2]','top');  
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/([^/]+)/?",'index.php?post_type=$matches[3]&trickytopic_id=$matches[1]','top');
			  
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/page/([0-9]+)?",'index.php?post_type=trickytopic&p=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/?",'index.php?post_type=tricky_topic&p=$matches[1]','top');
		}
		/**
    	* Register custom querystring variables.
		*
		* @param array $qvars WP qvars.
		* @return array $qvars.
    	*/
		public function juxtalearn_hub_queryvars( $qvars )
		{
		  $qvars[] = 'trickytopic_id';
		  return $qvars;
		}
		
		/**
    	* Debug function to check wp_query. Add ?q to url to use.
		*
    	*/
		function show_current_query() {
			global $wp_query;
		
			if ( !isset( $_GET['q'] ) )
				return;
			echo '<textarea cols="50" rows="10">';
			print_r( $wp_query );
			echo '</textarea>';
		}
		
		/**
    	* Handle custom querystring for trickytopic_id.
		*
		* @param array $query WP_query.
		* @return array $query.
    	*/
		public function juxtalearn_hub_query($query){
				
			if ( isset($query->query_vars['facetious_post_type'])){
				return;
			}
			
			if ( isset( $query->query_vars['trickytopic_id']) ) {
				$meta_query = array();
				$meta_query[] = array(
									'key' => 'juxtalearn_hub_trickytopic_id',
									'value' => $query->query_vars['trickytopic_id'],
									'compare' => '='
									);
				$query->set( 'meta_query' ,$meta_query);
				return;
			} 
			return;
		}
		
		/**
    	* Register controllers for custom JSON_API end points.
		*
		* @param object $controllers JSON_API.
		* @return object $controllers.
    	*/
		function add_hub_controller($controllers) {
		  $controllers[] = 'hub';
		  return $controllers;
		}
		
		/**
    	* Register controllers define path custom JSON_API end points.
		*
    	*/
		function set_hub_controller_path() {
		  return sprintf("%s/json/hub.php", JUXTALEARN_HUB_PATH);
		}
		
		/**
    	* Remove Pronamic Google Map Library wp-admin menu option.
		*
    	*/
		public function my_remove_named_menus(){
			global $menu;
			foreach ( $menu as $i => $item ) {
				if ( 'pronamic_google_maps' == $item[2] ) {
						unset( $menu[$i] );
						return $item;
				}
	        }
	        return false;
		}
		/**
    	* Handle custom admin notices.
		*
    	*/
		public static function admin_notices() {
			$messages = get_option('juxtalearn_hub_messages', array());
			if (count($messages)) {
				foreach ($messages as $message) { ?>
					<div class="updated">
						<p><?php echo $message; ?></p>
					</div>
				<?php }
				delete_option('juxtalearn_hub_messages');
			}
		}
		
		/**
    	* Handle custom admin notices - push message for display.
		*
		* @param string $message.
    	*/
		public static function add_admin_notice($message) {
			$messages = get_option('juxtalearn_hub_messages', array());
			$messages[] = $message;
			update_option('juxtalearn_hub_messages', $messages);
		}
		/**
    	* function to filter options array used in custom post types.
		*
		* @param array $arr options array.
		* @param string $key.
		* @param string $val.
		* @return array $newArray
    	*/
		public static function filterOptions($arr, $key, $val){
			$newArr = array();
			foreach($arr as $name => $option) {
				if (array_key_exists($key, $option) && $option[$key]==$val){
					$newArr[$name] = $arr[$name];
				}
			}
			return $newArr;
		}
		/**
    	* Load additional CSS/JS to wp_head in wp-admin.
		*
    	*/
		public function enqueue_autocomplete_scripts() {
			global $typenow;
			global $wp_styles;
			$scripts = array( 'jquery', 'post', 'jquery-ui-autocomplete');
  			if ($typenow=='student_problem' || $typenow=='teaching_activity' || $typenow=='tricky_topic') {
				wp_dequeue_style('jquery-ui-smoothness');
				wp_enqueue_style( 'leafletcss', 'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css' );
				wp_enqueue_script( 'leafletjs', 'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js?2' );
				$scripts[] = 'jquery-ui-core';
				$scripts[] = 'jquery-ui-tabs';
			} 
			if ($typenow=='location') {
				$scripts[] = 'pronamic_google_maps_admin';
			}
			wp_enqueue_style( 'juxtalearn-hub-autocomplete', plugins_url( 'css/admin.css' , JUXTALEARN_HUB_REGISTER_FILE ) );
			wp_enqueue_script( 'juxtalearn-hub-autocomplete', plugins_url( 'js/admin.js' , JUXTALEARN_HUB_REGISTER_FILE ), $scripts, '', true );
			wp_register_script( 'd3js', plugins_url( 'lib/map/lib/d3.v3.min.js' , JUXTALEARN_HUB_REGISTER_FILE), array( 'jquery' )  );
			wp_enqueue_script( 'd3js' );
			
			wp_dequeue_script('pronamic_google_maps_admin');
			wp_dequeue_style('pronamic_google_maps_admin');
			
			// Scripts
			wp_register_script(
				'pronamic_google_maps_admin',
				plugins_url( 'lib/pronamic-google-maps/js/admin.js', JUXTALEARN_HUB_REGISTER_FILE ),
				array( 'jquery', 'google-jsapi' )
			);
	
			// Styles
			wp_register_style(
				'pronamic_google_maps_admin',
				plugins_url( 'lib/pronamic-google-maps/css/admin.css', JUXTALEARN_HUB_REGISTER_FILE )
			);
			wp_enqueue_script('pronamic_google_maps_admin');
			wp_enqueue_style('pronamic_google_maps_admin');
		}
		/**
    	* Load additional CSS/JS to wp_head in frontend.
		*
    	*/
		public function enqueue_front_scripts() {
			wp_register_script( 'd3js', plugins_url( 'lib/map/lib/d3.v3.min.js' , JUXTALEARN_HUB_REGISTER_FILE), array( 'jquery' )  );
			wp_enqueue_script( 'd3js' );
			wp_register_style( 'juxtalearn_hub_style', plugins_url( 'css/style.css' , JUXTALEARN_HUB_REGISTER_FILE ) );
			wp_enqueue_style( 'juxtalearn_hub_style');
		}
		/**
    	* Creates an array for custom post type taxonomies.
		*
		* @param string $tax_single.
		* @param string $tax_plural.
		* @return array 
    	*/
		public static function get_taxonomy_args($tax_single, $tax_plural, $custom_slug = false){
			$labels = array(
				'name'                => sprintf( _x( '%s', 'taxonomy general name', 'juxtalearn_hub' ), $tax_plural ),
			    'singular_name'       => sprintf( _x( '%s', 'taxonomy singular name', 'juxtalearn_hub' ), $tax_single ),
			    'search_items'        => sprintf( __( 'Search %s', 'juxtalearn_hub' ), $tax_plural ),
			    'all_items'           => sprintf( __( 'All %s', 'juxtalearn_hub' ), $tax_plural ),
			    'parent_item'         => sprintf( __( 'Parent %s', 'juxtalearn_hub' ), $tax_single ),
			    'parent_item_colon'   => sprintf( __( 'Parent %s:', 'juxtalearn_hub' ), $tax_single ),
			    'edit_item'           => sprintf( __( 'Edit %s', 'juxtalearn_hub' ), $tax_single ),
			    'update_item'         => sprintf( __( 'Update %s', 'juxtalearn_hub' ), $tax_single ),
			    'add_new_item'        => sprintf( __( 'Add New %s', 'juxtalearn_hub' ), $tax_single ),
			    'new_item_name'       => sprintf( __( 'New %s Name', 'juxtalearn_hub' ), $tax_single ),
			    'menu_name'           => sprintf( __( '%s', 'juxtalearn_hub' ), $tax_plural )
				
			);
		
			return array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'query_var'             => true,
				'rewrite'               => array( 'slug' => (!$custom_slug) ? strtolower($tax_single) : $custom_slug),
				'capabilities' 			=> array(
												'manage_terms' => 'activate_plugins',
												'edit_terms' => 'activate_plugins',
												'delete_terms' => 'activate_plugins',
												'assign_terms' => 'activate_plugins'
											),
			);		
		}
		
		/**
    	* Adds evidence_hub prefixed taxonomy terms and custom fields to a post_id.
		*
		* @param string $post_id.
		* @param string $tax_plural.
		* @return array $post
    	*/
		public static function add_meta($post_id, $post = false) {
			if (!$post) {
				$post = array();
			} else {
				$post_id = $post->ID;
			}
			$post['ID'] = $post_id;
			$post['post_type'] = get_post_type($post_id);
			foreach (get_post_custom($post_id) as $key => $value) {
				if (strpos($key, 'juxtalearn_hub') !== 0) continue;
				$key = substr($key, 15);
				$post[$key] = @unserialize($value[0]) ? @unserialize($value[0]) : $value[0];
			}
			$taxonomies = get_object_taxonomies(get_post_type($post_id), 'objects');

			foreach ($taxonomies as $taxonomy_id => $taxonomy) {
				if (strpos($taxonomy_id, 'juxtalearn_hub') !== 0) continue;
				$value = wp_get_object_terms($post_id, $taxonomy_id);
				
				$taxonomy_id = substr($taxonomy_id, 15);
				$taxonomy_slug = $taxonomy_id."_slug";
				$name = array();
				$slug = array();
				foreach ($value as $v){
					$name[] = $v->name;	
					$slug[] = $v->slug;
				}
				$post[$taxonomy_id] = (count($name)<=1) ? implode("",$name) : $name;
				$post[$taxonomy_slug] = (count($slug)<=1) ? implode("",$slug) : $slug;
			}
			return $post;
		}
		
		/**
    	* Adds evidence_hub prefixed taxonomy terms and custom fields array of post ids.
		*
		* @param array $posts passed in using WP get_posts($args = array('fields' => 'ids')).
		* @return array $posts_termed 
    	*/
		public static function add_terms($posts) {
			$posts_termed = array();
			foreach ($posts as $post_id){
				$posts_termed[] = JuxtaLearn_Hub::add_meta($post_id);
			}
			return $posts_termed;
		}
		
		public function get_sankey_data(){
			$country_slug = $_POST[ 'country_slug' ];
			$title = "World";
			$nodes = array();
			$links = array();
			$markers = array();
			$nodesList = array();
			$posttypes = array('student_problem','teaching_activity');
			
			$args = array('post_type' => $posttypes, // my custom post type
			   'posts_per_page' => -1,
			   'post_status' => 'publish',
			   'fields' => 'ids'
			   ); // show all posts);
			if ($country_slug != "World"){
				$args = array_merge($args, array('tax_query' => array(array('taxonomy' => 'juxtalearn_hub_country',
											'field' => 'slug',
											'terms' => $country_slug,))));
				$term = get_term_by('slug', $country_slug, 'juxtalearn_hub_country'); 
				$title = $term->name;
			}
			$posts = JuxtaLearn_Hub::add_terms(get_posts($args));
			
			//$polarities = get_terms('juxtalearn_hub_polarity', 'hide_empty=0');
			$trickytopics = get_posts(array('post_type' => 'tricky_topic', // my custom post type
										   'posts_per_page' => -1,
										   'post_status' => 'publish',
										   'orderby' => 'title',
										   'order' => 'ASC',
										   'fields' => 'ids'));
			
			$sectors = get_terms('juxtalearn_hub_education_level', 'hide_empty=0');
			$sectors[] = (object) array('slug' => '',
							   			'name' => 'No Level');
										
			if ($country_slug != "World"){
				foreach ($posts as $post){
					$markers[] = array("id" => $post['ID'],
									   "name" => get_the_title($post['ID']),
									   "url" => get_permalink($post['ID']),
									   "lat" => get_post_meta($post['location_id'], '_pronamic_google_maps_latitude', true ),
									   "lng" => get_post_meta($post['location_id'], '_pronamic_google_maps_longitude', true ),
									   "sector" => $post['education_level_slug'],
									   "polarity" =>  $post['post_type']);
				}
			}
			
			foreach($trickytopics as $trickytopic){
				$hposts = JuxtaLearn_Hub::filterOptions($posts, 'trickytopic_id', $trickytopic);
				//$hposts_title = get_the_title($trickytopic);
				$base_link = ($country_slug != 'World') ? (site_url().'/country/'.$country_slug) : site_url();
				$subject = wp_get_post_terms($trickytopic, 'juxtalearn_hub_subject');
				if ($subject && empty($nodeList[$subject[0]->slug])){
					$nodes[] = array("name" => $subject[0]->name, "url" => $base_link."/subject/".$subject[0]->slug, "id" => $subject[0]->slug, "type" => "trickytopic");
					$nodeList[$subject[0]->slug] = 1;
					//$tt_link = $base_link . '/trickytopic/'.$trickytopic.'/'.basename(get_permalink($trickytopic));
					//$nodes[] = array("name" => $hposts_title, "url" => $tt_link, "id" => $trickytopic, "type" => "trickytopic" );
					foreach ($posttypes as $posttype){
						$pposts = JuxtaLearn_Hub::filterOptions($hposts, 'post_type', $posttype);
						if (empty($nodeList[$posttype])){
							$nodes[] = array("name" => ucwords(str_replace("_", " ", $posttype)), "url" => $base_link."/".$posttype, "id" => $posttype, "type" => "polarity");
							$nodeList[$posttype] = 1;
						}
						if (count($pposts) > 0) 
							$links[] = array("source" => $subject[0]->name, "target" => ucwords(str_replace("_", " ", $posttype)), "value" => count($pposts));
						foreach($sectors as $sector){
							$sposts = JuxtaLearn_Hub::filterOptions($pposts, 'education_level_slug', $sector->slug);
							if (empty($nodeList[$sector->name])){
								$nodes[] = array("name" => $sector->name, "url" => $base_link."/education_level/".$sector->slug, "id" => $sector->slug, "type" => "sector", );
								$nodeList[$sector->name] = 1;
							}
							if (count($sposts) > 0) 
								$links[] = array("source" => ucwords(str_replace("_", " ", $posttype)), "target" => $sector->name, "value" => count($sposts), "data" => array("url" => "xxx"));		
						}
					}
				}
			}	
			$graph = array('nodes' => $nodes, 'links' => $links, 'title' => $title, 'markers' => $markers);
			print_r(json_encode($graph));
			die();
		}
		
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
	
		public function generate_excerpt($post_id = false) {
			if ($post_id) $post = is_numeric($post_id) ? get_post($post_id) : $post_id;
			else $post = $GLOBALS['post'];
	
			if (!$post) return '';
			if (isset($post->post_excerpt) && !empty($post->post_excerpt)) return $post->post_excerpt;
			if (!isset($post->post_content)) return '';
		
			$content = $raw_content = $post->post_content;
		
			if (!empty($content)) {
				$content = strip_shortcodes($content);
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);
				$content = strip_tags($content);
	
				$excerpt_length = apply_filters('excerpt_length', 55);
				$words = preg_split("/[\n\r\t ]+/", $content, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
				if (count($words) > $excerpt_length) {
					array_pop($words);
					$content = implode(' ', $words);
					$content .= "...";
				} else $content = implode(' ', $words);
			}
		
			return apply_filters('wp_trim_excerpt', $content, $raw_content);
		}
		
		/**
    	* Set country terms.
		*
    	*/
		public function set_countries() {
			$jsonIterator = new RecursiveIteratorIterator(
					 new RecursiveArrayIterator(json_decode(file_get_contents(EVIDENCE_HUB_PATH."/lib/countries.json"), TRUE)),
					 RecursiveIteratorIterator::SELF_FIRST);
			$countries = array();
			foreach ($jsonIterator as $key => $val) {
				if(!is_array($val)) {
					$countries[$key] = $val;
				} 
			}
			return $countries;
		}
		
		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			flush_rewrite_rules();
			update_option( 'Pronamic_Google_maps', array( 'active' => array( 'location' => true, 'student_problem' => true, 'teaching_activity' => true, 'location' => true, 'policy' => true  ) ) );
			Juxtalearn_Hub_Shortcode::activate();
			// Do nothing
		} // END public static function activate
	
		/**
		 * Deactivate the plugin
		 */		
		public static function deactivate()
		{
			Juxtalearn_Hub_Shortcode::deactivate();
			// Do nothing
		} // END public static function deactivate
	} // END class JuxtaLearn_Hub
} // END if(!class_exists('JuxtaLearn_Hub'))

if(class_exists('JuxtaLearn_Hub'))
{
	// Installation and uninstallation hooks
	register_activation_hook(JUXTALEARN_HUB_REGISTER_FILE, array('JuxtaLearn_Hub', 'activate'));
	register_deactivation_hook(JUXTALEARN_HUB_REGISTER_FILE, array('JuxtaLearn_Hub', 'deactivate'));

	// instantiate the plugin class
	$wp_plugin_template = new JuxtaLearn_Hub();
	
    
}
?>