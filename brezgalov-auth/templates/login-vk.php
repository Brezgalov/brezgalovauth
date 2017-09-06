<?php 
	wp_enqueue_script('brau-login-vk-js');
	wp_enqueue_style('brau-css');
?>

<?php if(!is_user_logged_in()): ?>
	<script type="text/javascript" src="//vk.com/js/api/openapi.js?146"></script>
	<script type="text/javascript">
	  VK.init({apiId: <?php echo get_option('brau_vk_app_id'); ?>});
	</script>
	<div id="vk_auth"></div>
<?php endif; ?>