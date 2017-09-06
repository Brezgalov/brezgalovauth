jQuery(window).ready(function(){
	jQuery('#brau-set-submit').on('click', function(){
		if (jQuery('#brau-new-password-confirm').val() != jQuery('#brau-new-password').val()) {
			jQuery('#sp-brau-error-alert').text('Passwords not match');
			jQuery('#sp-brau-error-alert').slideDown();
			return false;
		}

		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'brau_set_password',
				password: jQuery('#brau-new-password').val(),
				password_confirm: jQuery('#brau-new-password-confirm').val(),
				key: jQuery('#brau_sp_key').val(),
				login: jQuery('#brau_sp_login').val()
			},
			success: function(data) {
				if (data.success) {
					jQuery('#sp-brau-error-alert').slideUp();
					if (data.msg) {
						jQuery('#sp-brau-success-alert').text(data.msg);
						jQuery('#sp-brau-success-alert').slideDown();
					}
				} else {
					jQuery('#sp-brau-error-alert').text(data.code + ': ' + data.msg);
					jQuery('#sp-brau-error-alert').slideDown();
				}
			},
			error: function(data) {
				console.log(data);
			}
		});
	});

	jQuery('#brau-new-password-confirm, #brau-new-password').on('keyup', function(){
		if (jQuery('#brau-new-password-confirm').val() != jQuery('#brau-new-password').val()) {
			jQuery('#sp-brau-error-alert').text('Passwords not match');
			jQuery('#sp-brau-error-alert').slideDown();
		} else {
			jQuery('#sp-brau-error-alert').slideUp();
			jQuery('#sp-brau-error-alert').text('');
		}
	}); 
});