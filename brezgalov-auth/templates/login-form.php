<?php 
	wp_enqueue_script('brau-login-form-js');
	wp_enqueue_style('brau-css');
?>

<form id="brau-login-form">
	<input type="hidden" id="brau_login" value="<?php echo (isset($_GET['code']))? $_GET['code'] : '' ?>"/>
	<div class="form-group">
		<label for="user_login"><?php _e('Login', 'brau'); ?></label>
		<input 
			type="text" 
			id="l_user_login" 
			name="user_login" 
			class="form-control"
			placeholder="<?php _e('Enter your login', 'brau')?>" 
		/>
	</div>
	<div class="form-group">
		<label for="user_password"><?php _e('Password', 'brau'); ?></label>
		<input 
			type="password" 
			id="l_user_password" 
			name="user_password" 
			class="form-control"
			placeholder="<?php _e('Enter your password', 'brau')?>"
		/>
	</div>	
	<div class="form-inline">
		<label for="remember"><?php _e('Remember me', 'brau'); ?></label>
		<input type="checkbox" id="l_remember" name="remember" class="form-control"/>
		<button type="button" class="btn btn-default" id="brau-login-form-submit">
			<?php _e('Login'); ?>
		</button>
	</div>
	<div class="form-inline text-center">
		<a>
			<?php _e('Forgot your password?', 'brau'); ?>
		</a>
	</div>
	<div class="form-group alert-group">
		<label id="l-brau-error-alert" class="error"></label>
		<label id="l-brau-success-alert" class="success"></label>
	</div>
</form>