<?php if (count($this->p->o['buckets'])): ?>
  <div class="wpbtb-wc">
    <div class="wpbtb-wc-header">
      <h2><?php _e('BTBuckets Options', $this->p->name) ?></h2>
    </div>
    <div class="wpbtb-wc-body">
      <strong><?php _e('Widget Mode') ?>:</strong>
      <?php echo $this->p->c->make_select(
        $this->p->o['widget_modes'],
        $this->p->o['widget_config'][$wid]['widget_mode'],
        'start_visible',
        array(
          'name' => 'wpbtb_widget_config[widget_config]['.$wid.'][widget_mode]',
          'class' => 'wpbtb_widget_mode'
        )
      ) ?>
      <?php if ($this->p->o['widget_config'][$wid]['widget_mode'] == 'start_hidden'): ?>
        <?php $start_hidden_class = 'wpbtb-show'; ?>
        <?php $start_visible_class = 'wpbtb-hide'; ?>
      <?php else: ?>
        <?php $start_hidden_class = 'wpbtb-hide'; ?>
        <?php $start_visible_class = 'wpbtb-show'; ?>
      <?php endif; ?>
      <div class="start-hidden <?php echo $start_hidden_class ?>">
        <table class="wpbtb-wc-table">
          <tr>
            <th><?php _e('Bucket Name', $this->p->name)?></th>
            <th><?php _e('Show', $this->p->name)?></th>
          </tr>
          <?php foreach ($this->p->o['buckets'] as $bucket_name => $bucket_config): ?>
            <tr>
              <td><?php echo $bucket_config['bucket_name'] ?></td>
              <td>
                <?php echo $this->p->c->make_checkbox(
                  $this->p->o['widget_config'][$wid]['to_show'][$bucket_name],
                  false,
                  array(
                    'name' => 'wpbtb_widget_config[widget_config]['.$wid.'][to_show]['.$bucket_name.']'
                  )
                ) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <div class="start-visible <?php echo $start_visible_class ?>">
        <table class="wpbtb-wc-table">
          <tr>
            <th><?php _e('Bucket Name', $this->p->name)?></th>
            <th><?php _e('Hide', $this->p->name)?></th>
          </tr>
          <?php foreach ($this->p->o['buckets'] as $bucket_name => $bucket_config): ?>
            <tr>
              <td><?php echo $bucket_config['bucket_name'] ?></td>
              <td>
                <?php echo $this->p->c->make_checkbox(
                  $this->p->o['widget_config'][$wid]['to_hide'][$bucket_name],
                  false,
                  array(
                    'name' => 'wpbtb_widget_config[widget_config]['.$wid.'][to_hide]['.$bucket_name.']'
                  )
                ) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>