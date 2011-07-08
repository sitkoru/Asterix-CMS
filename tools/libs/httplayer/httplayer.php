<?php
/*
 *   $ httpLayer: httplayer.php, v 1.0, 2010-07-08 16:24 YEKST, key@kosmatov.su Exp $
 *   required curl, pcre
 */

class httpLayer {

	private
		$curl,
		$cookies = array(),
		$headers = array(),
		$url = '',
		$host = '',
		$send_referer = false,
		$follow_location = false
	;

	public
		$messages = array(),
		$response_headers = array(),
		$response_result = ''
	;

	function __construct( $user_agent = 'httpLayer 1.0' ) {

		try {

			$this->curl = curl_init();
			curl_setopt( $this->curl, CURLOPT_USERAGENT, $user_agent );
			curl_setopt( $this->curl, CURLOPT_HEADER, 1 );
			curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
		}

		catch ( Exception $e ) {

			$this->messages[] = $e->getMessage();
		}
	}

	public function setUrl( $url ) {

		$ex = explode( '/', $url );
		$this->host = $ex[0];

		try {

			curl_setopt( $this->curl, CURLOPT_URL, $url );
		}

		catch ( Exception $e ) {

			$this->messages[] = $e->getMessage;
			return false;
		}

		$this->url = $url;

		return true;
	}

	public function getUrl( $strip_host = true ) {

		if ( $strip_host  )
			return str_replace( $this->host, '', $this->url );

		return $this->url;
	}

	public function getHost() {

		return $this->host;
	}

	protected function setPost( $fields ) {

		if ( is_array( $fields ) ) {

			$post = '';
			foreach ( $fields as $key => $value ) {

				$value = urlencode( $value );
				$post .= "&$key=$value";
			}
		}
		else $post = $fields;

		trim( $post, '&' );

		return curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $post );
	}

	protected function setCookie( $key, $value ) {

		$this->cookies[$key] = $value;
	}

	protected function setRequestCookies() {

		if ( ! $this->cookies )
			return false;

		foreach ( $this->cookies as $key => $value ) {

			$value = urlencode( $value );
			$cookie .= "$key=$value;";
		}

		trim( $cookie, ';' );

		return curl_setopt( $this->curl, CURLOPT_COOKIE, $cookie );
	}

	public function getCookies( $key = false ) {

		if ( $key )
			return isset( $this->cookie[$key] ) ? $this->cookie[$key] : false;

		return $this->cookies;
	}

	public function setHeaders( $headers ) {

		foreach ( $headers as $key => $value ) {

			$this->headers[$key] = $value;
		}
	}

	public function setRequestHeaders() {

		return curl_setopt( $this->curl, CURLOPT_HTTPHEADER, $this->headers );
	}

	public function unsetCookies( $key = false ) {

		if ( $key ) {

			unset( $this->cookies[$key] );
			return true;
		}

		return $this->cookies = array();
	}

	public function unsetHeaders( $key = false ) {

		if ( $key ) {

			unset( $this->headers[$key] );
			return true;
		}

		return $this->headers = array();
	}

	public function getHeaders( $key = false ) {

		if ( $key )
			return isset( $this->headers[$key] ) ? $this->headers[$key] : false;

		return $this->headers;
	}

	public function request( $url = '', $post = array(), $file = array() ) {

		if ( $url )
			$this->setUrl( preg_replace( "/^(http(s)?|ftp):\/\//i", '', $url ) );

		$this->setRequestCookies();
		$this->setRequestHeaders();

		if ( ! $post ) {

			curl_setopt( $this->curl, CURLOPT_POST, 0 );
			$this->messages[] = 'Request: GET ' . $this->getUrl();
		}

		else {

			curl_setopt( $this->curl, CURLOPT_POST, 1 );

			if ( $file ) {

				curl_setopt( $this->curl, CURLOPT_UPLOAD, 1 );
				/* file definitions:
				 * CURLOPT_INFILESIZE - filesize,
				 *
				 * */
			}

			$this->setPost( $post );

			$this->messages[] = 'Request: POST ' . $this->getUrl();
		}

		if ( $this->referer )
			$this->setReferer( $this->url );

		$r = curl_exec( $this->curl );
		$h = substr( $r, 0, strpos( $r, "\n\r\n" ) + 3 );
		$r = substr( $r, strlen($h) );

		if ( strpos( $h, '100 Continue' ) ) {

			$h = substr( $r, 0, strpos( $r, "\n\r\n" ) + 3 );
			$r = substr( $r, strlen($h) );
		}

		$h = explode( "\n", $h );

		if ( $h ) {

			$this->response_headers = array();
			foreach ( $h as $i => &$str ) {

				$ex = explode( ':', $str );
				if ( isset( $ex[1] ) ) {

					$key = trim( $ex[0] );
					if ( $key == 'Set-Cookie' ) {

						$exc = explode( '=', $ex[1] );
						$key = trim( $exc[0] );
						unset( $exc[0] );
						$this->setCookie( $key, substr( $exc[1], 0, strpos( $exc[1], ';' ) ) );
					}
					else {

						unset( $ex[0] );
						$this->response_headers[$key] = trim( implode( ':', $ex ) );
					}
				}
				else if ( ! $i )
					$this->response_result = $ex[0];
			}
		}

		$this->messages[] = "Response: {$this->response_result}";

		if ( $this->follow_location and isset( $this->response_headers['Location'] ) ) {

			if ( $this->follow_location > 10 ) {

				$this->messages[] = 'Location recursion detected. Exit';
				$this->follow_location = 1;
			}

			else {

				$this->messages[] = "Location: {$this->response_headers['Location']}";
				$this->follow_location ++;
				$r = $this->request( $this->host . $this->response_headers['Location'] );
			}
		}

		else if ( $this->follow_location > 1 )
			$this->follow_location = 1;

		return $r;
	}

	public function followLocation( $l = 1 ) {												// Follow header 'location' automaticaly

		$this->follow_location = (int) $l;
		curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, (int) $l );
	}

	public function setTimeout( $t = 600 ) {

		curl_setopt( $this->curl, CURLOPT_TIMEOUT, (int) $t );
	}

	public function setRefererUrl( $url ) {

		curl_setopt( $this->curl, CURLOPT_REFERER, $url );
	}

	public function setReferer( $r = true ) {

		$this->send_referer = (bool) $r;
	}

	public function setVerbose( $r = true ) {

		curl_setopt( $this->curl, CURLOPT_VERBOSE, (int) $r );
	}

	public function getMessages( $clean = true ) {

		$r = $this->messages;
		if ( $clean )
			$this->messages = array();

		return $r;
	}

	public function __destruct() {

		curl_close( $this->curl );
	}
}

?>
