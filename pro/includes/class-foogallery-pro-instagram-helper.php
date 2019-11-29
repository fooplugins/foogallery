<?php
/**
 * FooGallery Pro helper class to get Instagram data
 */
if ( ! class_exists( 'FooGallery_Pro_Instagram_Helper' ) ) {

	class FooGallery_Pro_Instagram_Helper {

		/**
		 * Finds the Instagram account profile info from a username
		 */
		function find_account_profile( $username ){
			$profile = array();

			$url = add_query_arg( array(
				'context' => 'blended',
				'query' => $username,
				'count' => 1
			), 'https://www.instagram.com/web/search/topsearch/' );

			$headers = array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36',
				'Origin' => 'https://www.instagram.com',
				'Referer' => 'https://www.instagram.com',
				'Connection' => 'close',
				'Cookie' => 'ig_or=landscape-primary; ig_pr=1; ig_vh=1080; ig_vw=1920; ds_user_id=25025320'
			);

			//search for Instagram users
			$response = wp_remote_get( $url, array( 'headers' => $headers ) );

			//check for problems
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			//store the cookies we get back for future use
			$cookies = wp_remote_retrieve_header( $response, 'set-cookie' );
			$response_cookies = array();

			//reformat the cookies
			foreach( $cookies as $cookie ) {
				$cookie_params = explode('; ', $cookie);

				if (empty($cookie_params[0])) continue;

				list ($cookie_name, $cookie_value) = explode('=', $cookie_params[0]);
				$response_cookies[$cookie_name] = $cookie_value;
			}

			//reformat again to raw
			$response_cookies_raw = array();
			foreach( $response_cookies as $response_cookie_name => $response_cookie_value ) {
				$response_cookies_raw[] = $response_cookie_name . '=' . $response_cookie_value;
			}


			$profile['cookies'] = join( '; ', $response_cookies_raw );

			$user_results = json_decode( wp_remote_retrieve_body( $response ), true );

			//find the exact match for the username
			if( !empty( $user_results['users'] ) && is_array( $user_results['users'] ) ) {
				foreach( $user_results['users'] as $user ) {

					if ( $user['user']['username'] === $username ) {
						$profile['user'] = $user['user'];
						break;
					}
				}
			}
			return $profile;
		}

		function find_user_images_by_username($username, $count = 20) {
			//first get the account profile
			$profile = $this->find_account_profile( $username );

			$image_data = array();
			$image_data['profile'] = $profile;

			if ( $profile === false ) {
				//we could not find the account profile
				$image_data['error'] = __( 'The Instagram username could not be found!', 'foogallery' );
			} else {
				//we found a profile

				//check if the profile is private
				if ( !empty( $profile['user']['is_private'] ) ) {
					$image_data['error'] = __( 'The Instagram account is private and cannot be used!', 'foogallery' );

				} else {
					//all good! continue to get the media

					$user_id = $profile['user']['pk'];

					$image_data['media'] = $this->find_media( $user_id , $profile['cookies'], $count );
				}
			}

			return $image_data;
		}

		function find_media( $user_id, $cookies, $limit = 20 ){

			$variables = json_encode( array(
				'id' => $user_id,
				'first' => 50
			) );

			$gis = md5( join(':', array( '', $variables ) ) );

			// request URL
			$url = add_query_arg( array(
				'query_hash' => 'f2405b236d85e8296cf30347c9f08c2a',
				'variables' => $variables,
			), 'https://www.instagram.com/graphql/query/' );

			$headers = array(
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36',
				'Origin' => 'https://www.instagram.com',
				'Referer' => 'https://www.instagram.com',
				'Connection' => 'close',
				'X-Csrftoken' => '',
				'X-Requested-With' => 'XMLHttpRequest',
				'X-Instagram-Ajax' => '1',
				'X-Instagram-Gis' => $gis,
			);

			$response = wp_remote_get( $url, array( 'headers' => $headers ) );

			//check for problems
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			$nodes = $body['data']['user']['edge_owner_to_timeline_media']['edges'];

			$media = array();

			foreach( $nodes as $node ) {
				$captions = $node['node']['edge_media_to_caption']['edges'];

				$media[] = array(
					'id' => $node['node']['id'],
					'display_url' => $node['node']['display_url'],
					'thumbnail_src' => $node['node']['thumbnail_src'],
					'shortcode' => $node['node']['shortcode'],
					'caption' => (is_array( $captions ) && count( $captions ) > 0) ? $captions[0]['node']['text'] : '',
					'is_video' => $node['node']['is_video'],
					'video_url' => isset( $node['node']['video_url'] ) ? $node['node']['video_url'] : '',
					'comment_count' => $node['node']['edge_media_to_comment']['count'],
					'like_count' => $node['node']['edge_media_preview_like']['count'],
				);
			}

			$media = array_slice($media, 0, $limit);

			return $media;
		}
	}
}