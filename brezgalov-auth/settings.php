<?php 
global $brau_options; // This is horrible, should be cleaned up at some point
$brau_options = array (
	'brau_activation_message' 	=> '<p>Hello, {{username}}</p>
							 	 	<p>You have been registered at {{sitename}}! Follow a link below to activate your account</p>
							 	 	<p>{{link}}</p>',
	'brau_activation_link' 	  	=> '/?code={{code}}',
	'brau_activation_link_text' => 'Activate my account!',
	'brau_require_activation' 	=> true,
	'brau_allow_password_reset'	=> true,
	'brau_scripts_enabled'		=> true,
	'brau_styles_enabled'		=> true,
	'brau_forgot_password_link'	=> '/',
	'brau_vk_app_id' => '',
	'brau_vk_app_secret' => '',
	'brau_ok_app_id' => '',
	'brau_ok_app_secret' => '',
	'brau_ok_app_pub_key' => '',
);

//whitelist options
add_filter('whitelist_options', 'brau_whitelist_options');
//register tab
add_action('admin_menu','brau_add_settings_tab');
//register settings
add_action('admin_init','brau_register_settings');

/**
 * Whitelists options
 */
function brau_whitelist_options($whitelist_options) {
	global $brau_options;
	// Add our options to the array
	$whitelist_options['brau'] = array_keys($brau_options);
	return $whitelist_options;
}

/**
 * Registers options
 */
function brau_register_settings() {
	global $brau_options;

	foreach ($brau_options as $name => $val) {
		add_option($name,$val);
	}
}

/**
 * Registers settings tab
 */
function brau_add_settings_tab() {
	if (function_exists('add_submenu_page')) {
		add_options_page(
			__('User auth settings', 'brau'),
			__('Auth', 'brau'),
			'manage_options',
			__FILE__,'brau_settings_page'
		);
	}
} 

/**
 * Renders settings page
 */
function brau_settings_page() { 
	?>
	<style>
		input[type="text"] {
			width:100%;
		}
	</style>
	<div class="wrap">
		<h2>
			<?php _e('Auth plugin options', 'brau'); ?>
		</h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field('brau-options'); ?>
			<table class="optiontable form-table">
				<tr valign="top">
					<th scope="row">
						<label for="brau_require_activation">
							<?php _e('Require user activation by email', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_require_activation" 
							type="checkbox" 
							id="brau_require_activation"
							<?php if(get_option('brau_require_activation')): ?>
								checked
							<?php endif; ?>
						/>
						<p class="description">
						<?php _e('This option enables sending activation email on registration and restricts authorization of non activated users. Previously registered users are considered activated', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_activation_message">
							<?php _e('Activation message', 'brau'); ?>
						</label>
					</th>
					<td>
						<?php 
						  	wp_editor( 
						  		get_option('brau_activation_message'), 
						  		'brau_activation_message', 
						  		$settings = array('textarea_name'=>'brau_activation_message') 
						  	); 
					  	?> 
						<p class="description">
						<?php _e('You can use such tags as {{sitename}}, {{username}} and {{link}} to paste your host name, user name and activation link', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_activation_link">
							<?php _e('Activation link', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
						name="brau_activation_link" 
						type="text" 
						id="brau_activation_link"
						value="<?php print(get_option('brau_activation_link')); ?>" 
					/>
						<p class="description">
						<?php _e('Set link leading to your login page, use {{code}} as parameter', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_activation_link_text">
							<?php _e('Activation link text', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_activation_link_text" 
							type="text" 
							id="brau_activation_link_text"
							value="<?php print(get_option('brau_activation_link_text')); ?>" 
						/>
						<p class="description">
							<?php _e('Define activation link text', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_forgot_password_link">
							<?php _e('Forgot password link', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_forgot_password_link" 
							type="text" 
							id="brau_forgot_password_link"
							value="<?php print(get_option('brau_forgot_password_link')); ?>" 
						/>
						<p class="description">
							<?php _e('Forgot password link', 'brau') ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Save Settings' ); ?>
			<input type="hidden" name="action" value="update"/>
			<input type="hidden" name="option_page" value="brau"/>
		</form>
	</div>
	<div class="wrap">
		<h2>
			<?php _e('VK', 'brau'); ?>
		</h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field('brau-options'); ?>
			<table class="optiontable form-table">
				<tr valign="top">
					<th scope="row">
						<label for="brau_vk_app_id">
							<?php _e('VK application Id', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_vk_app_id" 
							type="text" 
							id="brau_vk_app_id"
							<?php if(get_option('brau_vk_app_id')): ?>
								checked
							<?php endif; ?>
						/>
						<p class="description">
						<?php _e('Id of your VK application', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_vk_app_secret">
							<?php _e('VK application secret code', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_vk_app_secret" 
							type="text" 
							id="brau_vk_app_secret"
							<?php if(get_option('brau_vk_app_secret')): ?>
								checked
							<?php endif; ?>
						/>
						<p class="description">
						<?php _e('Secret code of your VK application', 'brau') ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Save Settings' ); ?>
			<input type="hidden" name="action" value="update"/>
			<input type="hidden" name="option_page" value="brau"/>
		</form>
	</div>
	<div class="wrap">
		<h2>
			<?php _e('Odnoklassniki', 'brau'); ?>
		</h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field('brau-options'); ?>
			<table class="optiontable form-table">
				<tr valign="top">
					<th scope="row">
						<label for="brau_ok_app_id">
							<?php _e('OK application Id', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_ok_app_id" 
							type="text" 
							id="brau_ok_app_id"
							<?php if(get_option('brau_ok_app_id')): ?>
								checked
							<?php endif; ?>
						/>
						<p class="description">
						<?php _e('Id of your OK application', 'brau') ?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="brau_ok_app_secret">
							<?php _e('OK application secret code', 'brau'); ?>
						</label>
					</th>
					<td>
						<input 
							name="brau_ok_app_secret" 
							type="text" 
							id="brau_ok_app_secret"
							<?php if(get_option('brau_ok_app_secret')): ?>
								checked
							<?php endif; ?>
						/>
						<p class="description">
						<?php _e('Secret code of your OK application', 'brau') ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Save Settings' ); ?>
			<input type="hidden" name="action" value="update"/>
			<input type="hidden" name="option_page" value="brau"/>
		</form>
	</div>
	<?php
}