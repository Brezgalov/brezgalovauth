jQuery(window).ready(function(){
	jQuery('#brau-register-form-submit').on('click', function(){
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'brau_register',
				user_name: jQuery('#r_user_name').val(),
				user_pass: jQuery('#r_user_pass').val(),
				user_email: jQuery('#r_user_email').val(),
				nickname: jQuery('#r_nickname').val()
			},
			success: function(data) {
				if (data.success) {
					jQuery('#r-brau-error-alert').slideUp();
					if (data.msg) {
						jQuery('#r-brau-success-alert').text(data.msg);
						jQuery('#r-brau-success-alert').slideDown();
					}
				} else {
					jQuery('#r-brau-error-alert').text(data.code + ': ' + data.msg);
					jQuery('#r-brau-error-alert').slideDown();
				}
			},
			error: function(data) {
				console.log(data);
			}
		});
	});

	jQuery('#r_user_pass').keypress(function(event){
		if (event.keyCode == 13 || event.which == 13) {
	        jQuery('#brau-register-form-submit').click();
	        event.preventDefault();
	    }
	});

	jQuery('#r_show').on('click', function(){
		if (jQuery(this).prop('checked')) {
			jQuery('#r_user_pass').attr('type', 'text');
		} else {
			jQuery('#r_user_pass').attr('type', 'password');
		}
	});
});