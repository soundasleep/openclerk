/**
 * 'Your transactions' page
 */
$(document).ready(function() {
  $("select#exchange_list").change(function() {
    var exchange = $("select#exchange_list").val();
    $("select#account_id_list option").hide();
    $("select#account_id_list option.exchange-" + exchange).show();
    $("select#account_id_list option.all").show();
  });
  $("select#exchange_list").change();
});
