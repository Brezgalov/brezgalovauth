<?php if(!is_user_logged_in()): ?>
	<?php 
		wp_enqueue_style('brau-css');
	?>

	<a class="brau-social-link" href="<?php echo brau_generate_login_ok_link() ?>">
		<img src="<?php echo plugins_url('../images/socials/odnoklassniki-logo.svg', __FILE__) ?>" />
	</a>
	<?php brau_handle_login_ok_link_redirect($redirect); ?>
<?php endif; ?>