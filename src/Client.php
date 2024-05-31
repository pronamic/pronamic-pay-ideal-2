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

use Pronamic\WordPress\Http\Facades\Http;
use Pronamic\WordPress\Http\Response;
use WP_Error;

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

		$jws_payload = (object) [
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

		if ( false === $private_key ) {
			throw new \Exception( 'Could not load private key.' );
		}

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
					\esc_html( (string) $response_code ),
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
	 * @return string|false
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

	/**
	 * Ensure response status.
	 *
	 * @param Response    $response                 Response.
	 * @param null|string $expected_response_status Expected response status.
	 * @return void
	 * @throws ErrorResponse Throws an exception if the response status does not meet expectations.
	 */
	private function ensure_response_status( $response, $expected_response_status ) {
		if ( null == $expected_response_status ) {
			return;
		}

		$response_status = (string) $response->status();

		if ( $expected_response_status === $response_status ) {
			return;
		}

		$http_exception = new \Exception( 'Unexpected HTTP response: ' . $response_status, (int) $response_status );

		$response_data = $response->json();

		$error = ErrorResponse::from_response_object( $response_data, (int) $response_status, $http_exception );

		throw $error;
	}

	/**
	 * Request.
	 *
	 * @param string      $access_token             Access token.
	 * @param string      $method                   Method.
	 * @param string      $endpoint                 Endpoint.
	 * @param object|null $body                     Body.
	 * @param string      $expected_response_status Expected response status.
	 * @return Response
	 * @throws \Exception Throws an exception if private key can not be loaded.
	 */
	private function request( $access_token, $method, $endpoint, $body, $expected_response_status ) {
		$configuration = $this->config;

		$jwt = JsonWebToken::from_string( $access_token );

		if ( ! is_object( $jwt->payload ) ) {
			throw new \Exception( 'Invalid JSON Web Token without payload.' );
		}

		$url = $configuration->ideal_hub_url . $endpoint;

		$date = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

		/**
		 * Unique request correlation id correlating request. It will be echoed back in response.
		 *
		 * @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_request_fields
		 * @link https://stackoverflow.com/questions/25433258/what-is-the-x-request-id-http-header
		 */
		$request_id = \wp_generate_uuid4();

		$ideal_hub_signing_ssl_certificate = new Certificate( $configuration->ideal_hub_signing_ssl->certificate );

		$jwt_payload = new ObjectAccess( $jwt->payload );

		$jws_header = [
			'typ'                           => 'jose+json',
			'x5c'                           => [
				\base64_encode( $ideal_hub_signing_ssl_certificate->get_der() ),
			],
			'alg'                           => $configuration->jws_algorithm,
			'https://idealapi.nl/sub'       => $jwt_payload->get_property( 'sub' ),
			'https://idealapi.nl/iss'       => $jwt_payload->get_property( 'sub' ),
			'https://idealapi.nl/scope'     => $jwt_payload->get_property( 'scope' ),
			'https://idealapi.nl/acq'       => $jwt_payload->get_property( 'iss' ),
			'https://idealapi.nl/iat'       => $date->format( \DATE_ATOM ),
			'https://idealapi.nl/jti'       => $request_id,
			'https://idealapi.nl/token-jti' => $jwt_payload->get_property( 'jti' ),
			'https://idealapi.nl/path'      => \wp_parse_url( $url, \PHP_URL_PATH ),
			'crit'                          => [
				'https://idealapi.nl/sub',
				'https://idealapi.nl/iss',
				'https://idealapi.nl/acq',
				'https://idealapi.nl/iat',
				'https://idealapi.nl/jti',
				'https://idealapi.nl/path',
				'https://idealapi.nl/scope',
				'https://idealapi.nl/token-jti',
			],
		];

		$private_key = \openssl_pkey_get_private(
			$configuration->ideal_hub_signing_ssl->private_key,
			$configuration->ideal_hub_signing_ssl->private_key_password
		);

		if ( false === $private_key ) {
			throw new \Exception( 'Could not load private key.' );
		}

		$jwt = new JsonWebToken( $jws_header, $body );

		$jwt->sign( $private_key, $configuration->jws_algorithm );

		/**
		 * Supplying request signatures for communication with the iDEAL hub.
		 *
		 * All requests to the iDEAL hub (2, 5) require the Creditor to supply
		 * a signed, detached JWS signature in the "Signature" header. See
		 * rfc7515 appendix-F for the detached JWS specification.
		 *
		 * @link https://datatracker.ietf.org/doc/html/rfc7515#appendix-F
		 * @link https://ideal-portal.ing.nl/idealDeveloperPortal/getting-started
		 */
		$signature = $jwt->detached_content();

		$response = Http::request(
			$url,
			[
				'method'                   => $method,
				'headers'                  => [
					'Accept'        => 'application/json',
					'Request-ID'    => $request_id,
					'Authorization' => 'Bearer ' . $access_token,
					'Signature'     => $signature,
					'Content-Type'  => 'application/json',
				],
				'body'                     => ( null === $body ) ? null : Client::json_encode( $body ),
				'ssl_certificate_blob'     => $configuration->ideal_hub_mtls_ssl->certificate,
				'ssl_private_key_blob'     => $configuration->ideal_hub_mtls_ssl->private_key,
				'ssl_private_key_password' => $configuration->ideal_hub_mtls_ssl->private_key_password,
			]
		);

		$this->ensure_response_status( $response, $expected_response_status );

		return $response;
	}

	/**
	 * Create new transaction.
	 *
	 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604276/Standard+iDEAL+Payment+Direct+Connection
	 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
	 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604322/Security+for+Direct+Connection
	 * @param string                   $access_token Access token.
	 * @param CreateTransactionRequest $request Request.
	 * @return CreateTransactionResponse
	 */
	public function create_new_transaction( $access_token, CreateTransactionRequest $request ) {
		$response = $this->request(
			$access_token,
			'POST',
			'/merchant-cpsp/transactions',
			$request->remote_serialize(),
			'201'
		);

		$data = $response->json();

		$response = CreateTransactionResponse::from_remote_object( $data );

		return $response;
	}

	/**
	 * Get transaction details.
	 *
	 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
	 * @param string $access_token Access token.
	 * @param string $transaction_id Transaction ID.
	 * @return GetTransactionResponse
	 */
	public function get_transaction_details( $access_token, $transaction_id ) {
		$response = $this->request(
			$access_token,
			'GET',
			'/merchant-cpsp/transactions/' . $transaction_id,
			null,
			'200'
		);

		$data = $response->json();

		return GetTransactionResponse::from_remote_object( $data );
	}
}
