<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\IDeal2
 */

namespace Pronamic\WordPress\Pay\Gateways\IDeal2;

use Pronamic\WordPress\Pay\Core\Gateway as PronamicGateway;
use Pronamic\WordPress\Pay\Core\ModeTrait;
use Pronamic\WordPress\Pay\Core\PaymentMethod;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Gateway class
 */
final class Gateway extends PronamicGateway {
	use ModeTrait;

	/**
	 * Config.
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Constructs and initializes an iDEAL 2.0 gateway
	 *
	 * @param Config $config Config.
	 */
	public function __construct( Config $config ) {
		parent::__construct();

		$this->config = $config;

		$this->set_method( self::METHOD_HTTP_REDIRECT );

		$this->supports = [
			'payment_status_request',
		];

		$ideal_payment_method = new PaymentMethod( PaymentMethods::IDEAL );
		$ideal_payment_method->set_status( 'active' );

		$this->register_payment_method( $ideal_payment_method );
	}

	/**
	 * Start
	 *
	 * @see PronamicGateway::start()
	 *
	 * @param Payment $payment Payment.
	 */
	public function start( Payment $payment ) {
		$client = new Client( $this->config );

		$configuration = $this->config;

		$access_token = $this->get_cached_access_token();

		$jwt = JsonWebToken::from_string( $access_token );

		/**
		 * Standard iDEAL Payment (Direct Connection).
		 *
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604276/Standard+iDEAL+Payment+Direct+Connection
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604322/Security+for+Direct+Connection
		 */
		$url = $configuration->ideal_hub_url . '/merchant-cpsp/transactions';

		$create_transaction_request = new CreateTransactionRequest(
			new Amount( $payment->get_total_amount()->get_minor_units()->to_int() ),
			$payment->get_description(),
			'iDEALpurchase21',
			new CreateTransactionCreditor( 'NL' ),
			$payment->get_return_url()
		);

		$body = $create_transaction_request->remote_serialize();

		$date = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

		$request_id = \wp_generate_uuid4();

		$ideal_hub_signing_ssl_certificate = new Certificate( $configuration->ideal_hub_signing_ssl->certificate );

		$jws_header = [
			'typ'                           => 'jose+json',
			'x5c'                           => [
				\base64_encode( $ideal_hub_signing_ssl_certificate->get_der() ),
			],
			'alg'                           => $configuration->jws_algorithm,
			'https://idealapi.nl/sub'       => $jwt->payload->sub,
			'https://idealapi.nl/iss'       => $jwt->payload->sub,
			'https://idealapi.nl/scope'     => $jwt->payload->scope,
			'https://idealapi.nl/acq'       => $jwt->payload->iss,
			'https://idealapi.nl/iat'       => $date->format( \DATE_ATOM ),
			'https://idealapi.nl/jti'       => $request_id,
			'https://idealapi.nl/token-jti' => $jwt->payload->jti,
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

		$result = \wp_remote_post(
			$url,
			[
				'headers'                  => [
					'Accept'        => 'application/json',
					/**
					* Unique request correlation id correlating request. It will be echoed back in response.
					*
					* @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_request_fields
					* @link https://stackoverflow.com/questions/25433258/what-is-the-x-request-id-http-header
					*/
					'Request-ID'    => $request_id,
					'Authorization' => 'Bearer ' . $access_token,
					'Signature'     => $signature,
					'Content-Type'  => 'application/json',
				],
				'body'                     => Client::json_encode( $body ),
				'ssl_certificate_blob'     => $configuration->ideal_hub_mtls_ssl->certificate,
				'ssl_private_key_blob'     => $configuration->ideal_hub_mtls_ssl->private_key,
				'ssl_private_key_password' => $configuration->ideal_hub_mtls_ssl->private_key_password,
			]
		);

		$body = \wp_remote_retrieve_body( $result );

		$response = CreateTransactionResponse::from_remote_json( $body );

		$payment->set_action_url( $response->links->redirect_url->href );
		$payment->set_transaction_id( $response->transaction_id );
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws \Exception Throws an execution if private key cannot be read.
	 */
	public function update_status( Payment $payment ) {
		$client = new Client( $this->config );

		$transaction_id = $payment->get_transaction_id();

		if ( null === $transaction_id ) {
			return;
		}

		$client = new Client( $this->config );

		$configuration = $this->config;

		$access_token = $this->get_cached_access_token();

		$jwt = JsonWebToken::from_string( $access_token );

		/**
		 * Standard iDEAL Payment (Direct Connection).
		 *
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604276/Standard+iDEAL+Payment+Direct+Connection
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417538917/iDEAL+-+Merchant+CPSP+API
		 * @link https://currencenl.atlassian.net/wiki/spaces/IPD/pages/3417604322/Security+for+Direct+Connection
		 */
		$url = $configuration->ideal_hub_url . '/merchant-cpsp/transactions/' . $transaction_id;

		$date = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

		$request_id = \wp_generate_uuid4();

		$ideal_hub_signing_ssl_certificate = new Certificate( $configuration->ideal_hub_signing_ssl->certificate );

		$jws_header = [
			'typ'                           => 'jose+json',
			'x5c'                           => [
				\base64_encode( $ideal_hub_signing_ssl_certificate->get_der() ),
			],
			'alg'                           => $configuration->jws_algorithm,
			'https://idealapi.nl/sub'       => $jwt->payload->sub,
			'https://idealapi.nl/iss'       => $jwt->payload->sub,
			'https://idealapi.nl/scope'     => $jwt->payload->scope,
			'https://idealapi.nl/acq'       => $jwt->payload->iss,
			'https://idealapi.nl/iat'       => $date->format( \DATE_ATOM ),
			'https://idealapi.nl/jti'       => $request_id,
			'https://idealapi.nl/token-jti' => $jwt->payload->jti,
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

		$jwt = new JsonWebToken( $jws_header );

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

		$result = \wp_remote_get(
			$url,
			[
				'headers'                  => [
					'Accept'        => 'application/json',
					/**
					* Unique request correlation id correlating request. It will be echoed back in response.
					*
					* @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_request_fields
					* @link https://stackoverflow.com/questions/25433258/what-is-the-x-request-id-http-header
					*/
					'Request-ID'    => $request_id,
					'Authorization' => 'Bearer ' . $access_token,
					'Signature'     => $signature,
				],
				'ssl_certificate_blob'     => $configuration->ideal_hub_mtls_ssl->certificate,
				'ssl_private_key_blob'     => $configuration->ideal_hub_mtls_ssl->private_key,
				'ssl_private_key_password' => $configuration->ideal_hub_mtls_ssl->private_key_password,
			]
		);

		$body = \wp_remote_retrieve_body( $result );

		$response = GetTransactionResponse::from_remote_json( $body );

		var_dump( $response );

		exit;
	}

	/**
	 * Get cached access token.
	 *
	 * @return string
	 */
	public function get_cached_access_token() {
		$client = new Client( $this->config );

		$cache_key = 'pronamic_gateway_ideal_2_access_token_' . $this->config->merchant_id;

		$access_token_response = null;

		$result = \get_transient( $cache_key );

		if ( false !== $result ) {
			try {
				$access_token_response = AccessTokenResponse::from_remote_json( $result );
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// No problem, will try to request a new access token.
			}
		}

		if ( null === $access_token_response ) {
			$access_token_response = $client->get_access_token();
		}

		\set_transient( $cache_key, wp_json_encode( $access_token_response ), $access_token_response->expires_in );

		return $access_token_response->access_token;
	}
}
