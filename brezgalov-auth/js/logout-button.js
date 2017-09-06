jQuery('#brau-logout').on('click', function(){
		jQuery.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'brau_logout'
			},
			success: function(data) {
				if (data.success) {
					location.reload();
				} else {
					console.log(data);
				}
			},
			error: function (data) {
				console.log(data);
			}
		});
	});