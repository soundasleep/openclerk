/**
 * 'Your transactions' page
 */
$(document).ready(function() {
  $("select#exchange_list").change(function() {
    var exchange = $("select#exchange_list").val();
    if (exchange == "") {
      $("select#account_id_list").val("");
      $("select#account_id_list").attr('disabled', true);
    } else {
      $("select#account_id_list").attr('disabled', false);
      $("select#account_id_list option").hide();
      $("select#account_id_list option.exchange-" + exchange).show();
      $("select#account_id_list option.all").show();
      if (!$("select#account_id_list option:selected").is(":visible")) {
        $("select#account_id_list").val("");
      }
    }
  });
  $("select#exchange_list").change();
});
