<?php
/**
 * Trickytopic scorecard 
 *
 * Generates metadata bars for policy
 * Shortcode: [tricky_topic_summary]
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */

// Base class 'JuxtaLearn_Hub_Shortcode' defined in 'shortcodes/shortcode.php'.
class JuxtaLearn_Hub_Shortcode_Tricky_Topic_Summary extends JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'tricky_topic_summary';
	public $defaults = array(
		'post_id' => false,
		'post_ids' => false,
		'title' => false,
		'sankey' => true,
		'no_example_message' => "There is no example yet for this trickytopic",
		'title_tag' => 'h3',
	);

	

	static $post_types_with_example = array('tricky_topic');
	
	function add_to_page($content) {
		if (in_array(get_post_type(), self::$post_types_with_example)) {
			if (is_single()) {
				$content = do_shortcode('[tricky_topic_summary]'); 
			} else {
				$content .= do_shortcode('[tricky_topic_summary sankey=false]');
			}
		}
		return $content;
	}

    /**
     * @return string
     */
	function content() {
		ob_start();
		extract($this->options);
		$post_id = $post_id = get_the_ID();
		$errors = array();
		if (!$post_id) $errors[] = "No posts ID provided";
		?>
        <div class="juxtalearn-list">
        	<?php echo do_shortcode('[example_meta location="header"]'); ?>
            <<?php echo $title_tag; ?>>
                <?php if (!$title) { ?>
                    Summary 
                <?php } else echo $title; ?>
            </<?php echo $title_tag; ?>>
         <div id="sankey-chart"></div>
		<?php
		$args = array('post_type' => array('student_problem','teaching_activity'), // my custom post type
    				   'posts_per_page' => -1,
					   'post_status' => 'publish',
					   'fields' => 'ids',
					   'meta_query' => array(
									array(
										'key' => 'juxtalearn_hub_trickytopic_id',
										'value' => $post_id,
										'compare' => '='
									)
								)); // show all posts);
		$example = JuxtaLearn_Hub::add_terms(get_posts($args));
		if (!empty($example) || !empty($no_example_message)) :
			$nodes = array();
			$base_link = get_permalink();
			


            echo '<div id="juxtalearn-balance">'; //html

            $links = $this->print_get_nodes_links($example, $nodes, $post_id);

            $this->print_sankey_javascript($sankey, $nodes, $links);
        ?>
        <?php else: ?>
                <p><?php echo $no_example_message; ?></p>
        <?php endif; // end of if !empty($example) ?>
		<?php echo '</div>'; //html end of juxtalearn-balance ?>
        <?php echo '<br/>'; ?>
        <?php echo do_shortcode('[example_meta location="footer" footer_terms="subject,country"]'); ?>
<?php return ob_get_clean();
	} // end of function content


    /**
     * @param array [in/out]
     * @return array Get array of links.
     */
    function print_get_nodes_links($hposts, &$nodes, $post_id) {
        $base_link = get_permalink();
        $links = array();
        $nodesList = array();
		$hposts_title = get_the_title($post_id);
			
		$nodes[] = array("name" => $hposts_title, "url" => $base_link, "id" => $post_id, "type" => "trickytopic" );

        // get polarity and sector terms
			$posttypes = array('student_problem','teaching_activity');
			$sectors = get_terms('juxtalearn_hub_education_level', 'hide_empty=0');
			$sectors[] = (object) array('slug' => '',
							   'name' => 'No Level');
			echo '<div class="juxtalearn-box">'; //html 
			echo '<table><tr>';
			foreach ($posttypes as $posttype){
				$pposts = JuxtaLearn_Hub::filterOptions($hposts, 'post_type', $posttype);
				
				echo '<td class="'.$posttype.'"><h4>'.ucwords(str_replace("_", " ", $posttype)).' ('.count($pposts).')</h4>'; //html
				echo '<ul>'; //html
				if (empty($nodeList[$posttype])){
					$nodes[] = array("name" => ucwords(str_replace("_", " ", $posttype)), "url" => $base_link.$posttype, "id" => $posttype, "type" => "polarity");
					$nodeList[$posttype] = 1;
				}
				if (count($pposts) > 0){ 
					$links[] = array("source" => $hposts_title, "target" => ucwords(str_replace("_", " ", $posttype)), "value" => count($pposts));
				}
				foreach($sectors as $sector){	
					$sposts = JuxtaLearn_Hub::filterOptions($pposts, 'education_level_slug', $sector->slug);
					if (empty($nodeList[$sector->name])){
						$nodes[] = array("name" => $sector->name, "url" => $base_link."education_level/".$sector->slug, "id" => $sector->slug, "type" => "sector", );
						$nodeList[$sector->name] = 1;
					}
					if (count($sposts) > 0) {
						$links[] = array("source" => ucwords(str_replace("_", " ", $posttype)), "target" => $sector->name, "value" => count($sposts), "data" => array("url" => "xxx"));	
						echo '<li>'.$sector->name; //html
						echo '<ul>'; //html 
						foreach($sposts as $epost){
							echo '<li><a href="'.get_permalink($epost['ID']).'">'.get_the_title($epost['ID']).'</a></li>'; //html
						}
						echo '</ul>'; //html
						echo '</li>'; //html
					}
				}
				echo '</ul></td>'; // html
				
			}
			echo '</tr></table></div>'; //html end of div juxtalearn-box

        return $links;
    }


    /**
     * @return NULL
     */
    function print_sankey_javascript($sankey, $nodes, $links) {
        $graph = array('nodes' => $nodes, 'links' => $links); ?>
		<?php if ($sankey == 1): // <-- start of sankey if single ?>
            <script>
            var graph = <?php print_r(json_encode($graph)); ?>;
            var margin = {top: 1, right: 1, bottom: 1, left: 1},
                width = document.getElementById("content").offsetWidth - margin.left - margin.right,
                height = 400 - margin.top - margin.bottom;
            </script>
            <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'lib/map/css/styles.css' , JUXTALEARN_HUB_REGISTER_FILE )?>" />
            <script src="<?php echo plugins_url( 'js/sankey.js' , JUXTALEARN_HUB_REGISTER_FILE )?>"></script>
            <script src="<?php echo plugins_url( 'js/sankey-control.js' , JUXTALEARN_HUB_REGISTER_FILE )?>"></script>
        <?php endif; // end of sankey if single and no example.
    }

} // end of class
