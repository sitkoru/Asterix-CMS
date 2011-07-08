<?php
/*
* @package TypografWebService
* key@kosmatov.su 2010-05-07 $
* required curl
*/

class TypografWebService {

	public $errors = array();

	const
		SERVICE_TYPOGRAFRU = 0,
		SERVICE_ARTLEBEDEV = 1
	;

	protected
		$service = 0,
		$headers = array(),
		$tags = array(

			'{text}' => '',
			'{charset}' => 'utf-8',
			'{tags}' => 1,
			'{tags-delete}' => 0,
			'{paragraph-insert}' => 1,
			'{paragraph-start}' => '<p>',
			'{paragraph-stop}' => '</p>',
			'{newline}' => '</p><p>',
			'{newline-insert}' => 1,
			'{nowrap-insert}' => 1,
			'{nowrap-start}' => '<span class="nowrap">',
			'{nowrap-stop}' => '</span>',
			'{minus-sign}' => '&ndash;',
			'{acronym}' => '',
			'{acronym-insert}' => 0,
			'{symbols-type}' => 0,
			'{link-class}' => '',
			'{hanging-line}' => 1,
			'{hanging-punct}' => 0,
			'{entity-type}' => 1,
			'{max-nobr}' => 3
		);

	private
		$curl,
		$path
	;

	public function __construct( $useragent = 'Sitko.ru typograf client', $tags = false ) {

		if ( $tags )
			$this->setTags( $tags );

		try {

			$this->curl = curl_init();
			curl_setopt( $this->curl, CURLOPT_POST, 1 );
			curl_setopt( $this->curl, CURLOPT_USERAGENT, $useragent );
		}

		catch ( Exception $e ) {

			$this->errors[] = $e->getMessage();
		}

		$this->setTypografRuService();

		$info = pathinfo( __FILE__ );
		$this->path = $info['dirname'];
	}

	public function setTags( $tags ) {

		foreach ( $tags as $key => $value ) {

			if ( isset( $this->tags["{{$key}}"] ) )
				$this->tags["{{$key}}"] = $value;
		}
	}

	public function setHeaders( $headers ) {

		foreach ( $headers as $key => $value ) {

			$this->headers[$key] = $value;
		}
	}

	public function setServiceUrl( $url ) {

		try {

			curl_setopt( $this->curl, CURLOPT_URL, $url );
			return true;
		}

		catch ( Exception $e ) {

			$this->errors[] = $e->getMessage;
			return false;
		}
	}

	public function setLebedevService() {

		$this->service = self::SERVICE_ARTLEBEDEV;
		return $this->setServiceUrl( 'http://typograf.artlebedev.ru/webservices/typograf.asmx' );
	}

	public function setTypografRuService() {

		$this->service = self::SERVICE_TYPOGRAFRU;
		return $this->setServiceUrl( 'http://typograf.ru/webservice/' );
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

	public function request( $text, $silent = true ) {

		if ( $silent )
			ob_start();

		if ( $this->service == self::SERVICE_TYPOGRAFRU ) {

			try {

				$xml = file_get_contents( $this->path . '/typograf.ru.xml' );
			}

			catch( Exception $e ) {

				$this->errors[] = $e->getMessage();
				return false;
			}

			$xml = strtr( $xml, $this->tags );
			$chr = $this->tags['{charset}'];
			$this->setPost( array( 'text' => $text, 'xml' => $xml, 'chr' => $chr ) );
		}

		else if ( $this->service == self::SERVICE_ARTLEBEDEV ) {

			try {

				$xml = file_get_contents( $this->path . '/artlebedev.soap.xml' );
			}

			catch( Exception $e ) {

				$this->errors[] = $e->getMessage();
				return false;
			}

			$this->tags['{text}'] = $text;
			$xml = strtr( $xml, $this->tags );
			$this->headers['SOAPAction'] = 'http://typograf.artlebedev.ru/webservices/ProcessText';
			$this->setPost( $xml );
		}

		if ( $this->headers )
			curl_setopt( $this->curl, CURLOPT_HTTPHEADER, $this->headers );

		curl_exec( $this->curl );

		if ( $silent ) {

			$ret = ob_get_contents();
			ob_end_clean();

			if ( $this->service == self::SERVICE_ARTLEBEDEV ) {

				$from = strpos( $ret, '<ProcessTextResult>' ) + 19;
				$to = strpos( $ret, '</ProcessTextResult>' ) - $from - 1;
				$ret = substr( $ret, $from, $to );
				$ret = htmlspecialchars_decode( $ret );
			}
		}

		else $ret = true;

		return $ret;
	}

	public function getInfo() {

		return curl_getinfo( $this->curl );
	}

	public function __destruct() {

		curl_close( $this->curl );
	}
}
?>
