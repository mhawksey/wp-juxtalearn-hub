<?php
/*
Controller name: JuxtaLearn Hub
Controller description: JuxtaLearn Hub APIs
*/

class JSON_API_Hub_Controller {
	public function hello_world() {
		return array(
		  "message" => "JSON API for OER JuxtaLearn Hub"
		);
	}
	public function get_trickytopic() {
		global $json_api;
		$include_example = $json_api->query->include_example;
		$trickytopic_id = $json_api->query->trickytopic_id;
		return $this->get_all_type(array('type' => 'tricky_topic', 'include_example' => $include_example, 'trickytopic_id' => $trickytopic_id));
	}
	
	public function get_juxtalearn() {
		return $this->get_all_type(array('type' => 'example'));
	}
	
	public function get_locations() {
		return $this->get_all_type(array('type' => 'location'));
	}
	
	public function get_all_type($args = array()){
		global $json_api;
		$type = (isset($args['type'])) ? $args['type'] : $json_api->query->type;
		$output = array();
		$example = array();
		$uri = (!isset($args['ignore_uri'])) ? '' : $_SERVER['REQUEST_URI'];
		$url = parse_url($uri);
		$defaults = array(
					  'ignore_sticky_posts' => true,
					  'fields' => 'ids'
					);
		$query = wp_parse_args($url['query']);
		$query['post_type'] = explode(",",$type);
		if ($json_api->query->count) {
		  $query['posts_per_page'] = $json_api->query->count;
		}
		if ($json_api->query->paged) {
			$query['paged'] = $json_api->query->paged;
		}
		if (isset($args['trickytopic_id']))
			$query['p'] = $args['trickytopic_id']; 
		unset($query['json']);
		unset($query['post_status']);
		$query = array_merge($defaults, $query, $args);
		$the_query = new WP_Query($query);
		foreach ($the_query->posts as $post_id){
				$p = JuxtaLearn_Hub::add_meta($post_id);
				$geo = array();
				if ($p->post_type != 'tricky_topic'){
					if ($p->post_type === 'example'){
						if (!$lat = get_post_meta($post_id, '_pronamic_google_maps_latitude', true ))
							$lat = get_post_meta($p['location_id'], '_pronamic_google_maps_latitude', true );
							
						if (!$long = get_post_meta($post_id, '_pronamic_google_maps_longitude', true ))
							$long = get_post_meta($p['location_id'], '_pronamic_google_maps_longitude', true );
					} else {
						$long = get_post_meta($post_id, '_pronamic_google_maps_longitude', true );
						$lat = get_post_meta($post_id, '_pronamic_google_maps_latitude', true );	
					}
					$geo = array('geometry' => array("type" => "Point", "coordinates" => array((float)$long, (float)$lat)));	
				}
				if (isset($args['include_example'])){
					$example = $this->get_all_type(array('type'=> 'example', 'posts_per_page' => -1, 'meta_query' => array(array('key' => 'juxtalearn_hub_trickytopic_id', 'value' => $post_id+"", 'compare' => '=')),  'exclude_post_result' => 1));
				}
				$output[get_post_type($post_id)][] = array_merge($p,
							array('title' => get_the_title($post_id),
								  'description' => apply_filters('the_content',get_post_field('post_content', $post_id)),
								  'url' => get_permalink($post_id),
								  ), $geo, $example);
		}
		wp_reset_query();
		if (isset($args['exclude_post_result'])){
			return array($type => $output);
		}
		return array_merge($this->posts_result($the_query), $output);
	}
	
	public function get_geojson($args=array()){
		global $json_api;
		if ($json_api->query->count) {
		  $args['posts_per_page'] = $json_api->query->count;
		}
		if ($json_api->query->type) {
			$args['type'] = $json_api->query->type;
		}
		if ($json_api->query->paged) {
			$args['paged'] = $json_api->query->paged;
		}
		$posts = $this->get_all_type($args);
		$geoJSON = array();
		if (!empty($posts) && !empty($args['type'])){
			foreach (explode(",", $args['type']) as $type){ 
				foreach ($posts[$type] as $post){
					$property = array("type" => $type,
									  "name" => $post['title'],
									  "desc" => JuxtaLearn_Hub::generate_excerpt($post['ID']),
									  "url" => $post['url'],
									  // Defensive programming - use isset().
									  "sector" => isset($post['sector_slug']) ? $post['sector_slug'] : NULL,
									  );
					if ($type=='example'){
						$property = array_merge($property, array("polarity" => isset($post['polarity_slug']) ? $post['polarity_slug'] : NULL,
									  "location" => (($post['location_id'] > 0) ? get_the_title($post['location_id']) : "N/A"),
									  "trickytopic_id" => $post['trickytopic_id'],
									  "trickytopic" => (($post['trickytopic_id'] > 0) ? get_the_title($post['trickytopic_id']) : "Unassigned")));
					} elseif ($type=='policy'){
						$property = array_merge($property, array("locale" => isset($post['locale_slug']) ? $post['locale_slug'] : NULL));	
					}
									  
					$geoJSON[] = array("type" => "Feature",
									   "properties" => $property,
									   "geometry" => isset($post['geometry']) ? $post['geometry'] : NULL);						
				}
			}
		}	

		return array('count' => $posts['count'],
					 'count_total' => $posts['count_total'],
					 'pages' => $posts['pages'],
					 'geoJSON' => $geoJSON);
	}
	
	protected function posts_result($query) {
		return array(
		  'count' => (int) $query->post_count,
		  'count_total' => (int) $query->found_posts,
		  'pages' => $query->max_num_pages
		);
	  }
	
	public function get_reingold_tilford() {
		$args = array('post_type' => 'tricky_topic', // my custom post type
    				   'posts_per_page' => -1); // show all posts);
		
		$tree = $this->get_all_post_by_query(array('post_type' => 'tricky_topic',
												   'orderby' => 'title', 
												   'order' => 'ASC'
												   ), 'TrickyTopic', array('Subject' => 'juxtalearn_hub_subject'));
		
		
		foreach ($tree['children'] as $key => $val){
			 $tree['children'][$key]['children'][] = $this->get_all_post_by_query(array('post_type' => 'example', 
																	'meta_query' => array(
																		array(
																			'key' => 'juxtalearn_hub_trickytopic_id',
																			'value' => $tree['children'][$key]['id'],
																			'compare' => '='
																		)),
																	 'tax_query' => array(
																		array(
																			'taxonomy' => 'juxtalearn_hub_polarity',
																			'field' => 'slug',
																			'terms' => 'pos'
																		)
																	)), 'Positive +ve');
																	
			 $tree['children'][$key]['children'][] = $this->get_all_post_by_query(array('post_type' => 'example', 
														'meta_query' => array(
															array(
																'key' => 'juxtalearn_hub_trickytopic_id',
																'value' => $tree['children'][$key]['id'],
																'compare' => '='
															)),
														'tax_query' => array(
																		array(
																			'taxonomy' => 'juxtalearn_hub_polarity',
																			'field' => 'slug',
																			'terms' => 'neg'
																		)
														)), 'Negative -ve', array('Polarity' => 'juxtalearn_hub_polarity'));
		}

		return $tree;
	}
	
	protected function get_all_post_by_query($args,  $name, $postmeta){
		$args = array_merge($args, array('posts_per_page' => -1)); // show all posts);
		$the_query = new WP_Query($args);
		$children = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				
				$meta = array('name' => get_the_title(),
							  'id' => get_the_ID(),
							  'link' => get_permalink(),
							  'excerpt' => get_the_excerpt());
							  //'size' => strlen(get_the_content()) );

				foreach($postmeta as $key => $val){
					$meta[$key] = get_post_meta( get_the_ID(), $val, true  );
				}			
				$children[] = $meta;
			}
		}
		
		//if ($name){
			return array(	
					"name" => $name,
					"size" => count($children),
					"children" => $children
				  );
		/*} else {
			return $children;
		}*/
	}
}
?>