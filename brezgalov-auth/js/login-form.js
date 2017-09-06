jQuery(window).ready(function(){
	jQuery('#brau-login-form-submit').on('click', function(){
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'brau_login',
				user_login: jQuery('#l_user_login').val(),
				user_password: jQuery('#l_user_password').val(),
				remember: jQuery('#l_remember').prop('checked'),
				code: jQuery('#brau_login').val()
			},
			success: function(data) {
				if (data.success) {
					jQuery('#l-brau-error-alert').slideUp();

					if (data.msg) {
						jQuery('#l-brau-success-alert').text(data.msg);
						jQuery('#l-brau-success-alert').slideDown();
					}

					if (brau_login) {
						if (!brau_login.timeout) {
							brau_login.timeout = 0;
						}
						setTimeout(
							function() {
								if (brau_login.redirect) {
									window.location.replace(
										brau_login.redirect
									);
								} else {
									if (brau_login.reload && brau_login.reload != 'false') {
										window.location.reload();
									}
								}
								
							},
							brau_login.timeout
						);
					}
				} else {
					jQuery('#l-brau-success-alert').slideUp();
					jQuery('#l-brau-error-alert').text(data.code + ': ' + data.msg);
					jQuery('#l-brau-error-alert').slideDown();
				}
			},
			error: function(data) {
				console.log(data);
			}
		});
	});

	jQuery('#l_user_login').keypress(function(event){
		if (event.keyCode == 13 || event.which == 13) {
	        jQuery('#user_password').focus();
	        event.preventDefault();
	    }
	});

	jQuery('#l_user_password').keypress(function(event){
		if (event.keyCode == 13 || event.which == 13) {
	        jQuery('#brau-login-form-submit').click();
	        event.preventDefault();
	    }
	});
});