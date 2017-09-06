if (VK) {
	VK.Widgets.Auth(
		"vk_auth", 
		{
			onAuth: function(vkData) {
				vkData.action = 'brau_login_vk';
				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: vkData,
					success: function(data) {
						if (data.success) {
							jQuery('#vk-brau-error-alert').slideUp();
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
										}
										if (brau_login.reload) {
											window.location.reload();
										}
									},
									brau_login.timeout
								);
							}
							if (data.msg) {
								jQuery('#vk-brau-success-alert').text(data.msg);
								jQuery('#vk-brau-success-alert').slideDown();
							}
						} else {
							jQuery('#vk-brau-success-alert').slideUp();
							jQuery('#vk-brau-error-alert').text(data.code + ': ' + data.msg);
							jQuery('#vk-brau-error-alert').slideDown();
						}
					},
					error: function(data) {
						console.log(data);
					}
				});
			}
		}
	);
}