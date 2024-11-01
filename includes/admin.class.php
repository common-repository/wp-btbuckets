<?php
class WPBTBucketsAdmin {

  // objects
  var $p = null;

  function WPBTBucketsAdmin($plugin) {
    $this->p = $plugin;

    // activation hook
    register_activation_hook(dirname(dirname(__FILE__)).'/'.$this->p->name_dashed.'.php', array($this, 'activate'));

    // register admin hooks
    add_action('admin_init', array($this, 'request_handler'));
    add_action('admin_init', array($this, 'register_scripts'));
    add_action('admin_init', array($this, 'register_styles'));
    add_action('admin_print_scripts', array($this, 'enqueue_widget_scripts'));
    add_action('admin_print_styles', array($this, 'enqueue_widget_styles'));
    add_action('admin_head', array($this, 'check_for_btb_update'));
    add_action('admin_head', array($this, 'check_empty_api_key'));
    add_action('admin_head', array($this->p->c, 'a_check_version'));
    add_action('admin_menu', array($this, 'menu'));
    add_action('wp_ajax_'.$this->p->name, array($this, 'ajax_request_handler'));

    // widgets
    if ($this->p->o['enable_smart_widgets']) {
       add_action('sidebar_admin_setup', array($this, 'attach_widget_context_controls'));
      // Save widget context settings, when in admin area
      add_filter('sidebars_widgets', array($this, 'filter_widgets'), 50);
    }
  }
  
  function default_options() {
    return array(
      'version' => $this->p->version,
      'show_link' => true,
      'debug' => false,
      'enable_smart_widgets' => true,
      'btb_last_updated' => 0,
      'btb_refresh_interval' => 3600,
      'buckets' => array(),
      'deprecated' => array(),
      'widget_config' => array(),
      'widget_modes' => array(
        'start_visible' => 'Visible by Default',
        'start_hidden' => 'Hidden by Default'
      )
    );
  }

  function activate() {
    $this->p->c->a_check_version();
  }

  function register_scripts() {
    wp_register_script($this->p->name.'_jquery_livequery',
      $this->p->c->get_plugin_url().'js/jquery.livequery.js', array('jquery'));
    wp_register_script($this->p->name.'_widgets_onload',
      $this->p->c->get_plugin_url().'js/widgets-onload.js', array('jquery'));
  }

  function enqueue_scripts() {
    wp_enqueue_script($this->p->name.'_jquery_livequery');
  }
  
  function enqueue_widget_scripts() {
    if (strstr($_SERVER['SCRIPT_NAME'], 'widgets.php'))
      wp_enqueue_script($this->p->name.'_widgets_onload', array($this->p->name.'_jquery_livequery'));
  }

  function register_styles() {
    $this->p->c->a_register_styles();
    wp_register_style($this->p->name.'_style_admin_widgets',
      $this->p->c->get_plugin_url().'css/style-admin-widgets.css');
  }

  function enqueue_styles() {
    $this->p->c->a_enqueue_styles();
  }

  function enqueue_widget_styles() {
    if (strstr($_SERVER['SCRIPT_NAME'], 'widgets.php'))
      wp_enqueue_style($this->p->name.'_style_admin_widgets');
  }

  function ajax_request_handler() {
    check_ajax_referer($this->p->name);
    if (isset($_POST['wpbtb_action'])) {
      if (strtolower($_POST['wpbtb_action']) == 'show_advanced_options') {
        $this->ajax_enable_advanced_options(true);
      } elseif (strtolower($_POST['wpbtb_action']) == 'hide_advanced_options') {
        $this->ajax_enable_advanced_options(false);
      } else {
        echo 'Invalid wpbtb_action.';
      }
      exit();
    }
  }

  function request_handler() {
    if (isset($_POST['wpbtb_options_general_submit'])) {
      check_admin_referer($this->p->name);
      $this->update_options_general();
    } elseif (isset($_POST['wpbtb_options_refresh_btb'])) {
      check_admin_referer($this->p->name);
      $this->update_btb_data($_POST['wpbtb_options_general']['api_key'], true);
    } elseif (isset($_POST['wpbtb_options_upgrade'])) {
      check_admin_referer($this->p->name);
      $this->p->c->a_upgrade_options();
    } elseif (isset($_POST['wpbtb_options_reset'])) {
      check_admin_referer($this->p->name);
      $this->p->c->a_reset_options();
      $this->p->o = get_option($this->p->name);
    } elseif (isset($_POST['wpbtb_options_import'])) {
      check_admin_referer($this->p->name);
      $this->p->c->a_import_options('wpbtb_options_import_file');
      $this->p->o = get_option($this->p->name);
    } elseif (isset($_POST['wpbtb_options_export'])) {
      $this->p->c->a_export_options($this->p->name_dashed.'-options');
    }
  }

  function ajax_enable_advanced_options($enable) {
    if ($enable == true){
      $this->p->o['show_advanced_options'] = true;
    } else {
      $this->p->o['show_advanced_options'] = false;
    }
    update_option($this->p->name, $this->p->o);
  }

  function update_options_general() {
    if (!current_user_can('manage_options'))
      die(__('You cannot edit the BTBuckets options.', $this->p->name));

    $new_options = $_POST['wpbtb_options_general'];

    // first check API key and generate BTBuckets tag, and update buckets list if necessary
    if (!strlen($new_options['api_key'])) {
      add_action('admin_notices', array($this->p->c, 'a_notify_invalid_api_key'));
    } elseif ($this->p->o['api_key'] != $new_options['api_key']) {
      $this->update_btb_data($new_options['api_key'], true);
    }

    if ($new_options['enable_smart_widgets'])
      $this->p->o['enable_smart_widgets'] = true;
    else
      $this->p->o['enable_smart_widgets'] = false;
    
    if ($new_options['show_link'])
      $this->p->o['show_link'] = true;
    else
      $this->p->o['show_link'] = false;
    
    // update other options
    foreach ($new_options as $k => $v) {
      if (!in_array($k, array('enable', 'show_link', 'api_key'))) {
        $this->p->o[$k] = $v;
      }
    }
    
    update_option($this->p->name, $this->p->o);
  }
  
  function update_btb_data($api_key, $notify=false) {
    // check API key, generate BTBuckets tag, and update buckets list
    if (!strlen($api_key)) {
      if ($notify)
        add_action('admin_notices', array($this->p->c, 'a_notify_invalid_api_key'));
    } else {
      $this->p->o['api_key'] = $api_key;

      // get tag
      $json_obj = $this->p->call_btb('generate_tag');
      if ($json_obj->tag_code) {
        //$tag_code = preg_replace('/\$BTB={s:(\w*?)};/', '$BTB={s:\1,ref:thaya};', ($json_obj->tag_code));
        $tag_code = ($json_obj->tag_code);
        $this->p->o['tag'] = $tag_code;
        if ($notify)
          $this->p->c->a_notify(__('BTBuckets tag updated', $this->p->name), false);
      } else {
        if ($notify)
          $this->p->c->a_notify(__('Error updating BTBuckets tag', $this->p->name), true);
      }
      
      // update buckets list
      $json_obj = $this->p->call_btb('get_all_buckets');
      if ($json_obj->buckets) {
        $new_buckets = array();
        foreach ($json_obj->buckets as $k => $bucket) {
          $bucket_config = array();
          foreach ($bucket as $field_name => $value) {
            $bucket_config[$field_name] = $value;
          }
          if (isset($this->p->o[$bucket_config['friendly_name']]['show'])) {
            $bucket_config['show'] = $this->p->o[$bucket_config['friendly_name']]['show'];
          } else {
            $bucket_config['show'] = true;              
          }
          if (count($bucket_config)) {
            $new_buckets[$bucket_config['friendly_name']] = $bucket_config;
          }
        }
        if (count($new_buckets)) {
          $this->p->o['buckets'] = $new_buckets;
          if ($notify)
            $this->p->c->a_notify(__('Buckets updated', $this->p->name), false);
        } else {
          if ($notify)
            $this->p->c->a_notify(__('Failed to update Buckets', $this->p->name), false);            
        }
      } else {
        if ($notify)
          $this->p->c->a_notify(__('Error retrieving buckets', $this->p->name), true);
      }
      
      $this->p->o['btb_last_updated'] = time();
      
      update_option($this->p->name, $this->p->o);
    }      
  }

  function check_for_btb_update() {
    // update zone info if it's time
    if ((time() - $this->p->o['btb_last_updated']) > $this->p->o['btb_refresh_interval']) {
      $this->update_btb_data($this->p->o['api_key'], false);
    }
  }
  
  function check_empty_api_key() {
    if (!strlen($this->p->o['api_key'])) {
      $this->p->c->a_notify(sprintf(__('Please enter your BTBuckets API Key on the WP BTBuckets %soptions page%s.'), '<a href="options-general.php?page=wp_btbuckets">', '</a>', false));
    }
  }

  function menu() {
    $options_page = add_options_page($this->p->name_proper, $this->p->name_proper, 'manage_options', $this->p->name, array($this, 'page'));
    // only load these scripts on this plugin's options page
    add_action('admin_print_scripts-'.$options_page, array($this, 'enqueue_scripts'));
    // need style on all pages
    add_action('admin_print_styles', array($this, 'enqueue_styles'));
    // only check on settings pages
    add_action('admin_head-'.$options_page, array($this->p->c, 'a_check_orphan_options'));
  }

  function page() {
    require_once($this->p->path.'views/admin/header.php');

    if(isset($_GET['wpbtb-page'])) {
      if($_GET['wpbtb-page'] == 'generic') {
        require_once($this->p->path.'views/admin/generic.php');
      }
      elseif($_GET['wpbtb-page'] == 'import-export') {
        require_once($this->p->path.'views/admin/import-export.php');
      } else {
        require_once($this->p->path.'views/admin/generic.php');
      }
    } else {
      require_once($this->p->path.'views/admin/generic.php');
    }

    require_once($this->p->path.'views/admin/sidebar.php');  
    require_once($this->p->path.'views/admin/footer.php');
  } // function page()
  
  function attach_widget_context_controls() {
    global $wp_registered_widget_controls, $wp_registered_widgets;
    
    // Don't show custom config when adding (there's a bug)
    if (!isset($_POST['add_new'])) {
      foreach ($wp_registered_widgets as $widget_id => $widget_data) {
        // Pass widget id as param, so that we can later call the original callback function
        $wp_registered_widget_controls[$widget_id]['params'][]['widget_id'] = $widget_id;
        // Store the original callback functions and replace them with Widget Context
        $wp_registered_widget_controls[$widget_id]['callback_original_wpbtb'] = $wp_registered_widget_controls[$widget_id]['callback'];
        $wp_registered_widget_controls[$widget_id]['callback'] = array($this, 'replace_widget_control_callback');
      }
    }
  }
  
  function replace_widget_control_callback() {
    global $wp_registered_widget_controls;
    
    $all_params = func_get_args();

    //if (isset($all_params['number']) && $all_params['number'] == '-1') {
    //  // do not show when initilizing multi-instance widget
    //  // since we don't have the proper widget_id yet

    //  // Display the original callback
    //  if (isset($original_callback) && is_callable($original_callback)) {
    //    call_user_func_array($original_callback, $all_params);
    //  } else {
    //    print '<!-- WP BTBuckets [controls]: could not call the original callback function -->';
    //  }
    //}
    
    if (is_array($all_params[1]))
      $widget_id = $all_params[1]['widget_id'];
    else
      $widget_id = $all_params[0]['widget_id'];
                
    $original_callback = $wp_registered_widget_controls[$widget_id]['callback_original_wpbtb'];
                
    // Display the original callback
    if (isset($original_callback) && is_callable($original_callback)) {
      call_user_func_array($original_callback, $all_params);
    } else {
      print '<!-- WP BTBuckets [controls]: could not call the original callback function -->';
    }
    
    $this->display_widget_context($original_callback, $widget_id);
  }
  
  function display_widget_context($args = array(), $wid = null) {
    require($this->p->path.'views/admin/widget-options.php');
  }

  function filter_widgets($sidebars_widgets) {
    foreach ($sidebars_widgets as $sidebar_id => $widgets) {
      // Don't configure inactive widgets
      if ($sidebar_id != 'wp_inactive_widgets' && !empty($widgets)) {
        foreach ($widgets as $widget_no => $widget_id) {
          // initialize widget_config for this widget_id if it's doesn't exist yet
          if (!isset($this->p->o['widget_config'][$widget_id]) || !count($this->p->o['widget_config'][$widget_id])) {
            $this->p->o['widget_config'][$widget_id] = array();
            $this->p->o['widget_config'][$widget_id]['widget_mode'] = 'start_visible';
            $this->p->o['widget_config'][$widget_id]['to_hide'] = array();
            $this->p->o['widget_config'][$widget_id]['to_show'] = array();
          }
        }
      }
    }

    update_option($this->p->name, $this->p->o);
    
    if (isset($_POST['wpbtb_widget_config']) && !empty($_POST['wpbtb_widget_config'])) {
      $this->save_widget_context();
      unset($_POST['wpbtb_widget_config']);
    }
    
    return $sidebars_widgets;
  }
  
  function save_widget_context() {
    // Delete
    if (isset($_POST['delete_widget']) && $_POST['delete_widget']) {
      $del_id = $_POST['widget-id'];
      unset($this->p->o['widget_config'][$del_id]);
    } else {
      $new_settings = $_POST['wpbtb_widget_config']['widget_config'];

      foreach($new_settings as $widget_id => $widget_settings) {
        // Add/Update
        if (isset($_POST['widget-links'])) {
          // links widget is special
          // save as "links-*"
          $this->p->o['widget_config'][$widget_id] = $widget_settings;
          foreach ($_POST['widget-links'] as $links_widget_num => $links_widget_data) {
            if (strlen($links_widget_data['category'])) {
              // save as "linkcat-*" for category specified in links widget
              $this->p->o['widget_config']['linkcat-'.$links_widget_data['category']] = $widget_settings;
              // save lookup in original "links-*"
              $this->p->o['widget_config'][$widget_id]['lookup'][] = 'linkcat-'.$links_widget_data['category'];
            } else {
              // save as "linkcat-*" for all categories if "All Links" is selected in links widget
              $args = array('offset' => 0, 'hide_empty' => 0);
              $categories = get_terms('link_category', $args);
              foreach ($categories as $category) {
                $this->p->o['widget_config']['linkcat-'.$category->term_id] = $widget_settings;
                // save lookup in original "links-*"
                $this->p->o['widget_config'][$widget_id]['lookup'][] = 'linkcat-'.$category->term_id;
              }
            }
          }
        } else {
          $this->p->o['widget_config'][$widget_id] = $widget_settings;
        }
      }
    }

    update_option($this->p->name, $this->p->o);
    
    return;
  }

} // class WPBTBucketsAdmin
?>
