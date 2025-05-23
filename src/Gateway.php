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

		$this->set_mode( $config->mode );

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

		$access_token = $this->get_cached_access_token();

		// Reference.
		$reference = $payment->format_string( (string) $this->config->reference );

		if ( '' === $reference ) {
			$reference = $payment->get_id();
		}

		$payment->set_meta( 'reference', $reference );

		// Request.
		$create_transaction_request = new CreateTransactionRequest(
			new Amount( $payment->get_total_amount()->get_minor_units()->to_int() ),
			(string) $payment->get_description(),
			(string) $reference,
			new CreateTransactionCreditor( 'NL' ),
			$payment->get_return_url()
		);

		$response = $client->create_new_transaction( $access_token, $create_transaction_request );

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

		$access_token = $this->get_cached_access_token();

		$client = new Client( $this->config );

		$response = $client->get_transaction_details( $access_token, $transaction_id );

		if ( null !== $response->status ) {
			$payment->set_status( $response->status->to_pronamic_status() );
		}
	}

	/**
	 * Get cached access token.
	 *
	 * @return string
	 */
	public function get_cached_access_token() {
		$client = new Client( $this->config );

		$cache_key = \sprintf( 'pronamic_gateway_ideal_2_access_token_%s_%s', $this->config->merchant_id, $this->config->mode );

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

			\set_transient( $cache_key, wp_json_encode( $access_token_response ), $access_token_response->expires_in );
		}

		return $access_token_response->access_token;
	}
}
