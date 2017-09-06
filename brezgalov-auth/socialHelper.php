<?php 

use Brau\oAuth\BasicAuthHelper;

/**
 * Encodes string
 */
function brau_json_encode($inputStr) {
    return strtr(base64_encode($inputStr), '+/=', '-_,');
}

/**
 * Decodes string
 */
function brau_json_decode($inputStr) {
    return base64_decode(strtr($inputStr, '-_,', '+/='));
}

/**
 * Return current url
 */
function brau_current_url() {
	global $wp;

	return add_query_arg( 
		$wp->query_string, 
		'', 
		home_url($wp->request) 
	);

}

/**
 * Checks if GET params are valid
 */
function brau_check_social_login_code($baseCode) {
	if (isset($_GET['code']) && isset($_GET['state'])) {
		$code = $_GET['code'];
		$state = json_decode(brau_json_decode($_GET['state']), true);	

		if (!isset($state['brau_auth'])) {
			return brau_return(
				(int)$baseCode + 1,
				__('No auth identifier', 'brau')
			);
		}

		$return = brau_return(0, 'Success', true);
		$return['data'] = [
			'code' => $code,
			'state' => $state,
			'social' => $state['brau_auth'],
		];

		return $return;
	}
	return brau_return(
		(int)$baseCode,
		__('Not enough parameters for operation', 'brau')
	);	
}

/**
 * Logs social network user in
 */
function brau_social_login($uid, $social, $firstName, $lastName, $photo='') {
	//find user
	$args = array(
		'meta_key'     => 'brau_social_id',
	    'meta_value'   => $social . '.brau.' . $uid,
	); 
	$users = get_users($args);

	if (count($users) > 1) {
		return brau_return(
			1599, 
			__('Could not clearly identify user')
		);
	}

	if (is_wp_error($user)) {
    	return brau_return(
			1506, 
			$user->get_error_message()
		);
    }

    //register new user if user not found
    if (empty($users)) {
    	$userdata = array(
		    'user_login'  =>  $social.$uid,
		    'user_pass'   =>  wp_generate_password(),
		    'user_nicename' => $firstName.' '.$lastName,
		    'display_name' => $firstName.' '.$lastName,
		    'nickname' => $firstName.' '.$lastName,
		    'first_name' => $firstName,
		    'last_name' => $lastName,
		);

		$user_id = wp_insert_user($userdata);

		if (is_wp_error($user_id)) {
	    	return brau_return(
				1506, 
				$user_id->get_error_message()
			);
   	 	}

   	 	update_user_meta($user_id, 'brau_social_id', $social . '.brau.' . $uid);
    } else {
    	$user_id = $users[0]->ID;
    }

    //update user photo
    update_user_meta($user_id, 'brau_social_photo', $photo);
    
    //log user in
    wp_set_auth_cookie($user_id, true);

	return brau_return(0, '', true);
}

/**
 * Generates login link for Vkontakte (VK)
 */
function brau_generate_login_vk_link() {
	$helper = new BasicAuthHelper(
		get_option('brau_vk_app_id'), 
		get_option('brau_vk_app_secret'), 
		'https://oauth.vk.com'
	);

	$currentUrl = brau_current_url();

	return $helper->generateUrl(
		'/authorize', 
		[
			'redirect_uri' => $currentUrl,
			'response_type' => 'code',
			'state' => brau_json_encode(json_encode(['brau_auth' => 'vk'])),
			'v' => '5.67',
		],
		true
	);
}

/**
 * Handles redirect after login at Odnoklassniki (OK)
 */
function brau_handle_login_vk_link_redirect($redirectOnSuccess='') {
	$code = brau_check_social_login_code(1600);
	$currentUrl = brau_current_url();

	$helper = new BasicAuthHelper(
		get_option('brau_vk_app_id'), 
		get_option('brau_vk_app_secret'), 
		'https://oauth.vk.com'
	);

	if ($code['success']) {
		$accessResult = $helper->get(
			'/access_token',
			[
				'code' => $code['data']['code'],
				'redirect_uri' => $currentUrl,
			],
			true,
			true
		);

		if (isset($accessResult['access_token']) && isset($accessResult['user_id'])) {
			$helper = new BasicAuthHelper(
				get_option('brau_vk_app_id'), 
				get_option('brau_vk_app_secret'), 
				'https://api.vk.com'
			);

			$userInfo = $helper->get(
				'/method/users.get',
				[
					'uids'         => $accessResult['user_id'],
					'fields'       => 'uid,first_name,last_name,photo_big',
            		'access_token' => $accessResult['access_token']
				]
			);
			$userInfo = $userInfo['response'][0];

			$authResult = brau_social_login($userInfo['uid'], 'vk', $userInfo['first_name'], $userInfo['last_name'], $userInfo['photo_big']);
			if ($authResult['success']) {
				if ($redirectOnSuccess) {
					wp_redirect($redirectOnSuccess);
				}
				//success
			} else {
				//auth failed
			}
		} else {
			//access token not found
		}		
	} else {
		//code not found
	}
}

/**
 * Generates login link for Odnoklassniki (OK)
 */
function brau_generate_login_ok_link() {
	$helper = new BasicAuthHelper(
		get_option('brau_ok_app_id'), 
		get_option('brau_ok_app_secret'), 
		'https://connect.ok.ru/oauth'
	);

	$currentUrl = brau_current_url();

	return $helper->generateUrl(
		'/authorize', 
		[
			'redirect_uri' => $currentUrl,
			'scope' => '',
			'response_type' => 'code',
			'state' => brau_json_encode(json_encode(['brau_auth' => 'ok'])),
		],
		true
	);
}

/**
 * Handles redirect after login at Odnoklassniki (OK)
 */
function brau_handle_login_ok_link_redirect($redirectOnSuccess='') {
	$code = brau_check_social_login_code(1700);
	
	$helper = new BasicAuthHelper(
		get_option('brau_ok_app_id'), 
		get_option('brau_ok_app_secret'), 
		'https://api.ok.ru'
	);

	if ($code['success']) {
		$currentUrl = brau_current_url();
		$accessResult = $helper->post(
			'/oauth/token.do', 
			[
				'code' => $code['data']['code'],
				'grant_type' => 'authorization_code',
				'redirect_uri' => $currentUrl,
			],
			true,
			true
		);

		if (isset($accessResult['access_token'])) {

			$key = get_option('brau_ok_app_pub_key');
			$access = $accessResult['access_token'];
			$secret = get_option('brau_ok_app_secret');
			$str = "application_key=$key"."format=jsonmethod=users.getCurrentUser".md5($access.$secret);
			$sign = md5($str);

			$userInfo = $helper->get(
				'/fb.do',
				[
					'method'          => 'users.getCurrentUser',
		            'access_token'    => $accessResult['access_token'],
		            'application_key' => get_option('brau_ok_app_pub_key'),
		            'format'          => 'json',
		            'sig'             => $sign,
				]
			);

			$authResult = brau_social_login($userInfo['uid'], 'ok', $userInfo['first_name'], $userInfo['last_name'], $userInfo['pic_3']);
			if ($authResult['success']) {
				if ($redirectOnSuccess) {
					wp_redirect($redirectOnSuccess);
				}
				//success
			} else {
				//auth failed
			}
		} else {
			//access token not found
		}
	} else {
		//code not found
	}   
}