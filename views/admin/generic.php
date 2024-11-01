<script type="text/javascript">
function confirm_reset() {
  var answer = confirm("<?php _e('All of your custom messages will also be reset.  Are you sure you want to reset all settings?', $this->p->name); ?>");
  if(answer)
    return true;
  else
    return false;
}

jQuery(document).ready(function($){
  $("#wpbtb_options_toggle_advanced").click(function(e){
    e.preventDefault();
    state = $(this).attr("state");
    if(state == "visible"){
      $(".advanced-option").fadeOut();
      $("#wpbtb_options_refresh_btb").fadeOut();
      $("#wpbtb_options_reset").fadeOut();
      $(this).attr("state", "hidden");
      $(this).attr("value", "Show Advanced Options" + String.fromCharCode(187));
      $.ajax({
        type    : "POST",
        url     : "admin-ajax.php",
        data    : { action : "<?php echo $this->p->name ?>", _ajax_nonce: "<?php echo wp_create_nonce($this->p->name) ?>", wpbtb_action : "hide_advanced_options" },
        success : function(resp){
          // do nothing visually
        },
        error   : function(resp){
          alert("Error:" + resp);
        }
      });
    }
    else{
      $(".advanced-option").fadeIn();
      $("#wpbtb_options_refresh_btb").fadeIn();
      $("#wpbtb_options_reset").fadeIn();
      $(this).attr("state", "visible");
      $(this).attr("value", "Hide Advanced Options" + String.fromCharCode(187));
      $.ajax({
        type    : "POST",
        url     : "admin-ajax.php",
        data    : { action : "<?php echo $this->p->name ?>", _ajax_nonce: "<?php echo wp_create_nonce($this->p->name) ?>", wpbtb_action : "show_advanced_options" },
        success : function(resp){
          // do nothing visually
        },
        error   : function(resp){
          alert("Error:" + resp);
        }
      });
    }
  });
});
</script>
<?php
  if ($this->p->o['show_advanced_options']) {
    $advanced_display = 'display:inline';
    $advanced_style = 'style="'.$advanced_display.'"';
    $advanced_toggle_text = __('Hide', $this->p->name);
    $advanced_toggle_state = 'visible';
  }
  else {
    $advanced_display = 'display:none';
    $advanced_style = 'style="'.$advanced_display.'"';
    $advanced_toggle_text = __('Show', $this->p->name);
    $advanced_toggle_state = 'hidden';
  }
?>
<form method="post">
  <?php wp_nonce_field($this->p->name) ?>
  <fieldset class="wpbtb_fieldset wpbtb_fieldset_alt">
    <h2><?php _e('Support this plugin!', $this->p->name) ?></h2>
    <p><?php echo $this->p->c->make_radio(
      $this->p->o['show_link'],
      'wpbtb_options_general[show_link]',
      true, true,
      __('Display \'Powered by WP BTBuckets\' link in the site footer', $this->p->name)) ?>
    </p>
    <p><?php echo $this->p->c->make_radio(
      $this->p->o['show_link'],
      'wpbtb_options_general[show_link]',
      false, false,
      sprintf(__('Do not display \'Powered by WP BTBuckets\' link.  I will %sdonate%s and/or write about this plugin', $this->p->name), '<a href="http://omninoggin.com/donate">', '</a>')) ?>
    </p>
    
    <h2><?php _e('General Configuration', $this->p->name) ?></h2>
    <p>
      <?php _e('Enable BTBuckets on Widgets', $this->p->name) ?>
      <?php echo $this->p->c->make_checkbox(
        $this->p->o['enable_smart_widgets'],
        true,
        array(
          'name' => 'wpbtb_options_general[enable_smart_widgets]',
        )
      ) ?>
    </p>
    <p><?php echo $this->p->c->make_textfield(
      $this->p->o[api_key],
      'wpbtb_options_general[api_key]', 'api-key',
      null, __('BTBuckets API Key', $this->p->name), '('.sprintf(__('Get this from your %sBTBuckets%s tags page', $this->p->name), '<a href="http://btbuckets.com/site/signup#utm_source=developer&utm_medium=plugin&utm_campaign=wordpress_thaya">', '</a>').')') ?>
    </p>
    <p class="advanced-option" <?php echo $advanced_style ?>>
      <?php _e('Debug Mode', $this->p->name) ?>
      <?php echo $this->p->c->make_checkbox(
        $this->p->o['debug'],
        false,
        array(
          'name' => 'wpbtb_options_general[debug]'
        )
      ) ?>
    </p>
    <p><?php echo $this->p->c->make_textfield(
      $this->p->o[btb_refresh_interval],
      'wpbtb_options_general[btb_refresh_interval]', 'btb-refresh-interval',
      null, __('BTBuckets Data Refresh Interval', $this->p->name), __('seconds', $this->p->name), true, $this->p->o['show_advanced_options']) ?>
    </p>
    <?php if (count($this->p->o['buckets'])): ?>
    <h2><?php _e('Available Buckets', $this->p->name) ?></h2>
    <ol>
    <?php foreach ($this->p->o['buckets'] as $bucket_name => $bucket_config): ?>
    <li><?php echo $bucket_config['bucket_name'] ?></li>
    <?php endforeach; ?>
    </ol>
    <?php _e('Don\'t see your bucket on this list? Try checking your API key and then click the \'Refresh BTBuckets Data\' button in advanced options below.') ?>
    <?php endif; ?>
    <p class="submit">
      <input type="submit" name="wpbtb_options_general_submit" value="<?php _e('Update Options', $this->p->name) ?> &#187;" />
      <?php if(isset($_GET['debug']) && $_GET['debug'] > 0): ?>
        <input type="submit" name="wpbtb_options_upgrade" value="<?php _e('Upgrade Options', $this->p->name) ?> &#187;" />
      <?php endif; ?>
      <input type="submit" id="wpbtb_options_refresh_btb" name="wpbtb_options_refresh_btb" value="<?php _e('Refresh BTBuckets Data', $this->p->name) ?> &#187;" <?php echo $advanced_style ?> />
      <input type="submit" id="wpbtb_options_reset" name="wpbtb_options_reset" value="<?php _e('Reset ALL Options', $this->p->name) ?> &#187;" onclick="return confirm_reset()" <?php echo $advanced_style ?> />
      <input type="button" id="wpbtb_options_toggle_advanced" name="wpbtb_options_toggle_advanced" state="<?php echo $advanced_toggle_state ?>" value="<?php echo $advanced_toggle_text ?> <?php _e('Advanced Options', $this->p->name) ?> &#187;" />
    </p>
  </fieldset>
</form>
