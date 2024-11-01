<script type="text/javascript">
function confirm_import() {
  var answer = confirm("<?php printf(__('All of your current %s options will be overwritten by the imported value.  Are you sure you want to overwrite all settings?', $this->p->name), $this->p->name_proper) ?>");
  if(answer)
    return true;
  else
    return false;
}
</script>
<h2><?php _e('Export', $this->p->name) ?></h2>
<form method="post">
  <fieldset class="wpbtb_fieldset wpbtb_fieldset_alt">
    <p><?php printf(__('This functionality will dump your entire %s options into a file.', $this->p->name), $this->p->name_proper) ?></p>
    <p class="submit">
      <input type="submit" id="wpbtb_options_export" name="wpbtb_options_export" value="<?php _e('Export Options', $this->p->name) ?> &#187;" />
    </p>
  </fieldset>
</form>

<h2><?php _e('Import', $this->p->name) ?></h2>
<form method="post" enctype="multipart/form-data">
  <?php echo wp_nonce_field($this->p->name) ?>
  <fieldset class="wpbtb_fieldset wpbtb_fieldset_alt">
    <p><?php printf(__('This functionality will restore your entire %s options from a file.', $this->p->name), $this->name_proper) ?><br/>
    <strong><?php _e('Make sure you\'ve done an export and backup the exported file before you try this!', $this->p->name) ?></strong></p>
    <input type="file" id="wpbtb_options_import_file" name="wpbtb_options_import_file" size="40" /><br/>
    <?php echo wp_nonce_field($this->p->name) ?>
    <p class="submit">
      <input type="submit" id="wpbtb_options_import" name="wpbtb_options_import" value="<?php _e('Import Options', $this->p->name) ?> &#187;" onclick="return confirm_import()" />
    </p>
  </fieldset>
</form>