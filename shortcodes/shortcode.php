<?php
/**
 * Abstract class used to construct shortcodes
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 *
 * @package JuxtaLearn_Hub
 * @subpackage JuxtaLearn_Hub_Shortcode
 */

abstract class JuxtaLearn_Hub_Shortcode {
	public $shortcode = 'juxtalearn_hub_shortcode';
	public $defaults = array();
	public $options = array();

	const LOC_DOMAIN = JuxtaLearn_Hub::LOC_DOMAIN;


	public function __construct() {
		add_shortcode($this->shortcode, array(&$this, 'shortcode'));
		add_filter('the_content', array(&$this, 'pre_add_to_page'));

		add_action('save_post', array(&$this, 'save_post'));
		add_action('trash_post', array(&$this, 'trash_post'));
		
		register_activation_hook(JUXTALEARN_HUB_REGISTER_FILE, array(&$this, 'activate'));
		register_deactivation_hook(JUXTALEARN_HUB_REGISTER_FILE, array(&$this, 'deactivate'));
		
		global $wpdb;
		$wpdb->juxtalearn_hub_shortcode_cache = $wpdb->prefix.'juxtalearn_hub_shortcode_cache';
	}
		
	public function shortcode($options) {
		$this->options = shortcode_atts($this->defaults, $options);	
		$this->prep_options();
		
		if (!$content = $this->get_cache()) {
			$content = $this->content();
			$this->cache($content);
		}

		$this->content_end();

		return $content;
	}
	
	public function pre_add_to_page($content) {
		$options = get_option('juxtalearn_hub_options');
		$options['add_to_page'] = 1;
		return $options['add_to_page'] ? $this->add_to_page($content) : $content;
	}
	
	protected function add_to_page($content) {
		return $content;
	}

	public function meta_bar($post, $options = 'sb', $return = FALSE) {
		$MY_SEP = ' | ';

		$result = '';
		$out = array();
		foreach (explode(',', $options) as $type) {
			$type = trim($type);
			$slug = $type."_slug";
			if ($type == 'sb'){

				$tags =wp_get_post_terms( $post['ID'], 'juxtalearn_hub_'.$type);
				$ln = '<span class="meta_label">'. __('Stumbling Blocks:', self::LOC_DOMAIN) .'</span> ';
				foreach($tags as $idx => $tag){
					$sep = $idx > 0 ? $MY_SEP : '';
					$ln .= $sep . '<a href="'.get_term_link($tag).'">'.$tag->name.'</a>';
				}
				$out[] = $ln;
				
			} elseif (isset($post[$type]) && isset($post[$slug])){
				if (is_array($post[$slug]) && is_array($post[$type])){
					foreach ($post[$slug] as $idx => $post_slug){
						$out[] = $this->get_meta($post, $type, $slug, $idx);
					}
				} else {
					$out[] = $this->get_meta($post, $type, $slug);
				}
			} else {
				$out[] = $this->get_meta($post, $type);
			}
		}
		$out = array_filter($out);
		if (!empty($out)) {
			$result = '<div class="juxtalearn-meta">'.implode($MY_SEP, $out).'</div>';
		}
		if ($return) {
			return $result;
		}
		echo $result;
	}

	function get_meta($post, $type, $slug = false, $idx = false){
		if (!isset($post[$type]) || $post[$type] == ""){
			return;
		}
		if ($idx){
			$slug_url = get_term_link($post[$slug][$idx],"juxtalearn_hub_".$type);
			$name = $post[$type][$idx];
		} elseif ($slug) {
			$slug_url = get_term_link($post[$slug],"juxtalearn_hub_".$type);
			$name = $post[$type];
		} elseif (isset($post[$type])) {
			$name = $post[$type];
		}


		if (isset($slug_url) && !is_wp_error($slug_url)){
			return __(sprintf('<span class="meta_label">%s</span>: <a href="%s">%s</a>', ucwords(str_replace("_", " ",$type)),$slug_url , ucwords($name)));
		} elseif ($type == 'trickytopic') {
			return  __(sprintf('<span class="meta_label">%s</span>: <a href="%s">%s</a>', ucwords($type),get_permalink($post['trickytopic_id']) , get_the_title($post['trickytopic_id'])));
		} elseif(isset($post[$type]) && ($type=="citation" || $type=="resource_link" || $type == "post_type")) {
			if ($type == "post_type"){
				return __(sprintf('<span class="meta_label">%s</span>: <a href="%s">%s</a>', ucwords(str_replace("_", " ",$type)), get_post_type_archive_link($post[$type]), ucwords($post[$type])));
			} else {
				return __(sprintf('<span class="meta_label">%s</span>: <a href="%s">%s</a>', ucwords(str_replace("_", " ",$type)), $post[$type], $post[$type]));
			}
		} elseif (isset($post[$type]) && $type == "link") {
			if (filter_var($post[$type], FILTER_VALIDATE_URL) === FALSE) {
				return __(sprintf('<span class="meta_label">%s</span>: %s', ucwords(str_replace("_", " ",$type)),$post[$type]));
			} else {
				return __(sprintf('<span class="meta_label">%s</span>: <a href="%s">Resource Link</a>', ucwords(str_replace("_", " ",$type)),$post[$type]));
			}
		}
		elseif (isset($post[$type]) ) {
			return __(sprintf('<span class="meta_label">%s</span>: %s', ucwords(str_replace("_", " ",$type)),$post[$type]));
		}
		return;
	}
	
	protected function prep_options() {
		foreach ($this->options as $key => $value) {
			if (is_string($value)) {
				if ($value == 'true') $this->options[$key] = true;
				if ($value == 'false') $this->options[$key] = false;
			}
		}
	}


	protected function content_end($shortcode = NULL) {
		$shortcode = $shortcode ? $shortcode : get_class($this);
		?>
	<script>
	document.documentElement.className += " jxl-shortcode <?php echo $shortcode ?>";
	</script>
<?php
	}


	abstract protected function content();

	
	// Caching ----------------------------------------------------------------

	// TODO: doesn't $wpdb need to be globalized in this function?
	public function activate() {
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta("CREATE TABLE $wpdb->juxtalearn_hub_shortcode_cache (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			shortcode text NOT NULL,
			options text NOT NULL,
			content mediumtext NOT NULL,
			UNIQUE KEY id(id)
		);");
	}
	
	public function deactivate() {
		global $wpdb;
		$wpdb->query("drop table $wpdb->juxtalearn_hub_shortcode_cache");
	}
	
	public function save_post($post_id) {
		if (!in_array(get_post_type($post_id), JuxtaLearn_Hub::$post_types)) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		self::clear_cache();
	}
		
	public function trash_post($post_id) {
		if (!in_array(get_post_type($post_id), JuxtaLearn_Hub::$post_types)) return;
		self::clear_cache();
	}
		
	protected function get_cache() {
		if (!get_option('juxtalearn_hub_caching')) return false;
		
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare(
			"SELECT content
			from $wpdb->juxtalearn_hub_shortcode_cache
			where shortcode = %s
			and options = %s",
			$this->shortcode,
			serialize($this->options)
		));
	}
	
	protected function cache($content) {
		global $wpdb;
		$wpdb->insert($wpdb->juxtalearn_hub_shortcode_cache, array(
			'created' => current_time('mysql'),
			'shortcode' => $this->shortcode,
			'options' => serialize($this->options),
			'content' => $content,
		));
	}
	
	protected static function get_all_cache() {
		global $wpdb;
		return $wpdb->get_results("SELECT shortcode, count(id) AS count FROM $wpdb->juxtalearn_hub_shortcode_cache GROUP BY shortcode", OBJECT);
	}

	protected static function clear_cache() {
		global $wpdb;
		$wpdb->query("TRUNCATE $wpdb->juxtalearn_hub_shortcode_cache");
	}
}