jQuery(window).ready(function(){
	jQuery('#brau-reset-submit').on('click', function(){
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'brau_get_password',
				user_email: jQuery('#brau-reset-email').val()
			},
			success: function(data) {
				if (data.success) {
					jQuery('#l-brau-error-alert').slideUp();
					if (data.msg) {
						jQuery('#gp-brau-success-alert').text(data.msg);
						jQuery('#gp-brau-success-alert').slideDown();
					}
				} else {
					jQuery('#gp-brau-error-alert').text(data.code + ': ' + data.msg);
					jQuery('#gp-brau-error-alert').slideDown();
				}
			},
			error: function(data) {
				console.log(data);
			}
		});
	});
});