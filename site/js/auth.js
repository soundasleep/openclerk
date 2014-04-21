/**
 * Authentication pages (login, signup)
 */
$(document).ready(function() {
  var callback = function(event) {
    var target = $("#openid_expand");
    if (target.is(":visible")) {
      target.hide();
    } else {
      target.show();
      $(target).find("input[type=text]").focus();
    }
    return false; // don't submit!
  };

  $("#openid").click(callback);
});

/**
 * Clicking an OpenID button fills out the OpenID URL field too before submitting.
 */
$(document).ready(function() {
  var callback = function(event) {
    var parent = $(event.target);
    $("input[name='openid']").val(parent.val());
  };

  $("button.openid-submit").click(callback);
  $("button.openid-submit").keypress(callback);

  // so pressing enter submits OpenID and not Google Accounts
  var callback2 = function(event) {
    if (event.which == 13) {
      $("#openid_manual_submit").click();
      return true;
    }
  };
  $("#openid_manual").keypress(callback2);
});

/**
 * OpenID/password switch
 */
$(document).ready(function() {
  $(".login-with-openid .password-openid-switch").click(function() {
    $(".login-with-openid").hide();
    $(".login-with-password").show();
    $(".email-required").show();
    $(".login-with-password input:first").focus();
    $("input#use_password").val(1);
    return false;   // stop event bubbling
  })
  $(".login-with-password .password-openid-switch").click(function() {
    $(".login-with-openid").show();
    $(".login-with-password").hide();
    $(".email-required").hide();
    $("input#use_password").val(0);
    return false;   // stop event bubbling
  })
});
