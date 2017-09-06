<?php if(!is_user_logged_in()): ?>
	<?php 
		wp_enqueue_style('brau-css');
	?>

	<a class="brau-social-link" href="<?php echo brau_generate_login_vk_link() ?>">
		<img src="<?php echo plugins_url('../images/socials/vk-social-logotype.svg', __FILE__) ?>" />
	</a>
	<?php brau_handle_login_vk_link_redirect($redirect); ?>
<?php endif; ?>