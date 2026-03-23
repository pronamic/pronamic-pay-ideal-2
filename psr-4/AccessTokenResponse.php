<?php
/**
 * Access token response
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use JsonSerializable;

/**
 * Access token response class
 */
final class AccessTokenResponse implements \JsonSerializable {
	/**
	 * Access token.
	 * 
	 * @var string
	 */
	public string $access_token;

	/**
	 * Scope.
	 * 
	 * @var string
	 */
	public string $scope;

	/**
	 * Token type.
	 * 
	 * @var string
	 */
	public string $token_type;

	/**
	 * Expires in.
	 * 
	 * @var int
	 */
	public int $expires_in;

	/**
	 * Construct access token response object.
	 * 
	 * @param string $access_token Access token.
	 * @param string $scope        Scope.
	 * @param string $token_type   Token type.
	 * @param int    $expires_in   Expires in.
	 */
	public function __construct( $access_token, $scope, $token_type, $expires_in ) {
		$this->access_token = $access_token;
		$this->scope        = $scope;
		$this->token_type   = $token_type;
		$this->expires_in   = $expires_in;
	}

	/**
	 * JSON serialize.
	 * 
	 * @return self
	 */
	public function jsonSerialize(): mixed {
		return $this;
	}

	/**
	 * From remote JSON.
	 * 
	 * @param string $json JSON.
	 * @return self
	 * @throws \InvalidArgumentException Throws an invalid argument exception if the JSON does not meet expectations.
	 */
	public static function from_remote_json( $json ) {
		$data = \json_decode( $json );

		if ( ! \is_object( $data ) ) {
			throw new \InvalidArgumentException( 'JSON is not an object.' );
		}

		if ( ! \property_exists( $data, 'access_token' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not have an `access_token` property.' );
		}

		if ( ! \property_exists( $data, 'scope' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not have an `scope` property.' );
		}

		if ( ! \property_exists( $data, 'token_type' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not have an `token_type` property.' );
		}

		if ( ! \property_exists( $data, 'expires_in' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not have an `expires_in` property.' );
		}

		return new self(
			$data->access_token,
			$data->scope,
			$data->token_type,
			$data->expires_in,
		);
	}
}
