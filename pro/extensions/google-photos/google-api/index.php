<?php
// Basic Example
// include your composer dependencies
require_once 'vendor/autoload.php';

// Your redirect URI can be any registered URI, but in this example
// we redirect back to this same page
$redirect_uri = 'http://localhost/php/google-api';
$googlephotos_client_secret = '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';

$client = new Google\Client();
$client->setClientId('90827584789-ghg88pdttme6b9iqevh4mrb7kl40khhh.apps.googleusercontent.com');
$client->setClientSecret($googlephotos_client_secret);




if (!isset($parameters['code']) || !isset($parameters['source']) || 'google' !== $parameters['source']) {

  $url = add_query_arg('test', 'test');
  $url = remove_query_arg('test', $url);
    
    $client->setRedirectUri($redirect_uri);
    $client->setAccessType('offline');   // Gets us our refreshtoken
    $client->setScopes(array('https://www.googleapis.com/auth/photoslibrary.readonly'));
    $client->setPrompt('consent');
    $client->setState(md5($googlephotos_client_secret . 'google') . '::' . rawurlencode($url));
    //$client->setIncludeGrantedScopes(true);


    //$client->setRedirectUri($redirect_uri);

    $auth_url = $client->createAuthUrl();

    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

} else {
  
  echo "Else";
}

//header("Location: https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/drive&response_type=code&access_type=offline&redirect_uri=https://www.maxsoftlk.com&client_id=1072307075112-fposi9hsdfghjkd3239f6gbfpkfasfpk.apps.googleusercontent.com");



/*$service = new Google\Service\Books($client);
$query = 'Henry David Thoreau';
$optParams = [
  'filter' => 'free-ebooks',
];
$results = $service->volumes->listVolumes($query, $optParams);

foreach ($results->getItems() as $item) {
  echo $item['volumeInfo']['title'], "<br /> \n";
}*/


// Authentication with OAuth
/*$client = new Google\Client();
$client->setAuthConfig('/path/to/client_credentials.json');
$client->addScope(Google\Service\Drive::DRIVE);
*/


function add_query_arg( ...$args ) {
	if ( is_array( $args[0] ) ) {
		if ( count( $args ) < 2 || false === $args[1] ) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			$uri = $args[1];
		}
	} else {
		if ( count( $args ) < 3 || false === $args[2] ) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			$uri = $args[2];
		}
	}

	$frag = strstr( $uri, '#' );
	if ( $frag ) {
		$uri = substr( $uri, 0, -strlen( $frag ) );
	} else {
		$frag = '';
	}

	if ( 0 === stripos( $uri, 'http://' ) ) {
		$protocol = 'http://';
		$uri      = substr( $uri, 7 );
	} elseif ( 0 === stripos( $uri, 'https://' ) ) {
		$protocol = 'https://';
		$uri      = substr( $uri, 8 );
	} else {
		$protocol = '';
	}

	if ( strpos( $uri, '?' ) !== false ) {
		list( $base, $query ) = explode( '?', $uri, 2 );
		$base                .= '?';
	} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
		$base  = $uri . '?';
		$query = '';
	} else {
		$base  = '';
		$query = $uri;
	}

	parse_str( $query, $qs );
	$qs = urlencode_deep( $qs ); // This re-URL-encodes things that were already in the query string.
	if ( is_array( $args[0] ) ) {
		foreach ( $args[0] as $k => $v ) {
			$qs[ $k ] = $v;
		}
	} else {
		$qs[ $args[0] ] = $args[1];
	}

	foreach ( $qs as $k => $v ) {
		if ( false === $v ) {
			unset( $qs[ $k ] );
		}
	}

	$ret = build_query( $qs );
	$ret = trim( $ret, '?' );
	$ret = preg_replace( '#=(&|$)#', '$1', $ret );
	$ret = $protocol . $base . $ret . $frag;
	$ret = rtrim( $ret, '?' );
	$ret = str_replace( '?#', '#', $ret );
	return $ret;
}

function remove_query_arg( $key, $query = false ) {
	if ( is_array( $key ) ) { // Removing multiple keys.
		foreach ( $key as $k ) {
			$query = add_query_arg( $k, false, $query );
		}
		return $query;
	}
	return add_query_arg( $key, false, $query );
}

function map_deep( $value, $callback ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $index => $item ) {
			$value[ $index ] = map_deep( $item, $callback );
		}
	} elseif ( is_object( $value ) ) {
		$object_vars = get_object_vars( $value );
		foreach ( $object_vars as $property_name => $property_value ) {
			$value->$property_name = map_deep( $property_value, $callback );
		}
	} else {
		$value = call_user_func( $callback, $value );
	}

	return $value;
}

function urlencode_deep( $value ) {
	return map_deep( $value, 'urlencode' );
}

function build_query( $data ) {
	return _http_build_query( $data, null, '&', '', false );
}

function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {
	$ret = array();

	foreach ( (array) $data as $k => $v ) {
		if ( $urlencode ) {
			$k = urlencode( $k );
		}
		if ( is_int( $k ) && null != $prefix ) {
			$k = $prefix . $k;
		}
		if ( ! empty( $key ) ) {
			$k = $key . '%5B' . $k . '%5D';
		}
		if ( null === $v ) {
			continue;
		} elseif ( false === $v ) {
			$v = '0';
		}

		if ( is_array( $v ) || is_object( $v ) ) {
			array_push( $ret, _http_build_query( $v, '', $sep, $k, $urlencode ) );
		} elseif ( $urlencode ) {
			array_push( $ret, $k . '=' . urlencode( $v ) );
		} else {
			array_push( $ret, $k . '=' . $v );
		}
	}

	if ( null === $sep ) {
		$sep = ini_get( 'arg_separator.output' );
	}

	return implode( $sep, $ret );
}