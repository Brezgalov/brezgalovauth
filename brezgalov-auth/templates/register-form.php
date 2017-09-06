<?php 
	wp_enqueue_script('brau-register-form-js');
	wp_enqueue_style('brau-css');
?>

<form id="brau-register-form">
	<div class="form-group">
		<label>
			<?php _e('Login', 'brau'); ?>
			<span class="required-sign"> * </span>
		</label>
		<input 
			type="text" 
			class="form-control"
			id="r_user_name"
			name="user_name"
			placeholder="<?php _e('Choose your login')?>" 
		/>
	</div>
	<div class="form-group">
		<label>
			<?php _e('Email', 'brau'); ?>
			<span class="required-sign"> * </span>
		</label>
		<input 
			type="text" 
			class="form-control"
			id="r_user_email"
			name="user_email"
			placeholder="<?php _e('Specifiy your email')?>" 
		/>
	</div>
	<div class="form-group">
		<label>
			<?php _e('User name', 'brau'); ?>
			<span class="required-sign"> * </span>
		</label>
		<input 
			type="text" 
			class="form-control"
			id="r_nickname"
			name="nickname"
			placeholder="Enter your name" 
		/>
	</div>
	<div class="form-group">
		<label>
			<?php _e('Password', 'brau'); ?>
			<span class="required-sign"> * </span>
		</label>
		<input 
			type="password" 
			class="form-control"
			id="r_user_pass"
			name="user_pass"
			placeholder="Set your password" 
		/>
	</div>
	<div class="form-inline">
		<label for="r_show"><?php _e('Show password', 'brau'); ?></label>
		<input type="checkbox" id="r_show" class="form-control"/>
		<button class="btn btn-default" id="brau-register-form-submit" type="button">
			<?php _e('Register', 'brau'); ?>
		</button>
	</div>
	<div class="form-group alert-group">
		<label id="r-brau-error-alert" class="error"></label>
		<label id="r-brau-success-alert" class="success"></label>
	</div>
</form>