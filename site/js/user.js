/**
 * E-mail/password functionality.
 */
$(document).ready(function() {
  $(".show-password-form a").click(function() {
    $(".show-password-form").hide();
    $("form.add-password-form").show();
  })
});
