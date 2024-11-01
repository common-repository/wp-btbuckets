<div class="wrap">
  <h2><?php echo sprintf(__('%s Options', $this->p->name), $this->p->name_proper); ?></h2>
  <div>
    <a href="<?php echo preg_replace('/&wpbtb-page=[^&]*/', '', $_SERVER['REQUEST_URI']).'&wpbtb-page=generic'; ?>"><?php _e('General Configuration', $this->p->name); ?></a>
    &nbsp;|&nbsp;
    <a href="<?php echo preg_replace('/&wpbtb-page=[^&]*/', '', $_SERVER['REQUEST_URI']).'&wpbtb-page=import-export'; ?>"><?php _e('Import/Export', $this->p->name); ?></a>
    &nbsp;|&nbsp;
    <a href="<?php echo $this->p->homepage; ?>"><?php _e('Documentation', $this->p->name); ?></a>
  </div>
  <div style="clear:both;"></div>
  <div class="wpbtb_admin_main"> 