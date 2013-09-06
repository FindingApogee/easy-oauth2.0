<?php
	/*
Plugin Name: Easy Google OAuth 2.0
Plugin URI: https://findingapogee.com/easy-google-oauth-2-0/
Description: Simple Google Oauth integration 
Version: 2.56
Author: justMiles
Author URI: http://findingapogee.com 
*/	/*  Copyright 2013  Finding Apogee
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
*/	// don't load directly
	

if (!class_exists("fa_oauth")) {
    class fa_oauth {
    	
		function easyGoogleOauth(){
			require_once 'src/Google_Client.php';
			require_once 'src/contrib/Google_Oauth2Service.php';
			//require_once 'src/auth/Google_Auth.php';
			
			//session_start();
			$client = new Google_Client();
			$client->setClientId('YOUR_CLIENT_ID');
			$client->setClientSecret('YOUR_CLIENT_SECRET');
			$client->setRedirectUri('YOUR_REDIRECT_URI');
			$client->setDeveloperKey('YOUR_DEVELOPER_KEY');
			
			$oauth2 = new Google_Oauth2Service($client);
			
			if (isset($_GET['code'])) {
				$client->authenticate();
				$_SESSION['token'] = $client->getAccessToken();
				$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
				header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
			}
			
			if (isset($_SESSION['token'])) {
			$client->setAccessToken($_SESSION['token']);
			}
			
			if (isset($_REQUEST['logout'])) {
				unset($_SESSION['token']);
				unset($_SESSION['google_data']); //Google session data unset
				$client->revokeToken();
				//TODO: Add logout function
				header('location: https://findingapogee.com');
			}
			
			if ($client->getAccessToken()) {
				$user = $oauth2->userinfo->get();
				$_SESSION['google_data']=$user; // Storing Google User Data in Session			
				$_SESSION['token'] = $client->getAccessToken();
			 	//echo json_encode($_SESSION['google_data']);
				} else {
				$authUrl = $client->createAuthUrl();
			}
			
			if (isset($authUrl) && !isset($_REQUEST['logout'])) {
				header('location: '.$authUrl);
				echo '<a class="login" href='.$authUrl.'>Google Account Login</a>';
			} else if (isset($_SESSION['google_data'])) {
				//TODO: Add login function
				fa_oauth::onLogin();
				echo '<a class="logout" href="?logout">Logout</a>';
			}
		}
		
		function onLogin() {
			$email = $_SESSION['google_data']['email'];
			$verified_email = $_SESSION['google_data']['verified_email'];
			$name = $_SESSION['google_data']['name'];
			$given_name = $_SESSION['google_data']['given_name'];
			$family_name = $_SESSION['google_data']['family_name'];
			$link = $_SESSION['google_data']['link'];
			$picture = $_SESSION['google_data']['picture'];
			$gender = $_SESSION['google_data']['gender'];
			$birthday = $_SESSION['google_data']['birthday'];
			$locale = $_SESSION['google_data']['locale'];
			$hd = $_SESSION['google_data']['hd'];
			$user_id = email_exists($email);
			if($user_id) {
				wp_set_auth_cookie( $user_id, 0 );
			   } else {
			   	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
				$user_id = wp_create_user( $email, $random_password, $email );
				wp_set_auth_cookie( $user_id, 0 );
			   }
			   wp_mail('miles@findingapogee.com', $name.' Logged into FA', $name.' has logged into findingapogee.com with the email '.$email.'. Good job bringing \'em in :) '.$picture );
				header('location: https://findingapogee.com/');
		}
    }
}

add_shortcode('EasyGoogleOauth', array('fa_oauth', 'easyGoogleOauth'));

?>