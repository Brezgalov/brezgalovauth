<?php 
	wp_enqueue_script('brau-get-password-form-js');
	wp_enqueue_style('brau-css');
?>

<form id="brau-reset-password-form">
	<div class="form-group">
		<label for="brau-login-form"><?php _e('Email:'); ?></label>
		<input 
			id="brau-reset-email" 
			name="email" 
			placeholder="<?php _e('Enter your email'); ?>" 
			class="form-control"
		/>
	</div>
	<div class="form-inline text-right">
		<button type="button" class="btn btn-default" id="brau-reset-submit">
			<?php _e('Reset password'); ?>
		</button>
	</div>
	<div class="form-group alert-group">
		<label id="gp-brau-error-alert" class="error"></label>
		<label id="gp-brau-success-alert" class="success"></label>
	</div>
</form>