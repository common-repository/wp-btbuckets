jQuery(document).ready(function($) {
  // bind custom onchange event to each widget mode drop down
  $('.wpbtb_widget_mode').live('change', function(e) {
    var select_val = $(this).val();
    var start_hidden = $(this).nextAll('.start-hidden');
    var start_visible = $(this).nextAll('.start-visible');
    if (select_val == 'start_hidden') {
      start_hidden.removeClass('wpbtb-hide').addClass('wpbtb-show');
      start_visible.removeClass('wpbtb-show').addClass('wpbtb-hide');      
    } else {
      start_hidden.removeClass('wpbtb-show').addClass('wpbtb-hide');
      start_visible.removeClass('wpbtb-hide').addClass('wpbtb-show');      
    }
  });
});
