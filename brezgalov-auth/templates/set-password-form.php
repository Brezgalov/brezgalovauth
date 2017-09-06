<?php 
	wp_enqueue_script('brau-set-password-form-js');
	wp_enqueue_style('brau-css');
?>

<form id="brau-reset-password-form">
	<input type="hidden" id="brau_sp_key" value="<?php echo (isset($_GET['key']))? $_GET['key'] : '' ?>"/>
	<input type="hidden" id="brau_sp_login" value="<?php echo (isset($_GET['login']))? $_GET['login'] : '' ?>"/>
	<div class="form-group">
		<label for="brau-new-password"><?php _e('New password:', 'brau'); ?></label>
		<input 
			id="brau-new-password" 
			name="password" 
			type="password"
			placeholder="<?php _e('Enter new password', 'brau'); ?>" 
			class="form-control"
		/>
	</div>
	<div class="form-group">
		<label for="brau-new-password-confirm"><?php _e('Password confirmation:', 'brau'); ?></label>
		<input 
			id="brau-new-password-confirm" 
			name="password_confirm" 
			type="password"
			placeholder="<?php _e('Confirm new password', 'brau'); ?>" 
			class="form-control"
		/>
	</div>
	<div class="form-inline text-right">
		<button type="button" class="btn btn-default" id="brau-set-submit">
			<?php _e('Set new password'); ?>
		</button>
	</div>
	<div class="form-group alert-group">
		<label id="sp-brau-error-alert" class="error"></label>
		<label id="sp-brau-success-alert" class="success"></label>
	</div>
</form>