function switch_to_signup() {
	$(".form-login").css("display", "none");
	$(".form-signup").css("display", "block");
}

function switch_to_login() {
	$(".form-login").css("display", "block");
	$(".form-signup").css("display", "none");
}

$(function() {

		$("#switch_to_signup").click(function() {
			switch_to_signup();
			});

		$("#switch_to_login").click(function() {
			switch_to_login();
			});

});

