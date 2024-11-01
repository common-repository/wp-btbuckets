<?php
class WPBTBuckets {

  // settings
  var $homepage = 'http://omninoggin.com/wordpress-plugins/wp-btbuckets-wordpress-plugin/';
  var $author_homepage = 'http://omninoggin.com/';
  var $name = 'wp_btbuckets';
  var $name_dashed = 'wp-btbuckets';
  var $name_proper = 'WP BTBuckets';
  var $version = '1.1';
  var $required_wp_version = '2.9';
  var $btb_api = 'https://btbuckets.com/services/%s/uk/%s/thaya';

  // variables
  var $o = null; // options
  var $path = null; // plugin page

  // objects
  var $a = null; // admin
  var $c = null; // common

  function WPBTBuckets() {
    // initialize common functions
    require_once(dirname(__FILE__).'/common.class.php');
    $this->c = new WPBTBucketsCommon($this);
    
    // initialize vaiables
    $this->o = get_option($this->name);
    $this->path = trailingslashit($this->c->get_plugin_dir());

    // load translation
    $this->c->load_text_domain();

    // admin mode or public mode?
    if (is_admin()) {
      // create admin object
      require_once(dirname(__FILE__).'/admin.class.php');
      $this->a = new WPBTBucketsAdmin($this);
    } else {
      // if no options then don't do anything in public
      if ($this->o) {
        // queue up other hooks after all plugins are loaded (per dependencies if any)
        add_action('plugins_loaded', array($this, 'execute'));
      }
    }
  }

  function execute() {
    // public hooks

    // track BTBuckets regardless
    add_action('wp_head', array($this, 'insert_tag'), 20);
    add_action('wp_footer', array($this, 'advertise'));

    if ($this->o['enable_smart_widgets'] && count($this->o['widget_config'])) {
      add_action('init', array($this, 'register_styles'));
      add_action('init', array($this, 'register_scripts'));
      add_action('wp_print_styles', array($this, 'enqueue_styles'));
      add_action('wp_print_scripts', array($this, 'enqueue_scripts'));
      add_action('wp_head', array($this, 'replace_widget_output_callback'));
      add_action('wp_footer', array($this, 'show_and_hide_widgets'));
    }
  }

  function register_styles() {
    wp_register_style($this->name.'_style',
      $this->c->get_plugin_url().'css/style.css');
  }

  function enqueue_styles() {
    wp_enqueue_style($this->name.'_style');
  }

  function register_scripts() {}

  function enqueue_scripts() {}

  function insert_tag() {
    if ($this->o['tag'] && strlen($this->o['tag'])) {
      printf("<!-- BTBuckets tag-->%s<!-- BTBuckets tag end -->\n", $this->o['tag']);
    }
  }

  function replace_widget_output_callback() {
    global $wp_registered_widgets;

    foreach ($wp_registered_widgets as $widget_id => $widget_data) {
      // Save the original widget id
      $wp_registered_widgets[$widget_id]['params'][]['widget_id'] = $widget_id;
      // Store original widget callbacks
      $wp_registered_widgets[$widget_id]['callback_original_wpbtb'] = $wp_registered_widgets[$widget_id]['callback'];
      $wp_registered_widgets[$widget_id]['callback'] = array($this, 'replace_widget_output');
    }
  }
  
  function replace_widget_output() {
    global $wp_registered_widgets;

    $all_params = func_get_args();

    if (is_array($all_params[2]))
      $widget_id = $all_params[2]['widget_id'];
    else
      $widget_id = $all_params[1]['widget_id'];

    $widget_callback = $wp_registered_widgets[$widget_id]['callback_original_wpbtb'];

    if (is_callable($widget_callback)) {
      $this->current_widget_id = $widget_id;
      ob_start(array($this, 'prepare_widget'));
      call_user_func_array($widget_callback, $all_params);
      ob_end_flush();
      $this->current_widget_id = null;
      return true;
    } elseif (!is_callable($widget_callback)) {
      print '<!-- widget context: could not call the original callback function -->';
      return false;
    } else {
      return false;
    }
  }
  
  function prepare_widget($buffer) {
    $widget_id = $this->current_widget_id;
    
    if ($this->p->o['debug'])
      $buffer .= $widget_id.'<br/>';
      
    if ($this->o['widget_config'][$widget_id]['widget_mode'] == 'start_hidden') {
      // add style="display:none" to widget block if it starts as hidden
      if (isset($this->o['widget_config'][$widget_id]['lookup'])) {
        // links widget
        $id = implode('|', $this->o['widget_config'][$widget_id]['lookup']);
      } else {
        $id = $widget_id;
      }
      $buffer = preg_replace('/( id=["\']('.$id.')["\'] )/', '$1style="display:none" ', $buffer, 1);
    }

    return $buffer;
  }
  
  function show_and_hide_widgets() {
    $show_list = array();
    $hide_list = array();
    foreach ($this->o['buckets'] as $bucket_name => $bucket_config) {
      $show_list[$bucket_name] = array();
      $hide_list[$bucket_name] = array();
    }
    
    foreach ($this->o['widget_config'] as $w_id => $w_config) {
      if ($w_config) {
        if ($w_config['widget_mode'] == 'start_hidden' && isset($w_config['to_show'])) {
          foreach ($w_config['to_show'] as $bucket => $show)
            if ($show) $show_list[$bucket][] = $w_id;
        } elseif (isset($w_config['to_hide'])) {
          foreach ($w_config['to_hide'] as $bucket => $hide)
            if ($hide) $hide_list[$bucket][] = $w_id;
        }
      }
    }
    require_once($this->path.'views/public/onload.php');
  }
  
  function advertise() {
    if ($this->o['show_link']) {
      printf("<p align='center'><small>Page Optimized by <a href='$this->homepage' title='$this->name_proper WordPress Plugin' style='text-decoration:none;'>$this->name_proper</a> <a href='$this->author_homepage' title='WordPress Plugin' style='text-decoration:none;'>WordPress Plugin</a></small></p>");
    }
  }
  
  function call_btb($method) {
    if (isset($this->o['api_key']) && strlen($method)) {
      $api_url = sprintf($this->btb_api, $method, $this->o['api_key']);
      $buffer = @file_get_contents($api_url);
      $json_obj = @json_decode(substr($buffer, 3));
      return $json_obj;
    }
  }

} // class WPBTBuckets
?>
