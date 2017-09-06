<?php
/**
 * @package Brezgalov Auth
 */
/*
Plugin Name: Brezgalov Auth
Description: Custom user authorization for WP
Version: 0.0.1
Author: Oleg Brezgalov
Author URI: https://brezgalov.ru
License: MIT
*/

//@TODO LIST
//Login - Done
//Login VK - Done
//Logout - Done
//Register - Done
//Add activation email to register - Done
//Reset - Done
//Get profile - 
//Set profile -
//Forms && shortcodes - added
//Plugin settings -  added

require('settings.php');
require('oauth/BasicAuthHelper.php');
require('socialHelper.php');

//scripts
wp_register_script('brau-login-form-js', plugins_url('js/login-form.js', __FILE__), array('jquery'), null, true);
wp_register_script('brau-login-vk-js', plugins_url('js/login-vk.js', __FILE__), array('jquery'), null, true);
wp_register_script('brau-logout-button-js', plugins_url('js/logout-button.js', __FILE__), array('jquery'), null, true);
wp_register_script('brau-register-form-js', plugins_url('js/register-form.js', __FILE__), array('jquery'), null, true);
wp_register_script('brau-get-password-form-js', plugins_url('js/get-password-form.js', __FILE__), array('jquery'), null, true);
wp_register_script('brau-set-password-form-js', plugins_url('js/set-password-form.js', __FILE__), array('jquery'), null, true);
//styles
wp_register_style('brau-css', plugins_url( 'css/brezgalov-auth.css', __FILE__ ));
//shortcodes
add_shortcode('brau-login', 'brau_render_login_form');
add_shortcode('brau-logout', 'brau_render_logout_button');
add_shortcode('brau-register', 'brau_render_register_form');
add_shortcode('brau-get-password', 'brau_render_get_password_form');
add_shortcode('brau-set-password', 'brau_render_set_password_form');
add_shortcode('brau-login-vk', 'brau_render_login_vk');
add_shortcode('brau-login-link-vk', 'brau_render_login_link_vk');
add_shortcode('brau-login-link-ok', 'brau_render_login_link_ok');
//ajax no priv
add_action('wp_ajax_nopriv_brau_login_vk', 'brau_login_vk_ajax');
add_action('wp_ajax_nopriv_brau_login', 'brau_login_ajax');
add_action('wp_ajax_nopriv_brau_register', 'brau_register_ajax');
add_action('wp_ajax_nopriv_brau_get_password', 'brau_retrieve_password_ajax');
add_action('wp_ajax_nopriv_brau_set_password', 'brau_do_password_reset_ajax');
//ajax priv
add_action('wp_ajax_brau_update_user', 'brau_update_user_ajax');
add_action('wp_ajax_brau_logout', 'brau_logout_ajax');
//other
add_action('init', 'brau_block_init');
add_action('login_form_rp', 'brau_redirect_to_custom_password_reset');
add_action('login_form_resetpass', 'brau_redirect_to_custom_password_reset');

// Localiztion with language files
function brau_custom_langs_i18n() {
    var_dump(
    	load_plugin_textdomain( 'brau', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ),
    	__('This is the test')
	);
}
add_action( 'init', 'brau_custom_langs_i18n' );

function brau_login_ajax() {
	echo json_encode(brau_login($_POST));die();
}

function brau_logout_ajax() {
	wp_logout();
	echo json_encode(brau_return(0,'',true));die();
}

function brau_register_ajax() {
	echo json_encode(brau_register($_POST));die();
}

function brau_retrieve_password_ajax() {
	echo json_encode(brau_retrieve_password($_POST));die();
}

function brau_do_password_reset_ajax() {
	echo json_encode(brau_do_password_reset($_POST));die();
}

function brau_update_user_ajax() {
	echo json_encode(brau_update_user($_POST));die();
}

function brau_activate_user_ajax() {
	echo json_encode(brau_activate_user($_POST));die();
}
function brau_login_vk_ajax() {
	echo json_encode(brau_login_vk($_POST));die();
}

/**
 * Returns formated array
 */
function brau_return($code, $msg='', $success=false) {
	return [
		'success' => $success,
		'msg' => $msg,
		'code' => $code,
	];
}

/**
 * Renders template
 */
function brau_render_html($template_name, $attributes = null) {
	if ( ! $attributes ) {
        $attributes = array();
    }
    extract($attributes, EXTR_SKIP);
    ob_start();
    require( 'templates/' . $template_name . '.php');
    $html = ob_get_contents();
    ob_end_clean();
 
    return $html;
}

/**
 * Logs user in
 */
function brau_login($data) {
	if (!isset($data['user_login']) || !$data['user_login']) {
		return brau_return(
			1101, 
			__('User login not set', 'brau')
		);
	}
	if (!isset($data['user_password']) || !$data['user_password']) {
		return brau_return(
			1102, 
			__('User password not set', 'brau')
		);
	}
    if (!isset($data['remember'])) {
		$_POST['remember'] = false;
	}
	if (get_option('brau_require_activation') && !brau_user_activated($data['user_login'], 'login')) {
		$activationResult = brau_activate_user($data);
		if (!$activationResult['success']) {
			return $activationResult;
		}
    } 

	$data['user_login'] = esc_attr($data['user_login']);
	$data['user_password'] = esc_attr($data['user_password']);
	$data['remember'] = (bool)$data['remember'];

	$user = wp_signon($data, false );
    if ( is_wp_error($user) ) {
    	return brau_return(
			1113, 
			$user->get_error_message()
		);
    }

    return brau_return(0, __('Log in is successfull', 'brau'), true);
}

/**
 * Register a user
 */
function brau_register($data) {
	if (!isset($data['user_name'])) {
		return brau_return(
			1201, 
			__('Username not set', 'brau')
		);
	}
	if (!isset($data['user_email'])) {
		return brau_return(
			1202, 
			__('Email not set', 'brau')
		);
	}
	if (!isset($data['user_pass'])) {
		return brau_return(
			1203, 
			__('Password not set', 'brau')
		);
	}
	if (email_exists(esc_attr($data['user_email']))) {
		return brau_return(
			1204, 
			__('Email has already been taken', 'brau')
		);
	}
	if (!is_email(esc_attr($data['user_email']))) {
		return brau_return(
			1205, 
			__('Email is invalid')
		);
	}
	if (username_exists($data['user_name'])) {
		return brau_return(
			1206, 
			__('Username has already been taken', 'brau')
		);
	}

	$rand='';
	$code='';
	$activated = 1;
	if (get_option('brau_require_activation')) {
		$rand = rand();
	    $code = brau_generate_token($data, $rand);
	    $activated = 0;

	    if (!$code) {
	    	return brau_return(
				1207, 
				__('Unable to generate user activation token', 'brau')
			);
	    }

	    $message = brau_generate_activation_email(
	    	esc_attr($data['user_name']),
	    	$code
    	);
    	if (
    		!wp_mail(
	    		esc_attr($data['user_email']), 
	    		__('Activation email', 'brau'), 
	    		$message
			)
		) {
    		return brau_return(
				1208, 
				__('Unable to send email', 'brau')
			);
		}
	}

	$userdata = array(
	    'user_pass' => esc_attr($data['user_pass']),
	    'user_login' => esc_attr($data['user_email']),//esc_attr($data['user_name']),
	    'first_name' => (isset($$data['first_name']))? esc_attr($data['first_name']) : '',
	    'last_name' => (isset($data['last_name']))? esc_attr($data['last_name']) : '',
	    'nickname' => (isset($data['nickname']))? esc_attr($data['nickname']) : esc_attr($data['user_name']),
	    'user_email' => esc_attr($data['user_email']),
	    'user_url' => (isset($data['user_url']))? esc_attr($data['user_url']) : '',
	    'role' => 'subscriber',
	    'display_name' => esc_attr($data['user_name']),
	);
    $user = wp_insert_user( $userdata );

	if (is_wp_error($user)) {
        return brau_return(
			1213, 
			$user->get_error_message()
		);
    }

    add_user_meta($user, 'brau-activated', $activated, true);
    add_user_meta($user, 'brau-rand', $rand, true);
    add_user_meta($user, 'brau-activation_key', $code, true);

    $msg = __('Registration successfull! You can log in now.');
    if (get_option('brau_require_activation')) {
    	$msg = __('Registration successfull! Activation message has been sent to your email.');
    }
    return brau_return(0, '', true);
}

/**
 * Activates user manualy
 */
function brau_activate_user($data) {
	if (!isset($data['code']) || !isset($data['user_login'])) {
		return brau_return(
    		1104,
    		__('Not enough data', 'brau')
		);
	}
	$tokenAccepted = brau_check_token($data['code'], $data['user_login'], 'login');
	if ($tokenAccepted) {
		$userData = get_user_by('login', $data['user_login']);
		update_user_meta($userData->ID, 'brau-activated', 1);
		update_user_meta($userData->ID, 'brau-rand', rand());
		update_user_meta($userData->ID, 'brau-activation_key', '');    		
		return brau_return(0, '', true);
	} else {
		return brau_return(
    		1103,
    		__('Activation token not accepted', 'brau')
		);
	}
}

/**
 * Checks if user activated 
 */
function brau_user_activated($identifier, $type='login') {
	$userId = $identifier;
	$userData = get_user_by($type, $identifier);

	if (empty($userData))
		return false;

	$userMeta = get_user_meta($userData->ID);

	if (empty($userMeta))
		return false;

	return !(isset($userMeta['brau-activated']) && $userMeta['brau-activated'][0] == '0') || 
		!(isset($userMeta['brau-activation_key']) && !empty($userMeta['brau-activation_key'][0]));
}

/**
 * Generates activation token
 */
function brau_generate_token($data, &$rand) {
	if (!isset($data['user_name']) || !isset($data['user_email'])) {
		return false;
	}
	if (!$rand)
		$rand = rand();
    return wp_hash_password(esc_attr($data['user_name']).$rand.esc_attr($data['user_email']));
}

/**
 * Generates activation email
 */
function brau_generate_activation_email($userName, $code) {
	$siteName = get_bloginfo('name');
	$hostName = '<a href="'.get_site_url().'">'.$siteName.'</a>';
	$link = '<a href="'.get_site_url().get_option("brau_activation_link").'">'.get_option('brau_activation_link_text').'</a>';
	$message = get_option('brau_activation_message');

	$message = str_replace("{{username}}", $userName, $message);
	$message = str_replace("{{sitename}}", $hostName, $message);
	$message = str_replace("{{link}}", $link, $message);
	$message = str_replace("{{code}}", $code, $message);

	return $message;
}

/**
 * Checks if token is ok
 */
function brau_check_token($token, $userId, $idType, $rand=NULL) {
	$userData = get_user_by($idType, $userId);
	if (!$userData)
		return false;
	$userMeta = get_user_meta($userData->ID);
	if (!$userMeta)
		return false;
	if (isset($userMeta['brau-rand'][0]) && !$rand) {
		$rand = $userMeta['brau-rand'][0];
	}

	$wp_hasher = new PasswordHash(8, TRUE);
	$newToken = $userData->user_login.$rand.$userData->user_email;

    return $wp_hasher->CheckPassword($newToken, $token);
}

/**
 * Blocks default wp login routes
 */
function brau_block_init() {
	global $pagenow;

	
	
	if ( is_admin() && !current_user_can('administrator') && ! ( defined('DOING_AJAX') && DOING_AJAX ) ) {
		wp_redirect(home_url());
		exit;
	}

    if($pagenow == 'wp-login.php') {
        wp_redirect(home_url());
        exit();
    }
}

/**
 * Sends and email with reset password link
 */
function brau_retrieve_password($data) {
    global $wpdb, $current_site;

    if (get_option('brau_require_activation') && !brau_user_activated($data['user_email'], 'email')) {
		return brau_return(
			1300,
			__('User not activated', 'brau')
		);
    } 

    if (!isset($data['user_email'])) {
		return brau_return(
    		1301,
    		__('User email not set', 'brau')
		);
	}
	$user_email = $data['user_email'];

    $userData = [];

    if (get_option('brau_require_activation') && !brau_user_activated($user_email, 'email')) {
    	return brau_return(
    		1300,
    		__('User not activated', 'brau')
		);
    }

    if (empty($user_email)) {
    	return brau_return(
    		1301,
    		__('User login not set', 'brau')
		);
    } else {
        $userData = get_user_by('email', trim($user_email));
    } 

    if (empty($userData)) {
    	return brau_return(
    		1302,
    		__('User data not found', 'brau')
		);
    }

    do_action('lostpassword_post');

    // redefining user_login ensures we return the right case in the email
    $user_login = $userData->user_login;
    $user_email = $userData->user_email;

    // do_action('retreive_password', $user_login);  // Misspelled and deprecated
    do_action('retrieve_password', $user_login);

    // $allow = apply_filters('allow_password_reset', true, $userData->ID);

    if (!get_option('brau_allow_password_reset') ) {
    	return brau_return(
    		1303,
    		__('Password reset is forbiden', 'brau')
		);
    }
    else if ( is_wp_error($allow) ) {
    	return brau_return(
    		1313,
    		$allow->get_error_message()
		);
    }

    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
    if (empty($key)) {
        // Generate something random for a key...
        $key = wp_generate_password(20, false);
        do_action('retrieve_password_key', $user_login, $key);
        // Now insert the new md5 key into the db
        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
    }
    $message = __('Someone requested that the password be reset for the following account:', 'brau') . "\r\n\r\n";
    $message .= network_home_url( '/' ) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s', 'brau'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'brau') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:', 'brau') . "\r\n\r\n";
    $message .= '<' . network_site_url("/password-reset?key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

    if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
    else
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $title = sprintf( __('[%s] Password Reset', 'brau'), $blogname );

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);

    if ( $message && !wp_mail($user_email, $title, $message) ) {
    	return brau_return(
    		1304,
    		__('Email could not be sent', 'brau')
		);
    }

    return brau_return(0, '', true);
}

/**
 * Redirects to the custom password reset page, or the page
 * if there are errors.
 */
function brau_redirect_to_custom_password_reset() {
    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        // Verify key / login combo
        $user = check_password_reset_key($_REQUEST['key'], $_REQUEST['login']);
        if ( ! $user || is_wp_error($user) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                wp_redirect( home_url() );//?
            } else {
                wp_redirect( home_url() );//?
            }
            exit;
        }
 
        $redirect_url = home_url('password-reset');
        $redirect_url = add_query_arg( 'login', esc_attr($_REQUEST['login']), $redirect_url );
        $redirect_url = add_query_arg( 'key', esc_attr($_REQUEST['key']), $redirect_url );
 
        wp_redirect( $redirect_url );
        exit;
    }
}

/**
 * Resets the user's password if the password reset form was submitted.
 */
function brau_do_password_reset($data) {
	global $wpdb;

	if (get_option('brau_require_activation') && !brau_user_activated($data['login'], 'login')) {
		return brau_return(
			1300,
			__('User not activated', 'brau')
		);
    } 

	if (!isset($data['key'])) {
		return brau_return(
			1305,
			__('User key not set', 'brau')
		);
	}
	if (!isset($data['login'])) {
		return brau_return(
			1306,
			__('User login not set', 'brau')
		);
	}

	$user = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * 
			FROM $wpdb->users 
			WHERE user_activation_key = %s AND user_login = %s", 
			$data['key'], 
			$data['login']
		)
	);
 	if (!$user) {
    	return brau_return(
			1308,
			__('User not found', 'brau')
		);
    } 
    if (is_wp_error($user) ) {
    	return brau_return(
			1307,
			$user->get_error_message()
		);
    }
    if (!isset($data['password'])) {
    	return brau_return(
			1309,
			__('Password not set', 'brau')
		);
    }
    if (!isset($data['password_confirm'])) {
    	return brau_return(
			1310,
			__('Password confirmation not set', 'brau')
		);
    }	
    if ($data['password'] != $data['password_confirm']) {
    	return brau_return(
    		1311,
    		__('Passwords doesnt match', 'brau')
		);
    }

    reset_password($user, $data['password']);

    return brau_return(0, '', true);
}

/**
 * Writes down user data and metadata
 */
function brau_update_user($data) {
	//update rights check
	//self update activation check
	if (!isset($data['user_id'])) {
		return brau_return(
			1401,
			__('User ID not set', 'brau')
		);
	}
	if (!isset($data['user_data'])) {
		return brau_return(
			1402,
			__('User data not set', 'brau')
		);
	}

	$userData = $data['user_data'];
	$userData['ID'] = $data['user_id'];
	
	$user = wp_update_user($userData);

	if (is_wp_error($user)) {
        return brau_return(
			1413, 
			$user->get_error_message()
		);
    }

    if (isset($data['user_meta'])) {
    	foreach ($data['user_meta'] as $metaArray) {
    		if (isset($metaArray['prev']) && $metaArray['prev']) {
    			update_user_meta(
	    			$data['user_id'], 
	    			$metaArray['key'], 
	    			$metaArray['value'],
	    			$metaArray['prev']
				);
    		} else {
	    		update_user_meta(
	    			$data['user_id'], 
	    			$metaArray['key'], 
	    			$metaArray['value']
				);	
    		}
    		
    	}
    }

    return brau_return(0, '', true);
}

function brau_register_by_email($email, $userName = '', $firstName='', $lastName='') {
	if ($userName == '') {
		$userName = $email;
	}

	return brau_register([
		'user_email' => $email,
		'user_name' => $userName,
		'user_pass' => wp_generate_password(),
		'first_name' => $firstName,
		'last_name' => $lastName,
	]);	
}

function brau_render_login_form($attributes, $content = null) {
	$args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => true,
		], 
		$attributes
	);
	wp_localize_script('brau-login-form-js', 'brau_login', $args);
    return brau_render_html(
    	'login-form', 
    	$args	
	);
}

function brau_render_logout_button($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => true,
		], 
		$attributes
	);
	wp_localize_script('brau-logout-button-js', 'brau_logout', $args);
    return brau_render_html(
    	'logout-button', 
    	$args
	);
}

function brau_render_register_form($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => true,
		], 
		$attributes
	);
	wp_localize_script('brau-register-form-js', 'brau_register', $args);
    return brau_render_html(
    	'register-form', 
    	$args
	);
}

function brau_render_get_password_form($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => false,
		], 
		$attributes
	);
	wp_localize_script('brau-get-password-form-js', 'brau_get_password', $args);
    return brau_render_html(
    	'get-password-form', 
    	$args
	);
}

function brau_render_set_password_form($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => false,
		], 
		$attributes
	);
	wp_localize_script('brau-set-password-form-js', 'brau_set_password', $args);
    return brau_render_html(
    	'set-password-form', 
    	$args
	);
}

function brau_render_login_link_vk($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'timeout' => 0,
			'redirect' => '',
			'reload' => true,
		], 
		$attributes
	);
	// wp_localize_script('brau-login-vk-js', 'brau_login_vk', $args);
    return brau_render_html(
    	'login-link-vk', 
    	$args
	);
}

function brau_render_login_link_ok($attributes, $content = null) {
    $args = shortcode_atts(
		[
			'redirect' => ''
		], 
		$attributes
	);
	// wp_localize_script('brau-login-vk-js', 'brau_login_vk', $args);
    return brau_render_html(
    	'login-link-ok', 
    	$args
	);
}