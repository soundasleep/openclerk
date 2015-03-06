$(document).ready(function() {
  $(document).find("#monthly").change(function(e) {
    $(document).find("#yearly").val(0);
  });
  $(document).find("#yearly").change(function(e) {
    $(document).find("#monthly").val(0);
  });
});
