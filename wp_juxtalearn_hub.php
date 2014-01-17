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
Copyright 2012  Francis Yaconiello  (email : francis@yaconiello.com)

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
define('JUXTALEARN_HUB_URL', plugin_dir_url(preg_replace('@\/var\/www\/[^\/]+@', '', __FILE__)));
define('JUXTALEARN_HUB_REGISTER_FILE', preg_replace('@\/var\/www\/[^\/]+@', '', __FILE__));



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
			
			require_once(sprintf("%s/shortcodes/shortcode.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/example_summary.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/example_meta.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/location_meta.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/policy_meta.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/example_map.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/trickytopic_bars.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/trickytopic_archive.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/trickytopic_sankey.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/example_geomap.php", JUXTALEARN_HUB_PATH));
			require_once(sprintf("%s/shortcodes/policy_geomap.php", JUXTALEARN_HUB_PATH));
			
			// Register custom post types - trickytopic
			require_once(sprintf("%s/post-types/trickytopic.php", JUXTALEARN_HUB_PATH));
			$TrickyTopic_Template = new TrickyTopic_Template();
			
			// Register custom post types - example
			require_once(sprintf("%s/post-types/example.php", JUXTALEARN_HUB_PATH));
			$Example_Template = new Example_Template();
			
			/* location depreciated
			// Register custom post types - location
			require_once(sprintf("%s/post-types/location.php", JUXTALEARN_HUB_PATH));
			$Location_Template = new Location_Template();
			*/
			
			// Register custom post types - location
			require_once(sprintf("%s/post-types/location.php", JUXTALEARN_HUB_PATH));
			$Location_Template = new Location_Template();
					
			//require_once(sprintf("%s/post-types/policy.php", JUXTALEARN_HUB_PATH));
			//$Policy_Template = new Policy_Template();
			
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

		   //$this->include_files();
		} // END public function __construct
		
		    	/**
    	 * hook into WP's init action hook
    	 */
    	public function init()
    	{	
			
			add_rewrite_rule("^country/([^/]+)/policy/sector/([^/]+)/page/([0-9]+)?",'index.php?post_type=policy&juxtalearn_hub_country=$matches[1]&sector=$matches[2]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)/policy/sector/([^/]+)?",'index.php?post_type=policy&juxtalearn_hub_country=$matches[1]&sector=$matches[2]','top');
			
			add_rewrite_rule("^country/([^/]+)/policy/page/([0-9]+)?",'index.php?post_type=policy&juxtalearn_hub_country=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^country/([^/]+)/policy([^/]+)?",'index.php?post_type=policy&juxtalearn_hub_country=$matches[1]','top');
			
			add_rewrite_rule("^country/([^/]+)/example/polarity/([^/]+)/sector/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&polarity=$matches[2]&sector=$matches[3]&paged=$matches[4]','top');
			add_rewrite_rule("^country/([^/]+)/example/polarity/([^/]+)/sector/([^/]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&polarity=$matches[2]&sector=$matches[3]','top');
			
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/[^/]+/polarity/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&tt_id=$matches[2]&polarity=$matches[3]&paged=$matches[4]','top');
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/[^/]+/polarity/([^/]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&tt_id=$matches[2]&polarity=$matches[3]','top');			
			add_rewrite_rule("^country/([^/]+)/trickytopic/([0-9]+)/.*?",'index.php?post_type=trickytopic&p=$matches[2]','top');
			
			add_rewrite_rule("^country/([^/]+)/example/(polarity|sector)/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&$matches[2]=$matches[3]&paged=$matches[4]','top');
			add_rewrite_rule("^country/([^/]+)/example/(polarity|sector)/([^/]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&$matches[2]=$matches[3]','top');
			
			add_rewrite_rule("^country/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]&paged=$matches[3]','top');
			add_rewrite_rule("^country/([^/]+)?",'index.php?post_type=example&juxtalearn_hub_country=$matches[1]','top');
					
					
			add_rewrite_rule("^example/polarity/([^/]+)/sector/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&polarity=$matches[1]&sector=$matches[2]&paged=$matches[3]','top');
			add_rewrite_rule("^example/polarity/([^/]+)/sector/([^/]+)?",'index.php?post_type=example&polarity=$matches[1]&sector=$matches[2]','top');
			
			add_rewrite_rule("^example/(polarity|sector)/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&$matches[1]=$matches[2]&paged=$matches[3]','top');
			add_rewrite_rule("^example/(polarity|sector)/([^/]+)?",'index.php?post_type=example&$matches[1]=$matches[2]','top');
			
			add_rewrite_rule("^policy/sector/([^/]+)/page/([0-9]+)?",'index.php?post_type=policy&sector=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^policy/sector/([^/]+)?",'index.php?post_type=policy&sector=$matches[1]','top');
	
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/polarity/([^/]+)/sector/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&tt_id=$matches[1]&polarity=$matches[3]&sector=$matches[4]&paged=$matches[5]','top');
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/polarity/([^/]+)/sector/([^/]+)?",'index.php?post_type=example&tt_id=$matches[1]&polarity=$matches[3]&sector=$matches[4]','top');
			
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/(polarity|sector)/([^/]+)/page/([0-9]+)?",'index.php?post_type=example&tt_id=$matches[1]&$matches[3]=$matches[4]&paged=$matches[5]','top');
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/(polarity|sector)/([^/]+)?",'index.php?post_type=example&tt_id=$matches[1]&$matches[3]=$matches[4]','top');
			
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/page/([0-9]+)?",'index.php?post_type=example&tt_id=$matches[1]&paged=$matches[2]','top');  
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/example/?",'index.php?post_type=example&tt_id=$matches[1]','top');
			  
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/page/([0-9]+)?",'index.php?post_type=trickytopic&p=$matches[1]&paged=$matches[2]','top');
			add_rewrite_rule("^trickytopic/([0-9]+)/([^/]+)/?",'index.php?post_type=trickytopic&p=$matches[1]','top');
		}
		
		public function juxtalearn_hub_queryvars( $qvars )
		{
		  $qvars[] = 'tt_id';
		  $qvars[] = 'polarity';
		  $qvars[] = 'sector';
		  return $qvars;
		}
		
		public function juxtalearn_hub_query($query){
			
			if ( is_admin() || is_search() || isset($query->query_vars['facetious_post_type']))
				return;
			
			if ( is_post_type_archive( 'trickytopic' ) && !isset( $query->query_vars['tt_id']) ) {
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
				$query->set( 'posts_per_page', -1 );
				return;
			} 
			
			if( isset( $query->query_vars['tt_id'] ) || (is_post_type_archive( 'example' ) && $query->is_main_query())) {
				$meta_query = array();
				$tax_query = array('relation' => 'AND');
				$querystr = $query->query_vars;
				
				$query->init();
				if (isset( $querystr['tt_id'] )){
					$meta_query[] = array(
									'key' => 'juxtalearn_hub_trickytopic_id',
									'value' => $querystr['tt_id'],
									'compare' => '='
									);
									
				}
				if (isset( $querystr['polarity'] ))
					$tax_query[] = array(
									'taxonomy' => 'juxtalearn_hub_polarity',
									'field' => 'slug',
									'terms' => $querystr['polarity'],
									);
				if (isset( $querystr['sector'] ))
					$tax_query[] = array(
									'taxonomy' => 'juxtalearn_hub_sector',
									'field' => 'slug',
									'terms' => $querystr['sector'],
									);
									
				if (isset( $querystr['juxtalearn_hub_country'] ))
					$tax_query[] = array(
									'taxonomy' => 'juxtalearn_hub_country',
									'field' => 'slug',
									'terms' => $querystr['juxtalearn_hub_country'],
									);									
				
				$query->set( 'post_type', $querystr['post_type']);	
				$query->set( 'meta_query' ,$meta_query);
				$query->set( 'tax_query' ,$tax_query);
				$query->set( 'post_status', 'publish');
				$query->parse_query();	
				return;
			}	
		}
		
		function add_hub_controller($controllers) {
		  $controllers[] = 'hub';
		  return $controllers;
		}
		
		
		function set_hub_controller_path() {
		  return sprintf("%s/json/hub.php", JUXTALEARN_HUB_PATH);
		}
		
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
		
		public static function add_admin_notice($message) {
			$messages = get_option('juxtalearn_hub_messages', array());
			$messages[] = $message;
			update_option('juxtalearn_hub_messages', $messages);
		}
		
		public static function filterOptions($arr, $key, $val){
			$newArr = array();
			foreach($arr as $name => $option) {
				if (array_key_exists($key, $option) && $option[$key]==$val){
					$newArr[$name] = $arr[$name];
				}
			}
			return $newArr;
		}
		
		public function enqueue_autocomplete_scripts() {
			global $typenow;
			global $wp_styles;
			$scripts = array( 'jquery', 'post', 'jquery-ui-autocomplete');
  			if ($typenow=='example') {
	  			//wp_enqueue_script( 'pronamic_google_maps_site');
				/*
				wp_enqueue_style( 'leafletcss', 'http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.css' );
				wp_enqueue_style( 'leafletcss-ie8', "http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.ie.css", array( 'leafletcss' )  );
    			$wp_styles->add_data( 'leafletcss-ie8', 'conditional', 'IE 8' );
				wp_enqueue_script( 'leafletjs', 'http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.js' );
				*/
				
				wp_enqueue_style( 'leafletcss', 'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css' );
				//wp_enqueue_style( 'leafletcss-ie8', "http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.ie.css", array( 'leafletcss' )  );
    			//$wp_styles->add_data( 'leafletcss-ie8', 'conditional', 'IE 8' );
				wp_enqueue_script( 'leafletjs', 'http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js?2' );
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
		
		public function enqueue_front_scripts() {
			wp_register_script( 'd3js', plugins_url( 'lib/map/lib/d3.v3.min.js' , JUXTALEARN_HUB_REGISTER_FILE), array( 'jquery' )  );
			wp_enqueue_script( 'd3js' );
			wp_register_style( 'juxtalearn_hub_style', plugins_url( 'css/style.css' , JUXTALEARN_HUB_REGISTER_FILE ) );
			wp_enqueue_style( 'juxtalearn_hub_style');
		}
		
		public static function get_taxonomy_args($tax_single, $tax_plural){
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
				'show_admin_column'     => false,
				'query_var'             => true,
				'rewrite'               => array( 'slug' => strtolower($tax_single)),
			);		
		}

		public static function add_meta($post_id, $post = false) {
			if (!$post) {
				$post = array();
			} else {
				$post_id = $post->ID;
			}
			$post['ID'] = $post_id;
			foreach (get_post_custom($post_id) as $key => $value) {
				if (strpos($key, 'juxtalearn_hub') !== 0) continue;
				$key = substr($key, 13);
				$post[$key] = @unserialize($value[0]) ? @unserialize($value[0]) : $value[0];
			}
			$taxonomies = get_object_taxonomies(get_post_type($post_id), 'objects');

			foreach ($taxonomies as $taxonomy_id => $taxonomy) {
				if (strpos($taxonomy_id, 'juxtalearn_hub') !== 0) continue;
				$value = wp_get_object_terms($post_id, $taxonomy_id);
				
				$taxonomy_id = substr($taxonomy_id, 13);
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
			
			$args = array('post_type' => 'example', // my custom post type
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
			
			$polarities = get_terms('juxtalearn_hub_polarity', 'hide_empty=0');
			$trickytopics = get_posts(array('post_type' => 'trickytopic', // my custom post type
										   'posts_per_page' => -1,
										   'post_status' => 'publish',
										   'orderby' => 'title',
										   'order' => 'ASC',
										   'fields' => 'ids'));
			$sectors = get_terms('juxtalearn_hub_sector', 'hide_empty=0');
			if ($country_slug != "World"){
				foreach ($posts as $post){
					$markers[] = array("id" => $post['ID'],
									   "name" => get_the_title($post['ID']),
									   "url" => get_permalink($post['ID']),
									   "lat" => get_post_meta($post['ID'], '_pronamic_google_maps_latitude', true ),
									   "lng" => get_post_meta($post['ID'], '_pronamic_google_maps_longitude', true ),
									   "sector" => $post['sector_slug'],
									   "polarity" =>  $post['polarity_slug']);
				}
			}
			
			foreach($trickytopics as $trickytopic){
				$hposts = JuxtaLearn_Hub::filterOptions($posts, 'trickytopic_id', $trickytopic);
				$hposts_title = get_the_title($trickytopic);
				$base_link = ($country_slug != 'World') ? (site_url().'/country/'.$country_slug) : site_url();
				$tt_link = $base_link . '/trickytopic/'.$trickytopic.'/'.basename(get_permalink($trickytopic));
				$nodes[] = array("name" => $hposts_title, "url" => $tt_link, "id" => $trickytopic, "type" => "trickytopic" );
				foreach ($polarities as $polarity){
					$pposts = JuxtaLearn_Hub::filterOptions($hposts, 'polarity_slug', $polarity->slug);
					if (empty($nodeList[$polarity->name])){
						$nodes[] = array("name" => $polarity->name, "url" => $base_link."/example/polarity/".$polarity->slug, "id" => $polarity->slug, "type" => "polarity", "fill" => json_decode($polarity->description)->fill);
						$nodeList[$polarity->name] = 1;
					}
					if (count($pposts) > 0) 
						$links[] = array("source" => $hposts_title, "target" => $polarity->name, "value" => count($pposts));
					foreach($sectors as $sector){
						$sposts = JuxtaLearn_Hub::filterOptions($pposts, 'sector_slug', $sector->slug);
						if (empty($nodeList[$sector->name])){
							$nodes[] = array("name" => $sector->name, "url" => $base_link."/example/sector/".$sector->slug, "id" => $sector->slug, "type" => "sector", "fill" => json_decode($sector->description)->fill);
							$nodeList[$sector->name] = 1;
						}
						if (count($sposts) > 0) 
							$links[] = array("source" => $polarity->name, "target" => $sector->name, "value" => count($sposts), "data" => array("url" => "xxx"));		
					}
				}
			}	
			$graph = array('nodes' => $nodes, 'links' => $links, 'title' => $title, 'markers' => $markers);
			print_r(json_encode($graph));
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
	
		
		public static function get_select_quick_edit($options, $column_name){
			?><fieldset class="inline-edit-col-right">
                  <div class="inline-edit-group">
                     <label>
                     <select
							name="<?php echo $column_name; ?>"
							id="<?php echo $column_name; ?>"
						>
							<option value=""></option>
                        <span class="title"><?php echo $option['label'];?></span>
                        <?php foreach ($option['options'] as $select) { 
							echo "<option value='" . $select->slug . "'>" . $select->name . "</option>\n";
						}
						?>
                     </label>
                  </div>
               </fieldset><?php
		}
		

		
		
		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			flush_rewrite_rules();
			update_option( 'Pronamic_Google_maps', array( 'active' => array( 'location' => true, 'example' => true, 'location' => true, 'policy' => true  ) ) );
			// Do nothing
		} // END public static function activate
	
		/**
		 * Deactivate the plugin
		 */		
		public static function deactivate()
		{
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