<?php
/**
 * JSON Web Token
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Firebase\JWT\JWT;
use JsonSerializable;

/**
 * JSON Web Token class
 */
final class JsonWebToken implements \JsonSerializable {
	public $header;

	public $payload;

	public $signature;

	public function __construct( $header, $payload, $signature = '' ) {
		$this->header    = $header;
		$this->payload   = $payload;
		$this->signature = $signature;
	}

	public function get_signing_string() {
		return $this->format( '$header.$payload' );
	}

	/**
	 * Sign this token with the specified private key and algorithm.
	 *
	 * @param string $private_key Private key.
	 * @param string $algorithm   Algorithm.
	 * @return void
	 */
	public function sign( $private_key, $algorithm ) {
		$signing_string = $this->get_signing_string();

		/**
		 * We use the PHP-JWT library due to the complexity of encoding the
		 * signature from a DER object. This is too complex for Pronamic to
		 * maintain and therefore it is better to use the PHP-JWT library.
		 *
		 * @link https://github.com/firebase/php-jwt/blob/v6.10.0/src/JWT.php#L252-L263
		 * @link https://github.com/firebase/php-jwt/blob/v6.10.0/src/JWT.php#L602-L627
		 */
		$this->signature = JWT::sign( $signing_string, $private_key, $algorithm );
	}

	/**
	 * Format this JSON web token to string.
	 *
	 * @param string $format Format.
	 * @return string
	 */
	private function format( $format = '$header.$payload.$signature' ) {
		/**
		 * Segments.
		 *
		 * A JWT is represented as a sequence of URL-safe parts separated by
		 * period ('.') characters.  Each part contains a base64url-encoded
		 * value.
		 *
		 * @link https://github.com/itsoft7/revolut-php/blob/74a332b4605e9f912d4846a15ad83416a47b3a53/src/Revolut.php#L125-L159
		 * @link https://www.rfc-editor.org/rfc/rfc7519#section-3
		 */
		$value = \strtr(
			$format,
			[
				'$header'    => Client::base64_encode_url( Client::json_encode( $this->header ) ),
				'$payload'   => Client::base64_encode_url( Client::json_encode( $this->payload ) ),
				'$signature' => Client::base64_encode_url( $this->signature ),
			]
		);

		return $value;
	}

	/**
	 * Detached content.
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc7515#appendix-F
	 * @return string
	 */
	public function detached_content() {
		return $this->format( '$header..$signature' );
	}

	/**
	 * JSON serialize.
	 *
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return $this->format();
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->format();
	}

	/**
	 * From string.
	 *
	 * @link https://github.com/felx/nimbus-jose-jwt/blob/47d66f2775c392964788aa6389a46fac84f976cd/src/main/java/com/nimbusds/jose/JWSObject.java#L400-L422
	 * @param string $value Value.
	 * @return self
	 */
	public static function from_string( $value ) {
		$parts = \explode( '.', $value );

		if ( 3 !== \count( $parts ) ) {
			throw new \InvalidArgumentException( 'JSON web token does not contain three parts (header, payload and signature).' );
		}

		list( $header, $payload, $signature ) = $parts;

		return new self(
			\json_decode( \base64_decode( $header ) ),
			\json_decode( \base64_decode( $payload ) ),
			\base64_decode( $signature )
		);
	}
}
