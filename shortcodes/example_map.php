<?php
/**
 * Fancy Evidence Map with Bars and Sankey Shortcode
 *
 * Shortcode: [example_map]
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */
class JuxtaLearn_Hub_Shortcode_Example_Map extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'example_map';
	public $defaults = array();

	static $post_types_with_example = array();
	

	function content() {
		ob_start();
		extract($this->options);
		$errors = array();	
		
		/**
		* A lot of this is a fudge to get the data in the right shape for Timo Grossenbacher/Global Oil Presentation.
		* The overall aim is to get a year bin (the actual year is currently ignored) with an array containing: country name,
		* slug, id (country id used in world-110m.json TOPOJson), and evidence counts for +ve/-ve extract shown below. Once we 
		* have this Timo's script (with some modification) does the rest.   
		* var data = {
		* 		  "2013": [
		* 			{
		* 			  "name": "Australia",
		* 			  "slug": "au",
		* 			  "id": "36",
		* 			  "positive": 0,
		* 			  "negative": 1
		* 			},
		* 			{
		* 			  "name": "Belgium",
		* 			  "slug": "be",
		* 			  "id": "56",
		* 			  "positive": 0,
		* 			  "negative": 0
		* 			},
		*			...
		* 		  ]
		* 		} 
		*
		*/
		
		$year = array();
		$graph = array();
		$nodes = array();
		$links = array();
		$totals = array();
		
		// Build a country slug => id lookup by reading csv used in visualisation
		$country_ids = $this->get_country_ids();
		
		$world = array("name"=>"World",
			   "id" => 900,
			   "positive" => 0,
			   "negative" => 0);  
		 
		$posttypes = array('student_problem','teaching_activity');
		$args = array('post_type' => $posttypes, // my custom post type
    				   'posts_per_page' => -1,
					   'post_status' => 'publish',
					   'fields' => 'ids'); // show all posts);
		
		
		$posts = JuxtaLearn_Hub::add_terms(get_posts($args));
		
		$countries = get_terms('juxtalearn_hub_country', array('post_types' => $posttypes ));
		
		$args['post_type'] = 'tricky_topic';
		$trickytopic = get_posts($args);

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
		
		// finally echo all the HTML/JS required
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
		var data = <?php echo json_encode($data);?>;
		/* ]]> */
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

<!--[if lte IE 8]>
            <div class="jl-chart-no-js">
              <p>Unfortunately, the map doesn't work in older browsers. Please <a
                href="http://whatbrowser.org/">try a different browser</a>.</p>
            </div>
<![endif]-->

                <div id="loading" class="jl-chart-loading">Loading map...</div>

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
	/**
	* Build a country slug => id lookup.
	*
	* @since 0.1.1
	* @return array 
	*/
	private function get_country_ids(){
		$country_ids = array();
		$handle = fopen(JUXTALEARN_HUB_PATH.'/lib/map/data/world-country-names.csv', 'r'); 
		if ($handle) { 
			set_time_limit(0); 		
			//loop through one row at a time 
			while (($rows = fgetcsv($handle, 256, ';')) !== FALSE) 
			{ 
				$country_ids[$rows[2]] = $rows[0];
			} 
			fclose($handle); 
		}
		return $country_ids; 	
	}
}