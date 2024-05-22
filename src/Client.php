<?php
/**
 * Client
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

/**
 * Client class
 */
final class Client {
	/**
	 * Config.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Construct client object.
	 * 
	 * @param Config $config Configuration object.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Get access token.
	 *
	 * @return AccessTokenResponse
	 * @throws \Exception Throws an exception if requesting an access token fails.
	 */
	public function get_access_token() {
		$acquirer_signing_ssl_certificate = new Certificate( $this->config->acquirer_signing_ssl->certificate );

		/**
		 * JSON Web Signature (JWS).
		 *
		 * @link https://datatracker.ietf.org/doc/html/rfc7515
		 * @link https://www.rfc-editor.org/rfc/rfc7519
		 */
		$jws_header = [
			/**
			 * The type of the header, it should always be `JWT`.
			 */
			'typ'      => 'JWT',
			/**
			 * The algorithm used for signing the JWT. It should be one of
			 * - ES256
			 * - ES384
			 * - RS256
			 * - RS384
			 * - RS512
			 * matching the key algorithm of your signing certificate see rfc7518
			 */
			'alg'      => $this->config->jws_algorithm,
			/**
			 * Base64url encoded sha256 digest of the (DER formatted) signing certificate used.
			 */
			'x5t#S256' => self::base64_encode_url( \hash( 'sha256', $acquirer_signing_ssl_certificate->get_der(), true ) ),
		];

		$jws_payload = [
			/**
			 * The "iss" (issuer) claim identifies the principal that issued the JWT. Should be the merchant id.
			 */
			'iss' => $this->config->merchant_id,
			/**
			 * The "sub" (subject) claim identifies the principal that is the subject of the JWT. Should be the merchant id.
			 */
			'sub' => $this->config->merchant_id,
			/**
			 * The "aud" (audience) claim identifies the recipients that the JWT is intended for.
			 * For sandbox this is https://api.sandbox.ideal-acquiring.ing.nl and for production https://api.ideal-acquiring.ing.nl
			 */
			'aud' => $this->config->acquirer_url,
			/**
			 * The "iat" (issued at) claim identifies the time at which the JWT was issued (created) by the merchant (value in epoch seconds).
			 */
			'iat' => time(),
		];

		$private_key = openssl_pkey_get_private(
			$this->config->acquirer_signing_ssl->private_key,
			$this->config->acquirer_signing_ssl->private_key_password
		);

		$jwt = new JsonWebToken( $jws_header, $jws_payload );

		$jwt->sign( $private_key, $this->config->jws_algorithm );

		$url = $this->config->acquirer_url . '/ideal2/merchanttoken';

		$body = [
			'client_id'             => $this->config->merchant_id,
			'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
			'client_assertion'      => (string) $jwt,
			'grant_type'            => 'client_credentials',
			'scope'                 => 'ideal2',
		];

		$result = \wp_remote_post(
			$url,
			[
				'headers'                  => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body'                     => $body,
				'ssl_certificate_blob'     => $this->config->acquirer_mtls_ssl->certificate,
				'ssl_private_key_blob'     => $this->config->acquirer_mtls_ssl->private_key,
				'ssl_private_key_password' => $this->config->acquirer_mtls_ssl->private_key_password,
			]
		);

		$response_code = (int) \wp_remote_retrieve_response_code( $result );

		$body = \wp_remote_retrieve_body( $result );

		if ( 200 !== $response_code ) {
			/**
			 * Error responses may happen too. Error response bodies may
			 * contain hints about what went wrong for logging/debugging
			 * purposes. For non 200 status codes the response should just
			 * be interpreted as failure and the body becomes somewhat
			 * unimportant.
			 *
			 * @link https://ideal-portal.ing.nl/idealDeveloperPortal/access-token
			 */
			throw new \Exception(
				\sprintf(
					'Access token request failed, unexpected response: %s - %s - %s',
					\esc_url( $url ),
					\esc_html( $response_code ),
					\esc_html( $body )
				),
				(int) $response_code
			);
		}

		return AccessTokenResponse::from_remote_json( $body );
	}

	/**
	 * Base64 encode URL.
	 *
	 * @link https://base64.guru/standards/base64url
	 * @link https://datatracker.ietf.org/doc/html/rfc4648#section-5
	 * @link https://www.php.net/manual/en/function.base64-encode.php#103849
	 * @param string $value Value.
	 * @return string
	 */
	public static function base64_encode_url( $value ) {
		return \rtrim(
			\strtr(
				\base64_encode( $value ),
				[
					'+' => '-',
					'/' => '_',
				]
			),
			'='
		);
	}

	/**
	 * JSON encode.
	 *
	 * @link https://github.com/firebase/php-jwt/blob/e9690f56c0bf9cd670655add889b4e243e3ac576/src/JWT.php#L379-L405
	 * @param mixed $value Value.
	 * @return string
	 */
	public static function json_encode( $value ) {
		return \wp_json_encode(
			$value,
			/**
			 * Don't escape /.
			 *
			 * Necessary for JSON Web Token (JWT) and/or iDEAL 2?
			 */
			\JSON_UNESCAPED_SLASHES
		);
	}
}
