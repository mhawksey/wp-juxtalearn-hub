<?php

new JuxtaLearn_Hub_Shortcode_Example_Map();
class JuxtaLearn_Hub_Shortcode_Example_Map extends JuxtaLearn_Hub_Shortcode {
	var $shortcode = 'example_map';
	var $defaults = array(
		'post_id' => false,
		'post_ids' => false,
		'title' => false,
		'no_example_message' => "There is no example map yet to display",
		'link_post' => true,
		'link_sessions' => true,
		'title_tag' => 'h3',
	);

	

	static $post_types_with_example = array();
	
	
	function prep_options() {
		// Turn csv into array
		if (!is_array($this->options['post_ids'])) $this->options['post_ids'] = array();
		if (!empty($this->options['post_ids'])) $this->options['post_ids'] = explode(',', $this->options['post_ids']);

		// add post_id to post_ids and get rid of it
		if ($this->options['post_id']) $this->options['post_ids'] = array_merge($this->options['post_ids'], explode(',', $this->options['post_id']));
		unset($this->options['post_id']);
		
		// fallback to current post if nothing specified
		if (empty($this->options['post_ids']) && $GLOBALS['post']->ID) $this->options['post_ids'] = array($GLOBALS['post']->ID);
		
		// unique list
		$this->options['post_ids'] = array_unique($this->options['post_ids']);
	}

	function content() {
		ob_start();
		extract($this->options);
		$errors = array();	
		?>
        <script type="application/javascript">
				/* <![CDATA[ */
		var MyAjax = {
			pluginurl: getPath('<?php echo JUXTALEARN_HUB_URL; ?>'),
			ajaxurl: getPath('<?php echo admin_url();?>admin-ajax.php')
		};
		function getPath(url) {
			var a = document.createElement('a');
			a.href = url;
			return a.pathname.charAt(0) != '/' ? '/' + a.pathname : a.pathname;
		}
		/* ]]> */
		<?php 
		$handle = fopen(JUXTALEARN_HUB_PATH.'/lib/map/data/world-country-names.csv', 'r'); 
		$country_ids = array();
		if ($handle) 
		{ 
			set_time_limit(0); 		
			//loop through one row at a time 
			while (($rows = fgetcsv($handle, 256, ';')) !== FALSE) 
			{ 
				$country_ids[$rows[2]] = $rows[0];
			} 
			fclose($handle); 
		} 
		$posttypes = array('student_problem','teaching_activity');
		$args = array('post_type' => $posttypes, // my custom post type
    				   'posts_per_page' => -1,
					   'post_status' => 'publish',
					   'fields' => 'ids'); // show all posts);
		
		$year = array();
		
		$posts = JuxtaLearn_Hub::add_terms(get_posts($args));
		
		$countries = get_terms('juxtalearn_hub_country', array('post_types' => $posttypes ));
		//$polarities = get_terms('juxtalearn_hub_polarity');
		$args['post_type'] = 'tricky_topic';
		$trickytopic = get_posts($args);

		
		$graph = array();
		$nodes = array();
		$links = array();
		$totals = array();
		


		$world = array("name"=>"World",
					   "id" => 900,
					   "positive" => 0,
					   "negative" => 0);
		foreach ($countries as $country){
			$cposts = JuxtaLearn_Hub::filterOptions($posts, 'country_slug' , $country->slug);
			$totals = array();
			foreach ($posttypes  as $posttype){
				$pposts = JuxtaLearn_Hub::filterOptions($cposts, 'post_type', $posttype);
				$totals[$posttype] = count($pposts);
			}
			$year[] = array("name" => $country->name,
							"slug" => $country->slug,
							"id" => $country_ids[$country->slug],
							"positive" => $totals['student_problem'],
							"negative" => $totals['teaching_activity']);
			$world['positive'] = $world['positive'] + $totals['student_problem'];
			$world['negative'] = $world['negative'] + $totals['teaching_activity'];		
		}
		$year[] = $world;
		$data = array("2013" => $year);
		print_r("var data = ".json_encode($data).";")
		
		?>
		</script>
        <script src="<?php echo plugins_url( 'lib/map/lib/queue.v1.min.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        <script src="<?php echo plugins_url( 'lib/map/lib/topojson.v1.min.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        <script src="<?php echo plugins_url( 'lib/map/lib/colorbrewer.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        <script src="<?php echo plugins_url( 'lib/map/lib/mootools-core-1.4.5.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        <script src="<?php echo plugins_url( 'lib/map/lib/mootools-more-1.4.0.1.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
		
        <script src="<?php echo plugins_url( 'lib/map/src/control.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        <script src="<?php echo plugins_url( 'js/sankey.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
        
        <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'lib/map/css/skeleton.css' , JUXTALEARN_HUB_REGISTER_FILE )?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'lib/map/css/styles.css' , JUXTALEARN_HUB_REGISTER_FILE )?>" />
        <!--[if gte IE 7]>
           <style>svg { height: 450px }</style>
        <![endif]-->
        <!-- main script -->
        <script src="<?php echo plugins_url( 'lib/map/src/main.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>

        
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <!--[if lte IE 10]>
        <style>
        #fullscreen-button { display:none; };
        </style>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Mobile-spezifische Metatags
        ================================================== -->
        <!--<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">-->
		
          <div id="juxtalearn-map">
                <div id="loading">
                    Loading...
                </div>

            <header>
                <h1>OER Research Hub - Example Map</h1>
            </header>
            <div id="impressum">
                <small>
                    <p>Tested in latest versions of Firefox, Chrome, Safari, and Internet Explorer.</p>
                    <strong>Original Author</strong>:
                    <br/>
                    Timo Grossenbacher (BSc in Geography, University of Zurich)
                    <br/>
                    <strong>Modified By Author</strong>:
                    <br/>
                    Martin Hawksey
                    <br/>
                    <strong>Sources</strong>:
                    <br/>
                    Original Code: <a href="http://labs.wnstnsmth.net/worldoil/">Timo Grossenbacher/Global Oil Presentation</a>
                    <br/>
                    Geodata: <a href="https://github.com/mbostock/topojson/blob/master/examples/world-110m.json">mbostock/topojson</a>            
               </small>    
            </div>
		  </div>
        
        <script type="text/javascript">
            window.addEvent('domready', function() {
                init();
                //constructControlPanel('Global Oil Production & Consumption');
                
            });
        </script>
        <div id="fullscreen-button"><a href="#" id="juxtalearn-map-fullscreen">Full Screen</a></div>
		<script src="<?php echo plugins_url( 'lib/map/lib/bigscreen.min.js' , JUXTALEARN_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
		<script>
		var element = document.getElementById('juxtalearn-map');

		document.getElementById('juxtalearn-map-fullscreen').addEventListener('click', function() {
			if (BigScreen.enabled) {
				BigScreen.request(element, onEnterJuxtaLearnMap, onExitJuxtaLearnMap);
				// You could also use .toggle(element, onEnter, onExit, onError)
			}
			else {
				// fallback for browsers that don't support full screen
			}
		}, false);
		
			// called when the first element enters full screen
		
		function onEnterJuxtaLearnMap(){
			jQuery('#impressum').show();
			jQuery('#juxtalearn-map').css('height','100%');
			jQuery('#ui').show();
		}
		function onExitJuxtaLearnMap(){
			jQuery('#impressum').hide();
			jQuery('#juxtalearn-map').css('height','');
			jQuery('#ui').hide();
		}
</script>
		<?php
		return ob_get_clean();
	}
}