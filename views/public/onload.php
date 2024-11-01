<script type="text/javascript" language="Javascript">
  var wpbtb_hide_list = new Array();
  <?php foreach ($hide_list as $bucket => $list): ?>
  <?php if (count($list) > 0): ?>
  wpbtb_hide_list["<?php echo $bucket ?>"] = ["<?php echo implode('","', $list) ?>"];
  <?php else: ?>
  wpbtb_hide_list["<?php echo $bucket ?>"] = new Array();
  <?php endif; ?>
  <?php endforeach; ?>

  var wpbtb_show_list = new Array();
  <?php foreach ($show_list as $bucket => $list): ?>
  <?php if (count($list) > 0): ?>
  wpbtb_show_list["<?php echo $bucket ?>"] = ["<?php echo implode('","', $list) ?>"];
  <?php else: ?>
  wpbtb_show_list["<?php echo $bucket ?>"] = new Array();
  <?php endif; ?>
  <?php endforeach; ?>
  
  var user_buckets = $BTB.getAllUserBuckets();
  try {
    console.log('user_buckets: ', user_buckets);
  } catch(e) {}

  for (var b in user_buckets) {
    try {
      console.log('bucket: ', user_buckets[b]);
    } catch(e) {}
    if (user_buckets[b] in wpbtb_hide_list) {
      var current_bucket = user_buckets[b];
      var widgets_to_hide = wpbtb_hide_list[current_bucket];
      try {
        console.log('widgets_to_hide: ', widgets_to_hide);
      } catch(e) {}
      for (var i in widgets_to_hide) {
        var w_id = widgets_to_hide[i];
        var e;
        while (e = document.getElementById(w_id)) {
          e.style.display = "none";
          e.id = w_id + '-hidden';
        }
      }
    }
    if (user_buckets[b] in wpbtb_show_list) {
      var current_bucket = user_buckets[b];
      var widgets_to_show = wpbtb_show_list[current_bucket];
      try {
        console.log('widgets_to_show: ', widgets_to_show);
      } catch(e) {}
      for (var i in widgets_to_show) {
        var w_id = widgets_to_show[i];
        var e;
        while (e = document.getElementById(w_id)) {
          e.style.display = "block";
          e.id = w_id + '-shown';
        }
      }
    }
  }
</script>
